<?php
/**
 * @file dbsession.php
 * @brief Database session handler for WBCE CMS.
 *
 * Stores session data in the database instead of files.
 * Compatible with both MySQL/MariaDB and SQLite via the new Database class.
 *
 * Schema design:
 *   - last_accessed is stored as INTEGER (Unix timestamp) for portability.
 *   - On MySQL a VIRTUAL generated column last_accessed_human provides a
 *     human-readable TIMESTAMP view in phpMyAdmin/Adminer at no storage cost.
 *     On SQLite this column is stripped by normalizeSql automatically.
 *   - If the driver changes (MySQL<->SQLite) and the schema no longer matches
 *     the active driver, the table is dropped and recreated automatically.
 *     All active sessions are lost in that case, which is acceptable since
 *     a driver switch implies a fresh deployment anyway.
 *
 * @author Richard Willars (www.richardwillars.com)
 * @author Stephen McIntyre (stevedecoded.com/)
 * @author Norbert Heimsath (heimsath.org)
 * @author Christian M. Stefan (https://www.wbEasy.de)
 *
 * @license GPLv2 or any later
 *
 * Note: According to the PHP SessionHandlerInterface contract (especially since PHP 7),
 * the write(), destroy() and gc() methods must return a boolean value.
 */

// Prevent  this  file  from  being  accessed  directly
defined('WB_PATH') or die('No direct access allowed');

class WbceDbSession implements SessionHandlerInterface
{
    private bool $alive = true;
    private ?Database $db = null;
    /** Existing WBCE 1.x MySQL session tables store last_accessed as TIMESTAMP. */
    private bool $legacyTimestampSchema = false;

    /** @var resource|null Held from read() to close()/write-end — see read(). */
    private $lockHandle = null;

    public function __construct()
    {
        $this->fetchGlobalDbInstanceOrDie();
        $this->ensureSessionTableExists();

        // A fresh installation has no authenticated WBCE session to resume.
        // During an upgrade, however, the update script must keep using the
        // database handler so it can verify and preserve the administrator's
        // existing login across the extracted code and database update steps.
        if (defined('WB_INSTALLER')) {
            return;
        }

        session_set_save_handler($this);

        if (mt_rand(0, 100) <= 1) {
            $this->gc(0);
        }
    }

    protected function fetchGlobalDbInstanceOrDie(): void
    {
        global $database;

        if (!($database instanceof Database)) {
            die('WbceDbSession: No valid Database object found.');
        }

        $this->db = $database;
    }

    /**
     * Ensure the session table exists and matches the active driver's expected schema.
     *
     * Detection logic:
     *   MySQL schema has `last_accessed_human` (VIRTUAL generated column).
     *   SQLite schema does not (stripped by normalizeSql during table creation).
     *
     *   If the detected schema does not match the active driver, the table is
     *   dropped and recreated. This handles the case where the database driver
     *   was switched (e.g. from MySQL to SQLite or vice versa).
     */
    protected function ensureSessionTableExists(): void
    {
        $tableExists = $this->db->fieldExists('{TP}dbsessions', 'id');

        if ($tableExists) {
            $hasHumanCol    = $this->db->fieldExists('{TP}dbsessions', 'last_accessed_human');
            $isMySQL        = $this->db->getDriver() === 'mysql';
            $schemaMismatch = ($isMySQL && !$hasHumanCol) || (!$isMySQL && $hasHumanCol);

            if ($schemaMismatch) {
                // A WBCE 1.x MySQL table has a TIMESTAMP in last_accessed and
                // no generated display column. It is fully usable for session
                // reads, so never drop it during an update: that would log out
                // every administrator exactly when the updater needs the session.
                if ($isMySQL && !$hasHumanCol) {
                    $this->legacyTimestampSchema = true;
                } else {
                    $this->db->query('DROP TABLE IF EXISTS `{TP}dbsessions`');
                    $tableExists = false;
                }
            }
        }

        if (!$tableExists) {
            $this->createSessionTable();
        }
    }

