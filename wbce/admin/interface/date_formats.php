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

// Define that this file is loaded
if (!defined('DATE_FORMATS_LOADED')) {
    define('DATE_FORMATS_LOADED', true);
}

// Create array
$DATE_FORMATS = array();

// Get the current time (in the users timezone if required)
$preview_timezone = (isset($user_time) && $user_time == true)
    ? null
    : (defined('DEFAULT_TIMEZONE') ? DEFAULT_TIMEZONE : 'UTC');
$actual_time = time();

// Add values to list
$DATE_FORMATS['l,|jS|F,|Y'] = wbce_format_timestamp($actual_time, 'l, jS F, Y', $preview_timezone);
$DATE_FORMATS['jS|F,|Y'] = wbce_format_timestamp($actual_time, 'jS F, Y', $preview_timezone);
$DATE_FORMATS['d|M|Y'] = wbce_format_timestamp($actual_time, 'd M Y', $preview_timezone);
$DATE_FORMATS['M|d|Y'] = wbce_format_timestamp($actual_time, 'M d Y', $preview_timezone);
$DATE_FORMATS['D|M|d,|Y'] = wbce_format_timestamp($actual_time, 'D M d, Y', $preview_timezone);
$DATE_FORMATS['d-m-Y'] = wbce_format_timestamp($actual_time, 'd-m-Y', $preview_timezone) . ' (D-M-Y)';
$DATE_FORMATS['m-d-Y'] = wbce_format_timestamp($actual_time, 'm-d-Y', $preview_timezone) . ' (M-D-Y)';
$DATE_FORMATS['d.m.Y'] = wbce_format_timestamp($actual_time, 'd.m.Y', $preview_timezone) . ' (D.M.Y)';
$DATE_FORMATS['m.d.Y'] = wbce_format_timestamp($actual_time, 'm.d.Y', $preview_timezone) . ' (M.D.Y)';
$DATE_FORMATS['d/m/Y'] = wbce_format_timestamp($actual_time, 'd/m/Y', $preview_timezone) . ' (D/M/Y)';
$DATE_FORMATS['m/d/Y'] = wbce_format_timestamp($actual_time, 'm/d/Y', $preview_timezone) . ' (M/D/Y)';
$DATE_FORMATS['j.n.Y'] = wbce_format_timestamp($actual_time, 'j.n.Y', $preview_timezone) . ' (j.n.Y)';

// Add "System Default" to list (if we need to)
if (isset($user_time) && $user_time == true) {
    global $TEXT;
    $DATE_FORMATS['system_default'] = wbce_format_timestamp($actual_time, DEFAULT_DATE_FORMAT, $preview_timezone) . ' (' . $TEXT['SYSTEM_DEFAULT'] . ')';
}

// Reverse array so "System Default" is at the top
$DATE_FORMATS = array_reverse($DATE_FORMATS, true);

if (!function_exists('getDateFormatsArray')) {

    /**
     * @brief  Returns an array of date formats set up by the system
     *         This function will return an array that can be used
     *         to display all the date formats or in order to create
     *         a select box to choose from.
     *
     * @param array $DATE_FORMATS
     * @return array
     */
    function getDateFormatsArray($DATE_FORMATS)
    {
        $aDateFormats = array();
        $i = 0;
        foreach ($DATE_FORMATS as $sFormat => $sTitle) {
            $sFormat = str_replace('|', ' ', $sFormat); // Adds white-spaces (not able to be stored in array key)

            $aDateFormats[$i]['VALUE'] = ($sFormat != 'system_default') ? $sFormat : '';
            $aDateFormats[$i]['NAME'] = $sTitle;

            $aDateFormats[$i]['SELECTED'] = false;
            if (DATE_FORMAT == $sFormat && !isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) {
                $aDateFormats[$i]['SELECTED'] = true;
            } elseif ($sFormat == 'system_default' && isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) {
                $aDateFormats[$i]['SELECTED'] = true;
            }
            $i++;
        }
        return $aDateFormats;
    }
}
