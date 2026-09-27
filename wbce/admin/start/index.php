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

require('../../config.php');
// The updater finalizes install/update.php through fetch(). Restore the
// session once more at the actual dashboard request, before admin performs
// its authentication check. This is deliberately a short-lived HttpOnly
// handover and the snapshot is removed immediately after use.
$updaterRecoveryToken = isset($_COOKIE['WBCE-updater-recovery']) && is_string($_COOKIE['WBCE-updater-recovery'])
    ? $_COOKIE['WBCE-updater-recovery'] : '';
if (preg_match('/^[a-f0-9]{64}$/', $updaterRecoveryToken)) {
    $updaterRecoveryFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wbce-updater-session-' . $updaterRecoveryToken . '.json';
    $updaterRecoveryRaw = @file_get_contents($updaterRecoveryFile);
    $updaterRecovery = is_string($updaterRecoveryRaw) ? json_decode($updaterRecoveryRaw, true) : null;
    if (is_array($updaterRecovery) && is_array($updaterRecovery['session'] ?? null)) {
        $updaterRecoveryId = (string)($updaterRecovery['session_id'] ?? '');
        $updaterRecoveryName = (string)($updaterRecovery['session_name'] ?? '');
        if ($updaterRecoveryId !== '' && preg_match('/^[A-Za-z0-9_-]{1,128}$/', $updaterRecoveryName)) {
            // config.php may not have opened the PHP session yet. Start the
            // saved session explicitly in both cases, then renew its cookie.
            if (session_status() === PHP_SESSION_ACTIVE && session_id() !== $updaterRecoveryId) session_write_close();
            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_name($updaterRecoveryName);
                @session_id($updaterRecoveryId);
                @session_start();
            }
            if (session_status() === PHP_SESSION_ACTIVE && session_id() === $updaterRecoveryId) {
                foreach ($updaterRecovery['session'] as $key => $value) if (is_string($key)) $_SESSION[$key] = $value;
                @session_write_close();
                @setcookie($updaterRecoveryName, $updaterRecoveryId, array('expires'=>0,'path'=>'/','secure'=>(defined('DOMAIN_PROTOCOLL') ? DOMAIN_PROTOCOLL : 'https') === 'https','httponly'=>true,'samesite'=>'Lax'));
            }
        }
    }
    @unlink($updaterRecoveryFile);
    @setcookie('WBCE-updater-recovery', '', array('expires'=>time()-3600,'path'=>'/','secure'=>(defined('DOMAIN_PROTOCOLL') ? DOMAIN_PROTOCOLL : 'https') === 'https','httponly'=>true,'samesite'=>'Lax'));
}
require_once(WB_PATH . '/framework/Admin.php');
$admin = new admin('Start', 'start');
// The update changelog belongs to the updater module, but its dismissal is
// deliberately handled here: this authenticated dashboard controller is not
// subject to the external-module request filter and already owns the FTAN.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && (string) ($_POST['dashboard_action'] ?? '') === 'dismiss_update_changelog') {
    if (!$admin->checkFTAN()) {
        $admin->print_error('Die Sicherheitsprüfung ist fehlgeschlagen.', ADMIN_URL . '/start/index.php');
    }
    $changelogVersion = trim((string) Settings::GetDb('wbce_update_changelog_version', ''));
    if ($changelogVersion !== '') {
        Settings::Set('wbce_update_changelog_dismissed_version', $changelogVersion);
    }
    unset($_SESSION['WBCE_UPDATE_CHANGELOG']);
    header('Location: ' . ADMIN_URL . '/start/index.php');
    exit;
}
// ---------------------------------------

if (defined('FINALIZE_SETUP')) {
    require_once(WB_PATH . '/framework/functions.php');
    $dirs = array('modules' => WB_PATH . '/modules/',
        'templates' => WB_PATH . '/templates/',
        'languages' => WB_PATH . '/languages/'
    );
    foreach ($dirs as $type => $dir) {
        if (($handle = opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '' and substr($file, 0, 1) != '.' and $file != 'admin.php' and $file != 'index.php') {
                    // Get addon type
                    if ($type == 'modules') {
                        load_module($dir . '/' . $file, true);
                        // Pretty ugly hack to let modules run $admin->set_error
                        // See dummy class definition admin_dummy above
                        if (isset($admin->error) && $admin->error != '') {
                            $admin->print_error($admin->error);
                        }
                    } elseif ($type == 'templates') {
                        load_template($dir . '/' . $file);
                    } elseif ($type == 'languages') {
                        load_language($dir . '/' . $file);
                    }
                }
            }
            closedir($handle);
        }
    }
    $sql = 'DELETE FROM `' . TABLE_PREFIX . 'settings` WHERE `name`=\'FINALIZE_SETUP\'';
    if ($database->query($sql)) {
    }
}
// ---------------------------------------
$msg = '<br />';

