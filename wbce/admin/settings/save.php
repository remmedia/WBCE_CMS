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

// AJAX saves use the same validation and persistence path as regular submits.
// Buffer legacy Admin output so errors that terminate the script can still be
// returned as a concise JSON response to the settings form.
$isAsyncSave = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$settingsSaveSucceeded = false;
if ($isAsyncSave) {
    ob_start();
    register_shutdown_function(function () use (&$settingsSaveSucceeded) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode(array(
            'success' => $settingsSaveSucceeded,
            'message' => $settingsSaveSucceeded
                ? 'Die Einstellungen wurden gespeichert.'
                : 'Die Einstellungen konnten nicht gespeichert werden. Bitte prüfen Sie die Eingaben.',
        ));
    });
}

// prevent this file from being accessed directly in the browser (would set all entries in DB settings table to '')
if (!isset($_POST['default_language']) || $_POST['default_language'] == '') {
    die(header('Location: index.php'));
}

// Find out if the user was view advanced options or not
$advanced = ($_POST['advanced'] == 'yes') ? '?advanced=yes' : '';

// Print admin header
require '../../config.php';
require_once WB_PATH . '/framework/Admin.php';

// Compatibility with admin templates which used the descriptive field name.
// The canonical setting remains `default_timezone` in the settings table.
if (isset($_POST['default_timezone_identifier']) && !isset($_POST['default_timezone'])) {
    $_POST['default_timezone'] = $_POST['default_timezone_identifier'];
}

// suppress to print the header, so no new FTAN will be set
if ($advanced == '') {
    $admin = new admin('Settings', 'settings_basic', false);
} else {
    $admin = new admin('Settings', 'settings_advanced', false);
}

// Create a javascript back link
$js_back = ADMIN_URL . '/settings/index.php' . $advanced;
if (!$admin->checkFTAN()) {
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $js_back);
}
// After check print the header
$admin->print_header();

// Ensure that the specified default email is formally valid
if (isset($_POST['server_email'])) {
    $_POST['server_email'] = strip_tags($_POST['server_email']);
    if (!$admin->validate_email($_POST['server_email'])) {
        $admin->print_error($MESSAGE['USERS_INVALID_EMAIL'] .
            '<br /><strong>Email: ' . htmlentities($_POST['server_email']) . '</strong>', $js_back);
    }
}

// rename pages/intro.php into intro.backup.php when intro page is disabled
// so that intro.php is not accessible from search engine results anymore
if (isset($_POST['intro_page'])) {
    $sLoc = WB_PATH . PAGES_DIRECTORY;
    if ($_POST['intro_page'] == 'true') {
        if (file_exists($sLoc . '/intro.backup' . PAGE_EXTENSION)) {
            rename($sLoc . '/intro.backup' . PAGE_EXTENSION, $sLoc . '/intro' . PAGE_EXTENSION);
        }
    } elseif ($_POST['intro_page'] == 'false') {
        if (file_exists($sLoc . '/intro' . PAGE_EXTENSION)) {
            rename($sLoc . '/intro' . PAGE_EXTENSION, $sLoc . '/intro.backup' . PAGE_EXTENSION);
        }
    }
}

if (isset($_POST['wbmailer_routine']) && ($_POST['wbmailer_routine'] == 'smtp')) {
    $checkSmtpHost = (isset($_POST['wbmailer_smtp_host']) && ($_POST['wbmailer_smtp_host'] == '') ? false : true);
    $checkSmtpUser = (isset($_POST['wbmailer_smtp_username']) && ($_POST['wbmailer_smtp_username'] == '') ? false : true);
    $checkSmtpPassword = (isset($_POST['wbmailer_smtp_password']) && ($_POST['wbmailer_smtp_password'] == '') ? false : true);
    if (!$checkSmtpHost || !$checkSmtpUser || !$checkSmtpPassword) {
        $admin->print_error($TEXT['REQUIRED'] . ' ' . $TEXT['WBMAILER_SMTP_AUTH'] .
            '<br /><strong>' . $MESSAGE['GENERIC_FILL_IN_ALL'] . '</strong>', $js_back);
    }
}

