<?php
/**
 * Temporary PHP 8.2-8.4 compatibility boundary for the PHP 8.5 based core.
 *
 * Do not add business logic here. Once PHP 8.2 support is dropped, calls to
 * these resource cleanup helpers can be removed together with this file:
 * PHP 8.5 manages the affected objects automatically.
 */
final class WbcePhp82Compatibility
{
    /** PHP 8.2/8.3 fallback for array_find(), introduced with PHP 8.4. */
    public static function arrayFind(array $values, callable $callback)
    {
        if (function_exists('array_find')) {
            return array_find($values, $callback);
        }
        foreach ($values as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return null;
    }

    /** PHP 8.2/8.3 fallback for mb_ucfirst(), introduced with PHP 8.4. */
    public static function mbUcfirst(string $value, ?string $encoding = null): string
    {
        if (function_exists('mb_ucfirst')) {
            return mb_ucfirst($value, $encoding);
        }
        $encoding = $encoding ?: mb_internal_encoding();
        return mb_strtoupper(mb_substr($value, 0, 1, $encoding), $encoding)
            . mb_substr($value, 1, null, $encoding);
    }

    /** PHP 8.2/8.3 fallback for http_get_last_response_headers() from PHP 8.4. */
    public static function lastHttpResponseHeaders(array $php82Headers = array()): array
    {
        if (function_exists('http_get_last_response_headers')) {
            return http_get_last_response_headers() ?: array();
        }
        return $php82Headers;
    }

    /** Resource cleanup retained only for PHP 8.2, 8.3 and 8.4. */
    public static function closeCurl($handle): void
    {
        if (PHP_VERSION_ID < 80500 && function_exists('curl_close') && $handle !== null) {
            curl_close($handle);
        }
    }

    public static function closeFileInfo($fileInfo): void
    {
        if (PHP_VERSION_ID < 80500 && function_exists('finfo_close') && $fileInfo !== false) {
            finfo_close($fileInfo);
        }
    }

    public static function destroyGdImage($image): void
    {
        if (PHP_VERSION_ID < 80500 && function_exists('imagedestroy') && $image !== false) {
            imagedestroy($image);
        }
    }

    public static function closeXmlParser($parser): void
    {
        if (PHP_VERSION_ID < 80500 && function_exists('xml_parser_free') && $parser !== false) {
            xml_parser_free($parser);
        }
    }
}