// Setup template object, parse vars to it, then parse it
// Create new template object
$template = new Template(dirname($admin->correct_theme_source('start.htt')));
$template->set_file('page', 'start.htt');
$template->set_block('page', 'main_block', 'main');

// Insert values into the template object
$template->set_var(
    array(
        'WELCOME_MESSAGE' => $MESSAGE['START_WELCOME_MESSAGE'],
        'CURRENT_USER' => $MESSAGE['START_CURRENT_USER'],
        'DISPLAY_NAME' => $admin->get_display_name(),
        'ADMIN_URL' => ADMIN_URL,
        'WB_URL' => WB_URL,
        'THEME_URL' => THEME_URL,
        'WB_VERSION' => WB_VERSION
    )
);

// Insert permission values into the template object
if ($admin->get_permission('pages') != true) {
    $template->set_var('DISPLAY_PAGES', 'display:none;');
}
if ($admin->get_permission('media') != true) {
    $template->set_var('DISPLAY_MEDIA', 'display:none;');
}
if ($admin->get_permission('addons') != true) {
    $template->set_var('DISPLAY_ADDONS', 'display:none;');
}
if ($admin->get_permission('access') != true) {
    $template->set_var('DISPLAY_ACCESS', 'display:none;');
}
if ($admin->get_permission('settings') != true) {
    $template->set_var('DISPLAY_SETTINGS', 'display:none;');
}
if ($admin->get_permission('admintools') != true) {
    $template->set_var('DISPLAY_ADMINTOOLS', 'display:none;');
}

// Check if installation directory still exists and delete the files
if (file_exists(WB_PATH . '/install/') || file_exists(WB_PATH . '/upgrade-script.php')) {
    if (!function_exists('rm_full_dir')) {
        @require_once(WB_PATH . '/framework/functions.php');
    }
    if (file_exists(WB_PATH . '/upgrade-script.php')) {
        unlink(WB_PATH . '/upgrade-script.php');
    }
    if (file_exists(WB_PATH . '/install/')) {
        rm_full_dir(WB_PATH . '/install/');
    }
}

if ($admin->get_group_id() == 1) {
    if (file_exists(WB_PATH . '/install/')) {
        $template->set_var('DISPLAY_WARNING', 'display:block;');
        $template->set_var('WARNING', $MESSAGE['START_INSTALL_DIR_EXISTS']);
    } elseif (file_exists(WB_PATH . '/modules/SimpleCommandDispatcher.inc.php')) {
        $template->set_var('DISPLAY_WARNING', 'display:block;');
        $template->set_var('WARNING', $MESSAGE['START_WBCE_NOT_CLEAN']);
	} elseif (substr(WB_URL,-1)=="/") {
		$template->set_var('DISPLAY_WARNING', 'display:block;');
        $template->set_var('WARNING', $MESSAGE['START_WB_URL_SLASH']);	
    } else {
        $template->set_var('DISPLAY_WARNING', 'display:none;');
    }
} else {
    $template->set_var('DISPLAY_WARNING', 'display:none;');
}

$wbce_latest_release ='';

if (function_exists('curl_version') && (!defined('SHOW_UPDATE_INFO') || SHOW_UPDATE_INFO != false)) {
    include WB_PATH . '/include/GitHubApiClient/GitHubApiClient.php';
    $gitHubApiClient = new \Neoflow\GitHubApiClient('Neoflow');
    $response = $gitHubApiClient->get('/repos/WBCE/WBCE_CMS/releases/latest');
    if ($response['header']['http_code'] === 200) {
        $wbce_latest_release = $response['content']['tag_name'];
        if ($wbce_latest_release > NEW_WBCE_VERSION) {
            echo $TEXT['OLDWBCE'];
            echo '<b style="color:red">' . $wbce_latest_release . '</b><br>';
        }
    }
}

