<?php

final class WbcePasswordResetService
{
    private const LIFETIME = 3600;
    private const REQUEST_INTERVAL = 300;

    public function __construct(private Database $db) { $this->ensureTable(); }

    public function issue(string $email): ?array
    {
        $user = $this->db->fetchRow('SELECT * FROM `{TP}users` WHERE `email` = ? AND `active` = 1 LIMIT 1', [trim($email)]);
        if (!$user || strlen((string)($user['signup_confirmcode'] ?? '')) > 25) return null;
        $last = (int)$this->db->fetchValue('SELECT MAX(`requested_at`) FROM `{TP}password_resets` WHERE `user_id` = ?', [(int)$user['user_id']]);
        if ($last > time() - self::REQUEST_INTERVAL) return null;
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $this->db->query('UPDATE `{TP}password_resets` SET `used_at` = ? WHERE `user_id` = ? AND `used_at` = 0', [time(), (int)$user['user_id']]);
        $this->db->insertRow('{TP}password_resets', [
            'user_id' => (int)$user['user_id'], 'selector' => $selector,
            'token_hash' => hash('sha256', $validator), 'expires_at' => time() + self::LIFETIME,
            'used_at' => 0, 'requested_at' => time(),
        ]);
        return ['user' => $user, 'token' => $selector . '.' . $validator, 'selector' => $selector];
    }

    public function revoke(string $selector): void
    {
        if (preg_match('/^[a-f0-9]{18}$/', $selector)) {
            $this->db->query('UPDATE `{TP}password_resets` SET `used_at` = ? WHERE `selector` = ?', [time(), $selector]);
        }
    }

    public function validate(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !preg_match('/^[a-f0-9]{18}$/', $parts[0]) || !preg_match('/^[a-f0-9]{64}$/', $parts[1])) return null;
        $reset = $this->db->fetchRow('SELECT * FROM `{TP}password_resets` WHERE `selector` = ? AND `used_at` = 0 LIMIT 1', [$parts[0]]);
        if (!$reset || (int)$reset['expires_at'] < time() || !hash_equals((string)$reset['token_hash'], hash('sha256', $parts[1]))) return null;
        return $reset;
    }

    public function complete(string $token, string $passwordHash): bool
    {
        $reset = $this->validate($token);
        if (!$reset) return false;
        $userId = (int)$reset['user_id'];
        if (!$this->db->upsertRow('{TP}users', 'user_id', ['user_id' => $userId, 'password' => $passwordHash, 'last_reset' => time()])) return false;
        $this->db->query('UPDATE `{TP}password_resets` SET `used_at` = ? WHERE `user_id` = ? AND `used_at` = 0', [time(), $userId]);
        wbce_revoke_user_sessions($userId);
        wbce_do_action('user.password.reset_completed', $userId);
        return true;
    }

    private function ensureTable(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `{TP}password_resets` (`id` BIGINT NOT NULL AUTO_INCREMENT,`user_id` INT NOT NULL,`selector` CHAR(18) NOT NULL,`token_hash` CHAR(64) NOT NULL,`expires_at` INT NOT NULL,`used_at` INT NOT NULL DEFAULT 0,`requested_at` INT NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `selector` (`selector`),INDEX `user_requested` (`user_id`,`requested_at`))');
    }
}