if (isset($_POST['app_name'])) {
	$pattern = '/^[a-z0-9_-]*$/';
	if (false == preg_match($pattern, $_POST['app_name']) || $_POST['app_name'] == '') {
		$admin->print_error($MESSAGE['INVALID_SESSION_NAME'], $js_back);
	}
}

// Work-out file mode
if ($advanced == '') {
    // Check if should be set to 777 or left alone
    if (isset($_POST['world_writeable']) && $_POST['world_writeable'] == 'true') {
        $file_mode = '0777';
        $dir_mode = '0777';
    } else {
        $file_mode = STRING_FILE_MODE;
        $dir_mode = STRING_DIR_MODE;
    }
} else {
    $file_mode = STRING_FILE_MODE;
    $dir_mode = STRING_DIR_MODE;
    if ($admin->get_user_id() == '1') {
        // Work-out the octal value for file mode
        $u = 0;
        if (isset($_POST['file_u_r']) && $_POST['file_u_r'] == 'true') {
            $u = $u + 4;
        }
        if (isset($_POST['file_u_w']) && $_POST['file_u_w'] == 'true') {
            $u = $u + 2;
        }
        if (isset($_POST['file_u_e']) && $_POST['file_u_e'] == 'true') {
            $u = $u + 1;
        }
        $g = 0;
        if (isset($_POST['file_g_r']) && $_POST['file_g_r'] == 'true') {
            $g = $g + 4;
        }
        if (isset($_POST['file_g_w']) && $_POST['file_g_w'] == 'true') {
            $g = $g + 2;
        }
        if (isset($_POST['file_g_e']) && $_POST['file_g_e'] == 'true') {
            $g = $g + 1;
        }
        $o = 0;
        if (isset($_POST['file_o_r']) && $_POST['file_o_r'] == 'true') {
            $o = $o + 4;
        }
        if (isset($_POST['file_o_w']) && $_POST['file_o_w'] == 'true') {
            $o = $o + 2;
        }
        if (isset($_POST['file_o_e']) && $_POST['file_o_e'] == 'true') {
            $o = $o + 1;
        }
        $file_mode = "0" . $u . $g . $o;
        // Work-out the octal value for dir mode
        $u = 0;
        if (isset($_POST['dir_u_r']) && $_POST['dir_u_r'] == 'true') {
            $u = $u + 4;
        }
        if (isset($_POST['dir_u_w']) && $_POST['dir_u_w'] == 'true') {
            $u = $u + 2;
        }
        if (isset($_POST['dir_u_e']) && $_POST['dir_u_e'] == 'true') {
            $u = $u + 1;
        }
        $g = 0;
        if (isset($_POST['dir_g_r']) && $_POST['dir_g_r'] == 'true') {
            $g = $g + 4;
        }
        if (isset($_POST['dir_g_w']) && $_POST['dir_g_w'] == 'true') {
            $g = $g + 2;
        }
        if (isset($_POST['dir_g_e']) && $_POST['dir_g_e'] == 'true') {
            $g = $g + 1;
        }
        $o = 0;
        if (isset($_POST['dir_o_r']) && $_POST['dir_o_r'] == 'true') {
            $o = $o + 4;
        }
        if (isset($_POST['dir_o_w']) && $_POST['dir_o_w'] == 'true') {
            $o = $o + 2;
        }
        if (isset($_POST['dir_o_e']) && $_POST['dir_o_e'] == 'true') {
            $o = $o + 1;
        }
        $dir_mode = "0" . $u . $g . $o;
    }
}

$allow_tags_in_fields = array('website_header', 'website_footer');
$allow_empty_values = array('website_header', 'website_footer', 'sec_anchor', 'pages_directory', 'page_spacer');
$disallow_in_fields = array('pages_directory', 'media_directory', 'wb_version');

