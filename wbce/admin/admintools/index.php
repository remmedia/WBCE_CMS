<?php
/** WBCE CMS — Admin Tools overview */
require '../../config.php';
require_once WB_PATH . '/framework/i18n/init.php';

// Twig translations maintain their own registry.  The legacy bootstrap has
// already selected LANGUAGE for the logged-in administrator, so apply it
// before loading the core and page-specific language files.
$adminToolsLocale = defined('LANGUAGE') ? strtoupper((string) LANGUAGE) : 'EN';
Lang::setLocale($adminToolsLocale);
Lang::loadCore($adminToolsLocale, WB_PATH . '/languages');
Lang::loadLanguage(__DIR__);
$admin = new Admin('admintools', 'admintools', false);
$defaults = array('columns' => 1, 'show_search' => true, 'most_used_first' => false);
$raw = Settings::Get('admintools_config');
$cfg = array_merge($defaults, $raw ? (json_decode($raw, true) ?: array()) : array());

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['save_admintools_settings'])) {
    if (!$admin->checkFTAN()) {
        if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
            http_response_code(403);
            echo '<div class="error">' . htmlspecialchars($MESSAGE['GENERIC_SECURITY_ACCESS'], ENT_QUOTES, 'UTF-8') . '</div>';
            exit;
        }
        $admin->print_header();
        $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL . '/admintools/');
    }
    $cfg = array(
        'columns' => (int) $admin->get_post('columns') === 2 ? 2 : 1,
        'show_search' => $admin->get_post('show_search') !== null,
        'most_used_first' => $admin->get_post('most_used_first') !== null,
    );
    Settings::Set('admintools_config', json_encode($cfg));
    if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
        header('HX-Trigger: {"settingsSaved": true}');
        echo '<div class="success">' . htmlspecialchars($MESSAGE['SETTINGS_SAVED'], ENT_QUOTES, 'UTF-8') . '</div>';
        exit;
    }
}

$admin->print_header();
$tools = array();
if (($toolResult = $database->query("SELECT * FROM `{TP}addons` WHERE `type` = 'module' AND `function` LIKE '%tool%' AND `function` NOT LIKE '%hidden%' ORDER BY `name`"))) {
while ($addon = $toolResult->fetchRow()) {
    $directory = (string) $addon['directory'];
    if (str_starts_with($directory, 'tool_')) continue;
    if (!$admin->isAdmin() && !in_array($directory . '_tool', (array) $admin->get_session('MODULE_PERMISSIONS'), true)) continue;
    $icon = 'fa fa-graduation-cap';
    $iconIsSvg = false;
    $moduleInfo = @file_get_contents(WB_PATH . '/modules/' . $directory . '/info.php');
    if (is_string($moduleInfo)) {
        $moduleIcon = get_variable_content('module_icon', $moduleInfo, false, false);
        if (is_string($moduleIcon) && trim($moduleIcon) !== '') {
            $icon = trim($moduleIcon);
            $iconIsSvg = stripos($icon, '<svg') !== false;
        }
    }
    $tools[(int) $addon['addon_id']] = array(
        'dir' => $directory,
        'name' => $admin->get_module_name($directory),
        'descr' => $admin->get_module_description($directory),
        'icon' => $icon,
        'icon_svg' => $iconIsSvg,
    );
}
}

$admin->getThemeFile('admintools.twig', array(
    'admin_tools' => $tools,
    'cfg' => $cfg,
    'can_change_settings' => $admin->isAdmin() || $admin->get_permission('admintools_settings'),
));
$admin->print_footer();
