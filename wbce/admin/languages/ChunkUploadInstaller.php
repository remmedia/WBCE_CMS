<?php
final class WbceLanguageChunkUploadInstaller
{
    public static function install(string $uploadId, bool $overwrite): string
    {
        $upload = $_SESSION['wbce_addon_chunk_uploads'][$uploadId] ?? null;
        if (!is_array($upload) || empty($upload['complete']) || ($upload['package_type'] ?? '') !== 'language') {
            throw new RuntimeException('Das Sprachpaket ist nicht verfügbar.');
        }
        $archive = (string) ($upload['path'] ?? '');
        if (!is_file($archive) || !str_starts_with(realpath($archive) ?: '', realpath(WB_PATH . '/temp/addon_chunks') ?: '')) {
            throw new RuntimeException('Das Sprachpaket ist ungültig.');
        }
        [$name, $source] = self::languageFile($archive);
        $code = strtoupper(substr($name, 0, 2));
        $target = WB_PATH . '/languages/' . $code . '.php';
        if (is_file($target) && !$overwrite) {
            throw new RuntimeException('Die Sprache ' . $code . ' ist bereits installiert.');
        }
        $temporary = $target . '.upload-' . bin2hex(random_bytes(8));
        if (file_put_contents($temporary, $source, LOCK_EX) === false || !rename($temporary, $target)) {
            @unlink($temporary);
            throw new RuntimeException('Die Sprachdatei konnte nicht gespeichert werden.');
        }
        load_language($target);
        @unlink($archive);
        unset($_SESSION['wbce_addon_chunk_uploads'][$uploadId]);
        return $code;
    }

    private static function languageFile(string $archive): array
    {
        $matches = array();
        $zip = new ZipArchive();
        if ($zip->open($archive) !== true) throw new RuntimeException('Das Sprachpaket kann nicht gelesen werden.');
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            $stat = $zip->statIndex($i);
            if (str_contains($name, '..') || !is_array($stat) || (int) ($stat['size'] ?? 0) > 1048576 || !preg_match('/(?:^|\/)([A-Z]{2})\.php$/', $name, $code)) continue;
            $source = (string) $zip->getFromIndex($i);
            if (preg_match('/\$language_name\s*=/', $source)) $matches[] = array($code[1] . '.php', $source);
        }
        $zip->close();
        if (count($matches) !== 1) throw new RuntimeException('Das ZIP muss genau eine gültige Sprachdatei enthalten.');
        return $matches[0];
    }
}