// Query current settings in the db, then loop through them and update the db with the new value
$settings = array();
$old_settings = array();
$changed_settings = array();
// Query current settings in the db, then loop through them to get old values
$sql = "SELECT `name`, `value` FROM `{TP}settings` ORDER BY `name`";
$res_settings = $database->query($sql);
if ($res_settings === false) {
    $admin->print_error($database->getError(), $js_back);
}
if ($res_settings) {
    $passed = false;
    while ($setting = $res_settings->fetchRow()) {
        $old_settings[$setting['name']] = $setting['value'];
        $setting_name = $setting['name'];
        $value = $admin->get_post($setting_name);
        $value = isset($_POST[$setting_name]) ? $value : $old_settings[$setting_name];
        switch ($setting_name) {
            case 'default_timezone':
                $value = trim((string)$value);
                if (!in_array($value, DateTimeZone::listIdentifiers(DateTimeZone::ALL), true)) {
                    $value = wbce_timezone_identifier($old_settings[$setting_name] ?? 'UTC');
                }
                $passed = true;
                break;
            case 'string_dir_mode':
                $value = $dir_mode;
                $passed = true;
                break;
            case 'string_file_mode':
                $value = $file_mode;
                $passed = true;
                break;
            case 'pages_directory':
                break;
            case 'wbmailer_smtp_auth':
                // Legacy fallback settings are managed only by the Mailer tool.
                // Keep their existing value when the basic-settings form omits them.
                $value = isset($_POST[$setting_name]) ? $_POST[$setting_name] : $old_settings[$setting_name];
                $passed = isset($_POST[$setting_name]);
                break;
            default:
                $passed = in_array($setting_name, $allow_empty_values);
                break;
        }

        if (!in_array($setting_name, $allow_tags_in_fields)) {
            $value = strip_tags($value);
			if ($setting_name != "website_title") {
				$value = str_replace( array( '\'','"',';','<','>','%','$','\\' ), '', $value);
			} 
        }

        if (!in_array($value, $disallow_in_fields) && (isset($_POST[$setting_name]) || $passed == true)) {
            $value = trim($value);
            $sSql = "UPDATE `{TP}settings` SET `value`='" . $database->escapeString($value) . "' "
                . "WHERE `name` != 'wb_version' AND `name`= '" . $setting_name . "'";
            $database->query($sSql);
            if ($database->hasError()) {
                $admin->print_error($database->getError(), $js_back);
                break;
            }
            if ((string)$value !== (string)$old_settings[$setting_name]) {
                $changed_settings[$setting_name] = ['old' => $old_settings[$setting_name], 'new' => $value];
            }
        }
    }
}

if ($changed_settings !== []) {
    wbce_do_action('settings.updated', $changed_settings);
    if (isset($changed_settings['default_template'])) wbce_do_action('template.activated', $changed_settings['default_template']);
    if (isset($changed_settings['default_theme'])) wbce_do_action('adminTemplate.activated', $changed_settings['default_theme']);
    if (isset($changed_settings['wb_maintainance_mode'])) {
        wbce_do_action(((int)$changed_settings['wb_maintainance_mode']['new'] === 1)
            ? 'system.maintenance.started' : 'system.maintenance.completed', $changed_settings['wb_maintainance_mode']);
    }
    wbce_do_action('cache.invalidate', 'settings', array_keys($changed_settings));
}

// Query current search settings in the db, then loop through them and update the db with the new value
$sql = "SELECT `name`, `value` FROM `{TP}search` WHERE `extra`= ''";
if (!($res_search = $database->query($sql))) {
    $admin->print_error($database->getError(), $js_back);
}
while ($search_setting = $res_search->fetchRow()) {
    $old_value = $search_setting['value'];
    $setting_name = $search_setting['name'];
    $post_name = 'search_' . $search_setting['name'];

    // hold old value if post is empty
    // check search template
    $value = (($admin->get_post($post_name) == '') && ($setting_name != 'template'))
        ? $old_value
        : $admin->get_post($post_name);
    if (isset($value)) {
        $value = $admin->add_slashes($value);
        $sSql = "UPDATE `{TP}search` SET `value`= '" . $value . "' "
            . "WHERE `name`='" . $setting_name . "' AND `extra`=''";
        if ($database->query($sSql) === false) {
            $admin->print_error($database->getError(), $js_back);
            break;
        }
        // $sql_info = mysql_info($database->db_handle); //->> nicht mehr erforderlich
    }
}

$settingsSaveSucceeded = true;
$admin->print_success($MESSAGE['SETTINGS_SAVED'], $js_back);
$admin->print_footer();