// Insert "Add-ons" section overview (pretty complex compared to normal)
$addons_overview = $TEXT['MANAGE'] . ' ';
$addons_count = 0;
if ($admin->get_permission('modules') == true) {
    $addons_overview .= '<a href="' . ADMIN_URL . '/modules/index.php">' . $MENU['MODULES'] . '</a>';
    $addons_count = 1;
}
if ($admin->get_permission('templates') == true) {
    if ($addons_count == 1) {
        $addons_overview .= ', ';
    }
    $addons_overview .= '<a href="' . ADMIN_URL . '/templates/index.php">' . $MENU['TEMPLATES'] . '</a>';
    $addons_count = 1;
}
if ($admin->get_permission('languages') == true) {
    if ($addons_count == 1) {
        $addons_overview .= ', ';
    }
    $addons_overview .= '<a href="' . ADMIN_URL . '/languages/index.php">' . $MENU['LANGUAGES'] . '</a>';
}

// Insert "Access" section overview (pretty complex compared to normal)
$access_overview = $TEXT['MANAGE'] . ' ';
$access_count = 0;
if ($admin->get_permission('users') == true) {
    $access_overview .= '<a href="' . ADMIN_URL . '/users/index.php">' . $MENU['USERS'] . '</a>';
    $access_count = 1;
}
if ($admin->get_permission('groups') == true) {
    if ($access_count == 1) {
        $access_overview .= ', ';
    }
    $access_overview .= '<a href="' . ADMIN_URL . '/groups/index.php">' . $MENU['GROUPS'] . '</a>';
    $access_count = 1;
}

// Insert section names and descriptions
$template->set_var(
    array(
        'PAGES' => $MENU['PAGES'],
        'MEDIA' => $MENU['MEDIA'],
        'ADDONS' => $MENU['ADDONS'],
        'ACCESS' => $MENU['ACCESS'],
        'PREFERENCES' => $MENU['PREFERENCES'],
        'SETTINGS' => $MENU['SETTINGS'],
        'ADMINTOOLS' => $MENU['ADMINTOOLS'],
        'HOME_OVERVIEW' => $OVERVIEW['START'],
        'PAGES_OVERVIEW' => $OVERVIEW['PAGES'],
        'MEDIA_OVERVIEW' => $OVERVIEW['MEDIA'],
        'ADDONS_OVERVIEW' => $addons_overview,
        'ACCESS_OVERVIEW' => $access_overview,
        'PREFERENCES_OVERVIEW' => $OVERVIEW['PREFERENCES'],
        'SETTINGS_OVERVIEW' => $OVERVIEW['SETTINGS'],
        'ADMINTOOLS_OVERVIEW' => $OVERVIEW['ADMINTOOLS']
    )
);

$dashboardWidgets = wbce_apply_filters('admin.dashboard.widgets', array(), (int)$admin->get_user_id(), $admin);
ob_start();
if (is_array($dashboardWidgets)) {
    foreach ($dashboardWidgets as $dashboardWidget) {
        if (is_string($dashboardWidget)) {
            echo $dashboardWidget;
            continue;
        }
        if (!is_array($dashboardWidget)) continue;
        if (isset($dashboardWidget['html']) && is_string($dashboardWidget['html'])) {
            echo $dashboardWidget['html'];
            continue;
        }
        if (!isset($dashboardWidget['title'], $dashboardWidget['content'])) continue;
        $widgetTitle = htmlspecialchars((string)$dashboardWidget['title'], ENT_QUOTES, 'UTF-8');
        $widgetUrl = isset($dashboardWidget['url']) ? (string)$dashboardWidget['url'] : '';
        echo '<section class="wbce-dashboard-hook-widget wbce-card">';
        echo '<h3>'.($widgetUrl !== '' ? '<a href="'.htmlspecialchars($widgetUrl, ENT_QUOTES, 'UTF-8').'">'.$widgetTitle.'</a>' : $widgetTitle).'</h3>';
        echo '<div class="wbce-dashboard-hook-content">'.$dashboardWidget['content'].'</div></section>';
    }
}
$dashboardWidgetsOutput = ob_get_clean();
$dashboardTemplateFile = $admin->correct_theme_source('start.htt');
$dashboardHasWidgetSlot = is_file($dashboardTemplateFile)
    && strpos((string)file_get_contents($dashboardTemplateFile), '{DASHBOARD_WIDGETS}') !== false;
if ($dashboardHasWidgetSlot) {
    $template->set_var('DASHBOARD_WIDGETS', $dashboardWidgetsOutput);
}

// Parse template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');
if (!$dashboardHasWidgetSlot) {
    echo $dashboardWidgetsOutput;
}

// Print admin footer
$admin->print_footer();
