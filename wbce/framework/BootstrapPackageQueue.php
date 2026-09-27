<?php
/**
 * Installs the package queue prepared by the standalone Bootstrap Installer.
 * The queue is deliberately consumed only after the CMS tables and bundled
 * addons exist, so external packages see a complete WBCE runtime.
 */
defined('WB_PATH') or die('No direct access allowed');

final class BootstrapPackageQueue
{
    private const MAX_BYTES = 536870912;

    /** @return array{installed:int,failed:int} */
    public static function run(string $path, AddonService $addons, callable $report): array
    {
        if (!is_file($path)) return ['installed' => 0, 'failed' => 0];
        $raw = file_get_contents($path);
        $queue = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($queue) || ($queue['schema'] ?? null) !== 1 || !is_array($queue['packages'] ?? null)) {
            $report('warn', 'Die Bootstrap-Paketwarteschlange ist ungültig und wurde übersprungen.');
            return ['installed' => 0, 'failed' => 1];
        }
        $origin = self::trustedOrigin((string)($queue['store_url'] ?? ''));
        if ($origin === null) {
            $report('warn', 'Die Bootstrap-Paketwarteschlange hat keine gültige Store-Adresse.');
            return ['installed' => 0, 'failed' => 1];
        }
        $token = (string)($queue['access_token'] ?? '');
        $installed = $failed = 0;
        foreach ($queue['packages'] as $package) {
            if (!is_array($package) || !self::validPackage($package, $origin)) {
                $report('warn', 'Ein Eintrag der Bootstrap-Paketwarteschlange ist ungültig.');
                $failed++;
                continue;
            }
            $label = (string)$package['type'].':'.(string)$package['slug'].' '.(string)$package['version'];
            $zip = tempnam(WB_PATH.'/temp', 'wbce-bootstrap-addon-');
            if ($zip === false) {
                $report('warn', $label.': temporäre Datei konnte nicht angelegt werden.');
                $failed++;
                continue;
            }
            try {
                self::download((string)$package['download_url'], $token, $zip, (int)$package['size'], (string)$package['sha256']);
                $signals = $addons->stageFromZip($zip, true);
                if (self::signalsFailed($signals)) throw new RuntimeException(self::signalText($signals));
                $signals = $addons->installFromStaged((string)$package['slug'], (string)$package['type']);
                if (self::signalsFailed($signals)) throw new RuntimeException(self::signalText($signals));
                $report('ok', $label);
                $installed++;
            } catch (Throwable $error) {
                $report('warn', $label.': '.$error->getMessage());
                $failed++;
            } finally {
                @unlink($zip);
            }
        }
        // A failed queue remains in place so an administrator can inspect and
        // retry it; do not leave a bearer token after a successful run.
        if ($failed === 0) @unlink($path);
        return ['installed' => $installed, 'failed' => $failed];
    }

    private static function trustedOrigin(string $url): ?array
    {
        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || !is_string($parts['host'] ?? null)
            || $parts['host'] === '' || isset($parts['user']) || isset($parts['pass'])) return null;
        return $parts;
    }

    private static function validPackage(array $package, array $origin): bool
    {
        $type = (string)($package['type'] ?? '');
        $slug = (string)($package['slug'] ?? '');
        $url = self::trustedOrigin((string)($package['download_url'] ?? ''));
        return in_array($type, ['module', 'template', 'language'], true)
            && preg_match('/^[a-z0-9_.-]+$/i', $slug) === 1
            && $url !== null && strcasecmp((string)$url['host'], (string)$origin['host']) === 0
            && preg_match('/^[a-f0-9]{64}$/i', (string)($package['sha256'] ?? '')) === 1
            && (int)($package['size'] ?? 0) > 0 && (int)$package['size'] <= self::MAX_BYTES;
    }

    private static function download(string $url, string $token, string $target, int $size, string $sha256): void
    {
        if (!function_exists('curl_init')) throw new RuntimeException('cURL ist nicht verfügbar.');
        $stream = fopen($target, 'wb');
        if ($stream === false) throw new RuntimeException('Paket kann nicht geschrieben werden.');
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_FILE => $stream, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 600,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $token !== '' ? ['Authorization: Bearer '.$token] : [],
            CURLOPT_USERAGENT => 'WBCE-Bootstrap-Queue/'.(defined('WBCE_VERSION') ? WBCE_VERSION : '1.7'),
        ]);
        $ok = curl_exec($curl); $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE); curl_close($curl); fclose($stream);
        if (!$ok || $status < 200 || $status >= 300 || filesize($target) !== $size || !hash_equals(strtolower($sha256), hash_file('sha256', $target))) {
            throw new RuntimeException('Download oder Prüfsumme ist ungültig.');
        }
    }

    private static function signalsFailed(array $signals): bool
    {
        $successful = [
            'ADDON_DETECTED', 'ADDON_INSERTED_OK', 'ADDON_UPDATED_OK',
            'ADDON_SCRIPT_OK', 'ADDON_EXTRACT_OK', 'ADDON_PRECHECK_OK',
            'ADDON_FETCH_OK', 'ADDON_UP_TO_DATE', 'ADDON_ALREADY_CURRENT',
            'ADDON_SCRIPT_NOT_FOUND', 'ADDON_STAGED', 'ADDON_SECURITY_WARNING',
        ];
        foreach ($signals as $signal) if (!in_array((string)($signal['signal'] ?? ''), $successful, true)) return true;
        return false;
    }

    private static function signalText(array $signals): string
    {
        $labels = array_filter(array_map(static fn(array $signal): string => (string)($signal['label'] ?? ''), $signals));
        return $labels ? implode('; ', $labels) : 'Paket konnte nicht installiert werden.';
    }
}