    /**
     * Create the session table.
     *
     * last_accessed is INTEGER (Unix timestamp) — portable across MySQL and SQLite.
     * last_accessed_human is a MySQL VIRTUAL column for human-readable display
     * in database admin tools. normalizeSql strips it for SQLite automatically.
     * Inline INDEX declarations are also stripped for SQLite by normalizeSql.
     */
    private function createSessionTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `{TP}dbsessions` (
                `id`                  VARCHAR(148) NOT NULL,
                `data`                LONGTEXT     NOT NULL,
                `last_accessed`       INTEGER      NOT NULL DEFAULT 0,
                `last_accessed_human` TIMESTAMP    AS (FROM_UNIXTIME(`last_accessed`)) VIRTUAL,
                `user`                INT(11)      NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                INDEX `idx_last_accessed` (`last_accessed`),
                INDEX `idx_user` (`user`)
            ) {TABLE_ENGINE} {TABLE_COLLATION};
        ";

        $this->db->importSql($sql);
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        $this->releaseLock();
        return true;
    }

    /**
     * Read session data from database.
     *
     * Also acquires a per-session-id file lock, held until close()/write()
     * ends the request's session lifecycle. Without this, concurrent
     * requests for the same session (e.g. a page load plus the handful of
     * thumbnail/AJAX requests it fires alongside it) each read the same
     * snapshot and each write it back independently — whichever finishes
     * last wins and silently overwrites anything the others added in the
     * meantime (an IDKEY generated by the main page load, say). PHP's own
     * file-based session handler prevents exactly this via flock(); this
     * handler talks to the DB instead, so it has to do the same thing itself.
     */
    public function read(string $id): string|false
    {
        $this->acquireLock($id);

        return $this->db->fetchValue(
            'SELECT `data` FROM `{TP}dbsessions` WHERE `id` = ? LIMIT 1',
            [$id]
        ) ?? '';
    }

    private function acquireLock(string $id): void
    {
        $lockFile = sys_get_temp_dir() . '/wbce_session_' . md5($id) . '.lock';
        $handle   = fopen($lockFile, 'c');
        if ($handle === false) {
            return; // Can't lock — proceed unlocked rather than fail the request.
        }
        flock($handle, LOCK_EX); // Blocks until any other request for this session finishes.
        $this->lockHandle = $handle;
    }

    private function releaseLock(): void
    {
        if ($this->lockHandle !== null) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->lockHandle = null;
        }
    }

    /**
     * Write session data to database.
     *
     * last_accessed written as PHP time() integer — no SQL time functions needed,
     * works identically on MySQL and SQLite.
     */
    public function write(string $id, string $data): bool
    {
        $userId = (int)(WbceSession::get('USER_ID') ?? 0);

        $sql = $this->legacyTimestampSchema
            ? 'REPLACE INTO `{TP}dbsessions` (`id`, `data`, `user`, `last_accessed`) VALUES (?, ?, ?, NOW())'
            : 'REPLACE INTO `{TP}dbsessions` (`id`, `data`, `user`, `last_accessed`) VALUES (?, ?, ?, ?)';
        $params = $this->legacyTimestampSchema ? [$id, $data, $userId] : [$id, $data, $userId, time()];
        $this->db->query($sql, $params);

        if ($this->db->hasError()) {
            // One retry: covers a transient SQLITE_BUSY that busy_timeout didn't
            // fully absorb. A silently dropped write here loses whatever this
            // request just stored in $_SESSION (e.g. a freshly issued IDKEY),
            // which then fails as "Access denied" on the very next request.
            $this->db->query($sql, $params);
        }

        return !$this->db->hasError();
    }

    /**
     * Destroy a session.
     */
    public function destroy(string $id): bool
    {
        $this->db->deleteRow('{TP}dbsessions', 'id', $id);
        return true;
    }

    /**
     * Garbage collection — remove expired sessions.
     *
     * Cutoff calculated in PHP as time() - $expire.
     * Simple integer comparison, no SQL date functions needed.
     */
    public function gc(int $maxlifetime): int|false
    {
        $expire = (int)ini_get('session.gc_maxlifetime');

        if (Settings::get("wb_session_timeout") !== false) {
            $expire = (int)Settings::get("wb_session_timeout");
        } elseif (Settings::get("wb_secform_timeout") !== false) {
            $expire = (int)Settings::get("wb_secform_timeout");
        }

        if ($this->legacyTimestampSchema) {
            $this->db->query('DELETE FROM `{TP}dbsessions` WHERE `last_accessed` < FROM_UNIXTIME(?)', [time() - $expire]);
        } else {
            $this->db->query('DELETE FROM `{TP}dbsessions` WHERE `last_accessed` < ?', [time() - $expire]);
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->alive) {
            session_write_close();
            $this->alive = false;
        }
    }

    public function delete(): void
    {
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->alive = false;
    }
}
