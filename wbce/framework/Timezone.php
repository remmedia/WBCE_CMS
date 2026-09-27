<?php

/** UTC is used for storage; an IANA identifier is used for presentation. */
function wbce_timezone_identifier(mixed $value = null): string
{
    if ($value === null && isset($_SESSION['TIMEZONE'])) $value = $_SESSION['TIMEZONE'];
    if ($value === null && defined('DEFAULT_TIMEZONE')) $value = DEFAULT_TIMEZONE;
    if (is_string($value) && $value !== '') {
        try { new DateTimeZone($value); return $value; } catch (Throwable) { }
    }
    if (is_numeric($value)) {
        $numericValue = (float) $value;
        // Valid civil time zones range from UTC-12 through UTC+14. Older
        // WBCE data or failed conversions can contain PHP_INT_MAX; never turn
        // such values into an invalid DateTimeZone offset identifier.
        if (!is_finite($numericValue) || abs($numericValue) > 50400) {
            return 'UTC';
        }
        $seconds = (int) $numericValue;
        // WBCE 1.x commonly stored the German/Central-European selection as
        // the fixed offset 3600 (some older forms used the hour value 1).
        // A fixed +01:00 offset is one hour late during daylight-saving time.
        if ($seconds === 3600 || $seconds === 1) return 'Europe/Berlin';
        $sign = $seconds < 0 ? '-' : '+';
        $seconds = abs($seconds);
        return sprintf('%s%02d:%02d', $sign, intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
    }
    return 'UTC';
}

function wbce_timezone(mixed $value = null): DateTimeZone
{
    return new DateTimeZone(wbce_timezone_identifier($value));
}

function wbce_timezone_offset(?DateTimeInterface $moment = null, mixed $value = null): int
{
    $instant = $moment ? DateTimeImmutable::createFromInterface($moment) : new DateTimeImmutable('now');
    return wbce_timezone($value)->getOffset($instant);
}

function wbce_format_timestamp(int $timestamp, ?string $format = null, mixed $timezone = null): string
{
    $format ??= (defined('DATE_FORMAT') ? DATE_FORMAT : 'd.m.Y') . ' '
        . (defined('TIME_FORMAT') ? TIME_FORMAT : 'H:i');
    return (new DateTimeImmutable('@' . $timestamp))->setTimezone(wbce_timezone($timezone))->format($format);
}

/** Convert a UTC database datetime to the selected WBCE timezone. */
function wbce_format_utc_datetime(string $value, ?string $format = null, mixed $timezone = null): string
{
    $value = trim($value);
    if ($value === '' || $value === '0000-00-00 00:00:00') return '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, new DateTimeZone('UTC'));
    if (!$date) {
        try { $date = new DateTimeImmutable($value, new DateTimeZone('UTC')); } catch (Throwable) { return $value; }
    }
    $format ??= (defined('DATE_FORMAT') ? DATE_FORMAT : 'd.m.Y') . ' '
        . (defined('TIME_FORMAT') ? TIME_FORMAT : 'H:i');
    return $date->setTimezone(wbce_timezone($timezone))->format($format);
}

/** Parse a date/time entered in the selected WBCE timezone into a UTC timestamp. */
function wbce_parse_local_datetime(string $value, ?string $format = null, mixed $timezone = null): int|false
{
    $value = trim($value);
    if ($value === '') return false;
    $zone = wbce_timezone($timezone);
    if ($format !== null && $format !== '') {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value, $zone);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && ($errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0))) {
            return $date->getTimestamp();
        }
    }
    try { return (new DateTimeImmutable($value, $zone))->getTimestamp(); } catch (Throwable) { return false; }
}
