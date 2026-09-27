<?php
/**
 * WBCE CMS
 * Way Better Content Editing.
 * Visit https://wbce.org to learn more and to join the community.
 *
 * @copyright Ryan Djurovich (2004-2009)
 * @copyright WebsiteBaker Org. e.V. (2009-2015)
 * @copyright WBCE Project (2015-)
 * @license GNU GPL2 (or any later version)
 */

if (!defined('WB_URL')) {
    header('Location: ../../../index.php');
    exit(0);
}

defined('TIMEZONES_LOADED') or define('TIMEZONES_LOADED', true);

$selected_timezone = isset($_SESSION['TIMEZONE']) && $_SESSION['TIMEZONE'] !== ''
    ? wbce_timezone_identifier($_SESSION['TIMEZONE']) : wbce_timezone_identifier(DEFAULT_TIMEZONE);
$TIMEZONES = array();
$now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $identifier) {
    $offset = (new DateTimeZone($identifier))->getOffset($now);
    $sign = $offset < 0 ? '-' : '+';
    $absolute = abs($offset);
    $TIMEZONES[$identifier] = sprintf(
        '(UTC%s%02d:%02d) %s', $sign, intdiv($absolute, 3600), intdiv($absolute % 3600, 60), $identifier
    );
}
uasort($TIMEZONES, static fn($left, $right) => strnatcasecmp($left, $right));
if (isset($user_time) && $user_time === true) {
    global $TEXT;
    $TIMEZONES = array('' => ($TEXT['SYSTEM_DEFAULT'] ?? 'System default') . ' (' . wbce_timezone_identifier(DEFAULT_TIMEZONE) . ')') + $TIMEZONES;
}

if (!function_exists('getTimeZonesArray')) {

    /**
     * @brief  Returns an array of timezones set up by the system
     *         This function will return an array that can be used
     *         to display all the timezones or in order to create a
     *         select box to choose from.
     *
     * @param array $TIMEZONES
     * @return array
     */
    function getTimeZonesArray($TIMEZONES)
    {
        global $selected_timezone;

        $aTimeZones = array();
        $i = 0;
        foreach ($TIMEZONES as $iOffset => $sTitle) {
            $aTimeZones[$i]['VALUE'] = $iOffset;
            $aTimeZones[$i]['NAME'] = $sTitle;

            $aTimeZones[$i]['SELECTED'] = false;
            $aTimeZones[$i]['SELECTED'] = ($iOffset === '' && isset($_SESSION['USE_DEFAULT_TIMEZONE']))
                || (!isset($_SESSION['USE_DEFAULT_TIMEZONE']) && $iOffset === $selected_timezone);
            $i++;
        }
        return $aTimeZones;
    }
}
