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

/*****************************
 * Functions Section
 * This needs to stay up here !!!
 *****************************/

if (!defined("WB_UPGRADE_SCRIPT")) {
    define("WB_UPGRADE_SCRIPT", true);
}

// function to extract var/value-pair from DB
function db_get_key_value($table, $key)
{
    global $database;
    $table = TABLE_PREFIX . $table;
    $sql = "SELECT value FROM $table WHERE name = '$key' LIMIT 1";
    return $database->get_one($sql);
}

// function to add a var/value-pair into settings-table
function db_add_key_value($key, $value)
{
    global $database;
    global $OK;
    global $FAIL;
    $table = TABLE_PREFIX . 'settings';
    $query = $database->query("SELECT `value` FROM $table WHERE `name` = '$key' LIMIT 1");
    if ($query->numRows() > 0) {
        echo "$key: already exists. $OK.<br />";
        return true;
    } else {
        $database->query("INSERT INTO $table (`name`,`value`) VALUES ('$key', '$value')");
        echo($database->is_error() ? $database->get_error() . '<br />' : '');
        $query = $database->query("SELECT `value` FROM $table WHERE `name` = '$key' LIMIT 1");
        if ($query->numRows() > 0) {
            echo "$key: $OK.<br />";
            return true;
        } else {
            echo "$key: $FAIL!<br />";
            return false;
        }
    }
}

// function to add a new field into a table
function wbce_update_progress(string $message): void
{
    echo '<script>try{window.parent.postMessage('.json_encode(['type'=>'wbce-updater-progress','message'=>'Datenbank: '.$message,'kind'=>'step'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).',window.location.origin)}catch(e){}</script>';
    echo str_repeat(' ', 2048); @ob_flush(); @flush();
}
function wbce_column_needs_change(string $table, string $column, string $expected): bool
{
    global $database; $row=$database->fetchRow('SHOW COLUMNS FROM `'.$table.'` LIKE ?',[$column]);
    return !is_array($row) || stripos((string)($row['Type']??''), $expected) === false;
}

/** Remove a retired add-on directory without depending on its own helper files. */
function wbce_remove_retired_addon_directory(string $directory): bool
{
    if (!is_dir($directory)) {
        return true;
    }
    if (!rm_full_dir($directory)) {
        echo 'Could not remove retired add-on directory: ' . htmlspecialchars($directory, ENT_QUOTES, 'UTF-8') . '<br />';
        return false;
    }
    return true;
}

function db_add_field($field, $table, $desc)
{
    global $database;
    global $OK;
    global $FAIL;
    $table = TABLE_PREFIX . $table;
    $query = $database->query("DESCRIBE $table '$field'");
    if ($query->numRows() == 0) {
        // add field
        $query = $database->query("ALTER TABLE $table ADD $field $desc");
        echo($database->is_error() ? $database->get_error() . '<br />' : '');
        $query = $database->query("DESCRIBE $table '$field'");
        echo($database->is_error() ? $database->get_error() . '<br />' : '');
        if ($query->numRows() > 0) {
            echo "'$field' added. $OK.<br />";
        } else {
            echo "adding '$field' $FAIL!<br />";
        }
    }
}

/**
 * Core add-on upgrades can contain expensive table rewrites.  They must run
 * when the add-on was actually updated, but never again for an unchanged
 * add-on during a routine CMS update.
 */
function wbce_module_upgrade_required(string $directory): bool
{
    global $database;
    $infoFile = WB_PATH . '/modules/' . $directory . '/info.php';
    $source = is_readable($infoFile) ? (string)file_get_contents($infoFile) : '';
    if (!preg_match("/\\\$module_version\\s*=\\s*['\"]([^'\"]+)['\"]/", $source, $match)) return true;
    $installed = (string)$database->fetchValue(
        "SELECT `version` FROM `{TP}addons` WHERE `type`='module' AND `directory`=? LIMIT 1",
        [$directory]
    );
    return $installed === '' || version_compare($installed, $match[1], '<');
}

function wbce_run_module_upgrade(string $directory): void
{
    // upgrade.php is included in this function's scope. Make the legacy
    // variables expected by add-on upgrades available there as well.
    global $database, $OK, $FAIL, $SIGNAL;
    $upgradeFile = WB_PATH . '/modules/' . $directory . '/upgrade.php';
    if (!is_file($upgradeFile)) return;
    if (!wbce_module_upgrade_required($directory)) {
        echo '<br />' . htmlspecialchars($directory, ENT_QUOTES, 'UTF-8') . ': Datenbankschema bereits aktuell<br />';
        return;
    }
    require_once $upgradeFile;
}

// analyze/check database tables
function mysqlCheckTables($dbName)
{
    global $database, $table_list;
    $table_prefix = TABLE_PREFIX;
    $sql = "SHOW TABLES FROM " . $dbName;
    $result = $database->query($sql);
    $data = array();
    $x = 0;

    while (($row = $result->fetchRow(MYSQLI_NUM)) == true) {
        $tmp = str_replace($table_prefix, '', $row[0]);

        if (stristr($row[0], $table_prefix) && in_array($tmp, $table_list)) {
            $sql = "CHECK TABLE " . $dbName . '.' . $row[0];
            $analyze = $database->query($sql);
            $rowFetch = $analyze->fetchRow(MYSQLI_ASSOC);
            $data[$x]['Op'] = $rowFetch["Op"];
            $data[$x]['Msg_type'] = $rowFetch["Msg_type"];
            $msgColor = '<span class="error">';
            $data[$x]['Table'] = $row[0];
            $msgColor = ($rowFetch["Msg_text"] == 'OK') ? '<span class="ok">' : '<span class="error">';
            $data[$x]['Msg_text'] = $msgColor . $rowFetch["Msg_text"] . '</span>';
            $x++;
        }
    }
    return $data;
}

// check existings tables for update or install
function check_wb_tables()
{
    global $database, $table_list;

    // if prefix inludes '_' or '%'
    $search_for = addcslashes(TABLE_PREFIX, '%_');
    $get_result = $database->query('SHOW TABLES LIKE "' . $search_for . '%"');

    // $get_result = $database->query( "SHOW TABLES FROM ".DB_NAME);
    $all_tables = array();
    if ($get_result->numRows() > 0) {
        while ($data = $get_result->fetchRow()) {
            $tmp = substr($data[0], strlen(TABLE_PREFIX));
            if (in_array($tmp, $table_list)) {
                $all_tables[] = $tmp;
            }
        }
    }
    return $all_tables;
}

/**
 * display a status message on the screen
 * @param string $message: the message to show
 * @param string $class:   kind of message as a css-class
 * @param string $element: witch HTML-tag use to cover the message
 * @return void
 */
function status_msg($message, $class = 'grey', $element = 'span')
{
    // returns a status message
    $msg = '<' . $element . ' class="' . $class . '">';
    $msg .= '<strong>' . strtoupper(strtok($class, ' ')) . '</strong>: ';
    $msg .= $message . '</' . $element . '>';
    echo $msg;
}

/*****************************
 * Functions Section END
 *****************************/

// include required scripts and setup admin object
define("WB_SECFORM_TIMEOUT", 7200); // versions bevore 2.8.2 do not have this value set so its needed

@require_once '../config.php';

// Resume the administrator session saved by the updater before the CMS files
// were replaced. This keeps the final dashboard redirect authenticated even
// if session bootstrapping changed in the delivered release.
$updaterSessionToken = isset($_POST['updater_session_recovery']) && is_string($_POST['updater_session_recovery'])
    ? $_POST['updater_session_recovery'] : '';
$updaterRestoredSessionId = '';
$updaterRestoredSessionName = '';
$updaterSessionFile = '';
// Set the recovery marker before this script renders its long update report.
// A later Set-Cookie would be ignored after the first output chunk.
if (preg_match('/^[a-f0-9]{64}$/', $updaterSessionToken)) {
    @setcookie('WBCE-updater-recovery', $updaterSessionToken, array(
        'expires' => time() + 900,
        'path' => '/',
        'secure' => (defined('DOMAIN_PROTOCOLL') ? DOMAIN_PROTOCOLL : 'https') === 'https',
        'httponly' => true,
        'samesite' => 'Lax',
    ));
    $updaterSessionFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wbce-updater-session-' . $updaterSessionToken . '.json';
    $updaterSessionRaw = @file_get_contents($updaterSessionFile);
    $updaterSessionSnapshot = is_string($updaterSessionRaw) ? json_decode($updaterSessionRaw, true) : null;
    if (is_array($updaterSessionSnapshot) && is_array($updaterSessionSnapshot['session'] ?? null)) {
        $savedSessionId = (string)($updaterSessionSnapshot['session_id'] ?? '');
        $savedSessionName = (string)($updaterSessionSnapshot['session_name'] ?? '');
        // config.php may not have opened a session yet. Always resume the
        // original administrator session before the update code initializes
        // the new framework, otherwise its authentication data is lost.
        if ($savedSessionId !== '') {
            if (session_status() === PHP_SESSION_ACTIVE && session_id() !== $savedSessionId) {
                session_write_close();
            }
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_id($savedSessionId);
                @session_start();
            }
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            foreach ($updaterSessionSnapshot['session'] as $key => $value) {
                if (is_string($key)) $_SESSION[$key] = $value;
            }
            $updaterRestoredSessionId = session_id();
            $updaterRestoredSessionName = session_name();
        }
    }
}

// The updater preserves the release notes of the verified package in a
// temporary, non-public file.  Keep them for the final screen only.
$updateChangeLog = '';
$updateChangeLogFile = WB_PATH . '/temp/.wbce-updater-release-notes.txt';
if (is_readable($updateChangeLogFile)) {
    $updateChangeLogContents = @file_get_contents($updateChangeLogFile);
    if (is_string($updateChangeLogContents) && trim($updateChangeLogContents) !== '') {
        $updateChangeLog = substr($updateChangeLogContents, 0, 262144);
    }
}

require_once WB_PATH . '/framework/functions.php';
require_once WB_PATH . '/framework/Database.php';
if (!isset($database) || !($database instanceof Database)) {
    $database = new Database();
}
require_once WB_PATH . '/framework/Admin.php';
$admin = new admin('Addons', 'modules', false, false);

// database tables included in package
$table_list = array('addons', 'blocking', 'groups', 'pages', 'search', 'sections', 'settings', 'users', 'mod_droplets', 'mod_menu_link', 'mod_miniform', 'mod_news_img_posts', 'mod_outputfilter_dashboard', 'mod_sitemap', 'mod_wbstats_day');

$OK = ' <span class="good">OK</span> ';
$FAIL = ' <span class="error">FAILED</span> ';
$sConfiguredTheme = trim((string) Settings::Get('default_theme'));
$bValidThemeName = $sConfiguredTheme !== ''
    && preg_match('/^[a-zA-Z0-9_-]+$/', $sConfiguredTheme) === 1;
$sConfiguredThemeHeader = WB_PATH . '/templates/' . $sConfiguredTheme . '/templates/header.htt';

// WBCE CMS replaces the former bundled default. Preserve explicitly selected
// custom themes, but migrate installations still using the former default.
$bUsesFormerDefaultTheme = $sConfiguredTheme === 'wbce_flat_theme';
$DEFAULT_THEME = ($bValidThemeName && is_file($sConfiguredThemeHeader) && !$bUsesFormerDefaultTheme)
    ? $sConfiguredTheme
    : 'wbce-cms';
$stepID = 1;

// removes old folders
$dirRemove = array(
    '[ROOT]/log/',
    '[ACCOUNT]/email_templates/',
    '[ACCOUNT]/functions/',
    '[ACCOUNT]/languages/',
    '[ACCOUNT]/templates/',
    '[ADMIN]/images/',
    '[ADMIN]/pages/page_tree/icons/',
    '[ADMIN]/themes/',
    '[INCLUDE]/Doctrine/',
    '[INCLUDE]/phpmailer/',
    '[INCLUDE]/Sensio/Twig/lib/',
    '[MODULES]/ckeditor/ckeditor/filemanager/',
    '[MODULES]/ckeditor/ckeditor/plugins/wsc/',
    '[MODULES]/ckeditor/ckeditor/plugins/flash/',
    '[MODULES]/ckeditor/ckeditor/plugins/oembed/',
    '[MODULES]/ckeditordev/ckeditor/filemanager/',
    '[MODULES]/ckeditordev/ckeditor/plugins/wsc/',
    '[MODULES]/ckeditordev/ckeditor/plugins/flash/',
    '[MODULES]/ckeditordev/ckeditor/plugins/oembed/',
    '[MODULES]/ckeditor_dev/',
    '[MODULES]/el_finder/',
    '[MODULES]/output_filter/',    
    '[MODULES]/user_search/',
    '[TEMPLATE]/advancedThemeWbFlat/',
    '[TEMPLATE]/argos_theme/',
    '[TEMPLATE]/argos_theme_classic/',
    '[TEMPLATE]/argos_theme_reloaded/images/flags/',
    '[TEMPLATE]/beesign_theme_ce/',
    '[TEMPLATE]/DefaultTheme/',
    '[TEMPLATE]/wb_theme/',
    '[TEMPLATE]/wbce_flat_theme/jquery/jqueryNiceFileInput/'
);

// files removed
$filesRemove['0'] = array(
    '[ROOT]/config.php.new',
    '[ROOT]/htaccess.txt',
    '[ACCOUNT]/Accounts.cfg.php',
    '[ACCOUNT]/check_details.php',
    '[ACCOUNT]/check_email.php',
    '[ACCOUNT]/check_password.php',
    '[ACCOUNT]/details.php',
    '[ACCOUNT]/email.php',
    '[ACCOUNT]/forgot_form.php',
    '[ACCOUNT]/frontend.css',
    '[ACCOUNT]/login_form.php',
    '[ACCOUNT]/password.php',
    '[ACCOUNT]/preferences_form.php',
    '[ACCOUNT]/signup_form.inc.php',
    '[ACCOUNT]/signup_form.php',
    '[ACCOUNT]/signup_switch.php',
    '[ACCOUNT]/signup2.php',
    '[ACCOUNT]/template.html',
    '[ACCOUNT]/template.php',
    '[ADMIN]/media/browse.php',
    '[ADMIN]/media/create.php',
    '[ADMIN]/media/delete.php',
    '[ADMIN]/media/dse.php',
    '[ADMIN]/media/MediaBlackList',
    '[ADMIN]/media/MediaWhiteList',
    '[ADMIN]/media/nopreview.jpg',
    '[ADMIN]/media/overlib.js',
    '[ADMIN]/media/parameters.php',
    '[ADMIN]/media/rename.php',
    '[ADMIN]/media/rename2.php',
    '[ADMIN]/media/resize_img.php',
    '[ADMIN]/media/setparameter.php',
    '[ADMIN]/media/thumb.php',
    '[ADMIN]/media/upload.php',
    '[ADMIN]/preferences/details.php',
    '[ADMIN]/preferences/email.php',
    '[ADMIN]/preferences/password.php',
    '[FRAMEWORK]/class.database.doctrine.php',
    '[FRAMEWORK]/class.wbmailer.php',
    '[FRAMEWORK]/PasswordHash.php',
    '[FRAMEWORK]/SecureForm.mtab.php',
    '[INCLUDE]Sensio/Twig/TwigConnect.php',
    '[INCLUDE]/jquery/GPL-LICENSE.txt',
    '[INCLUDE]/jquery/images/ui-anim_basic_16x16.gif',
    '[INCLUDE]/jquery/images/ui-bg_flat_0_aaaaaa_40x100.png',
    '[INCLUDE]/jquery/images/ui-bg_flat_75_ffffff_40x100.png',
    '[INCLUDE]/jquery/images/ui-bg_glass_55_fbf9ee_1x400.png',
    '[INCLUDE]/jquery/images/ui-bg_glass_65_ffffff_1x400.png',
    '[INCLUDE]/jquery/images/ui-bg_glass_75_dadada_1x400.png',
    '[INCLUDE]/jquery/images/ui-bg_glass_75_e6e6e6_1x400.png',
    '[INCLUDE]/jquery/images/ui-bg_glass_95_fef1ec_1x400.png',
    '[INCLUDE]/jquery/images/ui-bg_highlight-soft_75_cccccc_1x100.png',
    '[INCLUDE]/jquery/images/ui-icons_222222_256x240.png',
    '[INCLUDE]/jquery/images/ui-icons_2e83ff_256x240.png',
    '[INCLUDE]/jquery/images/ui-icons_454545_256x240.png',
    '[INCLUDE]/jquery/images/ui-icons_888888_256x240.png',
    '[INCLUDE]/jquery/images/ui-icons_cd0a0a_256x240.png',
    '[INCLUDE]/jquery/jquery-min.legacy.js',
    '[INCLUDE]/jquery/jquery-min132.js',
    '[INCLUDE]/jquery/jquery-min161.js',
    '[INCLUDE]/jquery/jquery-min164.js',
    '[INCLUDE]/jquery/jquery-min170.js',
    '[INCLUDE]/jquery/jquery-pngFix.js',
    '[INCLUDE]/jquery/jquery-ui.css',
    '[INCLUDE]/jquery/MIT-LICENSE.txt',
    '[INCLUDE]/jquery/version.txt',
    '[LANGUAGES]/SE.php',
    '[MODULES]/captcha_control/FTAN_SUPPORTED',
    '[MODULES]/captcha_control/install_struct.sql',
    '[MODULES]/SecureFormSwitcher/files/SecureForm.mtab.php',
    '[MODULES]/SecureFormSwitcher/FTAN_SUPPORTED',
    '[MODULES]/SecureFormSwitcher/htt/help.png',
    '[MODULES]/SecureFormSwitcher/htt/switchform.htt',
    '[MODULES]/SecureFormSwitcher/language_load.php',
    '[TEMPLATE]/wbce_flat_theme/css/themeextended.css',
    '[TEMPLATE]/wbce_flat_theme/images/preloader_blue.gif',
    '[TEMPLATE]/wbce_flat_theme/images/preloader_red.gif',
    '[TEMPLATE]/wbce_flat_theme/images/wblogo.png',
    '[TEMPLATE]/wbce_flat_theme/images/wblogo_large.png'
);

// check existing tables
$all_tables = check_wb_tables();
// print_r( $all_tables);
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>WBCE CMS Update Script</title>
<link href="normalize.css" rel="stylesheet" type="text/css"/>
<link href="stylesheet.css" rel="stylesheet" type="text/css"/>
<style>
/* Variables are shared by the core admin templates.  Fallbacks keep this
   standalone maintenance page usable while files are being replaced. */
:root{--update-accent:var(--wbce-accent,var(--theme-primary,#2271b1));--update-surface:var(--wbce-card-bg,var(--content-bg,#fff));--update-text:var(--wbce-text-color,var(--text-color,#1d2327));--update-border:var(--wbce-border-color,var(--border-color,#c8d1da))}
body{background:var(--wbce-admin-page-bg,#eef2f5)!important;color:var(--update-text)}
div.body{width:min(68rem,calc(100% - 2rem));box-sizing:border-box;margin:2rem auto;padding:1.5rem;background:var(--update-surface);border:1px solid var(--update-border);border-radius:var(--wbce-card-radius,10px);box-shadow:var(--wbce-card-shadow,0 8px 28px rgba(0,0,0,.14))}
table,table>thead{background:var(--update-surface)} table{border-color:var(--update-border)} .header tr{background:color-mix(in srgb,var(--update-accent) 13%,var(--update-surface))}.header h1{color:var(--update-text)}
.update-changes{margin:20px 0;border:1px solid var(--update-border);border-radius:var(--wbce-card-radius,6px);background:#fff;text-align:left}.update-changes summary{padding:13px 16px;cursor:pointer;font-weight:bold}.update-changes pre{margin:0;overflow:auto;padding:16px;border-top:1px solid var(--update-border);background:#fff;color:var(--update-text);font:13px/1.55 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;white-space:pre-wrap;overflow-wrap:anywhere}
</style>
</head>
<body>
<div class="body">
<?php echo str_repeat(' ', 4096); @ob_flush(); @flush(); ?>
    <table class="header">
        <tbody>
            <tr>
                <td><img class="logo" src="logo.png" alt="WBCE CMS Logo"/>&nbsp;<h1>Welcome to the Update Script</h1></td>
            </tr>
        </tbody>
    </table>
    <table class="step1">
        <thead>
            <tr>
                <th class="step-row"> <?php echo '<h2 class="step-row">Step ' . ($stepID++) . '</h2> &nbsp;Backup your files !!'; ?> </th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php
// extract previous WBCE version from DB (if exists)
$old_wbce_version = array(
    'VERSION' => db_get_key_value('settings', 'wbce_version'),
    'TAG' => db_get_key_value('settings', 'wbce_tag'),
);

// extract previous WB-classic version from DB or admin/interface/info.php
$old_wb_version = array(
    'VERSION' => defined('WB_VERSION') ? WB_VERSION : VERSION,
    'REV' => defined('WB_REVISION') ? WB_REVISION : REVISION,
    'SP' => defined('WB_SP') ? WB_SP : SP,
);

// check if we update from WBCE or WB-classic
if (!is_null($old_wbce_version['VERSION'])) {

    // we update from an older WBCE version
    $oldVersion = 'WBCE v' . $old_wbce_version['VERSION'] . ' (' . $old_wbce_version['TAG'] . ')';
} else {

    // we update from WB-classic, make sure that WB-classic is 2.7 or higher
    if (version_compare($old_wb_version['VERSION'], '2.7', '<')) {
        status_msg('<br />WebsiteBaker version below 2.7 can´t be updated to WBCE.<br />Please update your WB version to 2.7 before upgrading to WBCE in a second step!', 'warning', 'div');
        echo '</td></tr></tbody></table></div></body></html>';
        exit();
    }
    $oldVersion = 'WB-Classic v' . $old_wb_version['VERSION'] . ' (REV: ' . $old_wb_version['REV'] . ', SP: ' . $old_wb_version['SP'] . ')';
}

// Current 1.7 installations already use the normalized user schema.
// Legacy data migrations are expensive on large user tables and only belong
// to an initial upgrade from WB-Classic or a pre-1.7 WBCE release.
$oldCmsVersion = (string)($old_wbce_version['VERSION'] ?? '');
$needsLegacyUserMigration = $oldCmsVersion === '' || preg_match('/^1\.7\./', $oldCmsVersion) !== 1;

// string for new version
$newVersion = 'WBCE v' . NEW_WBCE_VERSION . ' (' . NEW_WBCE_TAG . ')';

// set addition settings if not exists, otherwise update will be breaks
if (!defined('WB_SP')) {
    define('WB_SP', '');
}
if (!defined('WB_REVISION')) {
    define('WB_REVISION', '');
}

?>
<p>This script updates <strong> <?php echo $oldVersion; ?></strong> to
<strong> <?php echo $newVersion ?> </strong>.</p>
<?php

// Check if disclaimer was accepted
if (!(isset($_POST['backup_confirmed']) && $_POST['backup_confirmed'] == 'confirmed')) {
?>

<br/><p>The update script modifies the existing database to reflect the changes introduced with the new version.</p>
<p>It is highly recommended to <strong>create a manual backup</strong> of the entire <strong>/pages folder</strong> and the <strong>database</strong> before proceeding.</p><br/>

<form name="send" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
    <pre>DISCLAIMER: The WBCE update script is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. One needs to confirm that a manual backup of the /pages folder (including all files and subfolders contained in it) and backup of the entire WBCE database was created before you can proceed.</pre>
    <br/>
    <input name="backup_confirmed" type="checkbox" value="confirmed"/>
    &nbsp;I confirm that a manual backup of the /pages folder and the database was created.
    <br/>

    <div class="warning">
        <p>You need to confirm that you have created a manual backup of the /pages directory and the database before you can proceed.</p>
    </div>
    <br/>
    <p class="center">
        <input name="send" type="submit" value="Start update script"/>
    </p>
</form>
<br/>
<?php
echo '</td></tr></tbody></table></div></body></html>';
exit();
}
wbce_do_action('system.update.started', array(
    'from_version' => $old_wbce_version['VERSION'],
    'to_version' => NEW_WBCE_VERSION,
));
                ?></td>
            </tr>
        </tbody>
    </table>
    <table class="step2">
        <thead>
            <tr>
                <th class="step-row"> <?php echo '<h2 class="step-row">Step ' . ($stepID++) . '</h2> &nbsp;Updating database entries and modules'; ?> </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php

/**********************************************************
 * First of all Create Database Tables required for the current version
 */

echo "Ensure the needed database tables are there<br />";
$sFileName = 'install_struct.sql';
$sFile = dirname(__FILE__) . '/' . $sFileName;
if (is_readable($sFile)) {
    if (!$database->SqlImport($sFile, TABLE_PREFIX)) {
        echo(__LINE__ . ": Unable to read import 'install/" . $sFileName . "'" . $database->get_error() . '<br />');
    }
} else {
    if (file_exists($sFile)) {
        echo(__LINE__ . ": Unable to read file 'install/" . $sFileName . "'<br />");
    } else {
        echo(__LINE__ . ": File 'install/" . $sFileName . "' doesn't exist!<br />");
    }
}

/**********************************************************
 * Adding field default_theme to settings table
 */

if ($sConfiguredTheme !== $DEFAULT_THEME) {
    echo "<br />Configured backend theme is unavailable; use " . htmlspecialchars($DEFAULT_THEME, ENT_QUOTES, 'UTF-8') . "<br />";
    Settings::Set('default_theme', $DEFAULT_THEME);
} else {
    echo "<br />Keep configured backend theme: " . htmlspecialchars($DEFAULT_THEME, ENT_QUOTES, 'UTF-8') . "<br />";
}
if (defined("WB_SECFORM_TIMEOUT")) {
    Settings::Set('wb_secform_timeout', '7200');
}
if (defined("WB_SESSION_TIMEOUT")) {
    Settings::Set('wb_session_timeout', Settings::GetDb('wb_secform_timeout'));
}

/**********************************************************
 * Checking Core modules for installation status and install if necessary
 */

// set config of CodeMirror_Config
if (!Settings::exists('cmc_cfg')) {
    if (is_readable($sFile = WB_PATH . '/modules/CodeMirror_Config/install.php')) {
        require_once $sFile;
    }
}


// Captcha Controll
$file_name = WB_PATH . "/modules/captcha_control/info.php";
if (file_exists($file_name)) {
    echo "<br />Update Captcha Controll<br />";
    wbce_run_module_upgrade('captcha_control');
}

// Errorloger
$file_name = WB_PATH . "/modules/errorlogger/info.php";
if (file_exists($file_name)) {
    echo "<br />Install Errorloger<br />";
    require_once WB_PATH . "/modules/errorlogger/install.php";
}

// Droplets
$file_name = WB_PATH . "/modules/droplets/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_droplets", $all_tables)) {
        echo "<br />Install Droplets<br />";
        require_once WB_PATH . "/modules/droplets/install.php";
    } else {
        echo "<br />Update Droplets<br />";
        wbce_run_module_upgrade('droplets');
    }
}

// Menu Link
$file_name = WB_PATH . "/modules/menu_link/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_menu_link", $all_tables)) {
        echo "<br />Install Menu Link<br />";
        require_once WB_PATH . "/modules/menu_link/install.php";
    } else {
        echo "<br />Update Menu Link<br />";
        wbce_run_module_upgrade('menu_link');
    }
}

// Miniform
$file_name = WB_PATH . "/modules/miniform/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_miniform", $all_tables)) {
        echo "<br />Install MiniForm<br />";
        require_once WB_PATH . "/modules/miniform/install.php";
    } else {
        echo "<br />Update MiniForm<br />";
        wbce_run_module_upgrade('miniform');
    }
}

// News with Images
$file_name = WB_PATH . "/modules/news_img/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_news_img_posts", $all_tables)) {
        echo "<br />Install News with Images<br />";
        require_once WB_PATH . "/modules/news_img/install.php";
    } else {
        echo "<br />Update News with Images<br />";
        wbce_run_module_upgrade('news_img');
    }
}

// OpF Dashboard
$file_name = WB_PATH . "/modules/outputfilter_dashboard/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_outputfilter_dashboard", $all_tables)) {
        echo "<br />Install OpF Dashboard<br />";
        require_once WB_PATH . "/modules/outputfilter_dashboard/install.php";
        Settings::Set('opf_show_advanced_backend', 0, false);
    } else {
        echo "<br />Update OpF Dashboard<br />";
        wbce_run_module_upgrade('outputfilter_dashboard');
        Settings::Set('opf_show_advanced_backend', 1, false);
    }
    // uninstall classical output filter module
    $classic_opf = WB_PATH . "/modules/output_filter/uninstall.php";
    if (file_exists($classic_opf)) {
        echo "<br />Uninstall classical output_filter module<br />";
        include_once($classic_opf);
        wbce_remove_retired_addon_directory(WB_PATH . "/modules/output_filter");
    }
    // Remove entry from DB
    $database->query("DELETE FROM `" . TABLE_PREFIX . "addons` WHERE `directory` = 'output_filter' AND `type` = 'module'");
    // uninstall opf simple backend
    $opf_simple = WB_PATH . "/modules/opf_simple_backend/uninstall.php";
    if (file_exists($opf_simple)) {
        echo "<br />Uninstall Opf Simple Backend  module<br />";
        include_once($opf_simple);
        wbce_remove_retired_addon_directory(WB_PATH . "/modules/opf_simple_backend");
    }
    // Remove entry from DB
    $database->query("DELETE FROM `" . TABLE_PREFIX . "addons` WHERE `directory` = 'opf_simple_backend' AND `type` = 'module'");
}

// Sitemap
$file_name = WB_PATH . "/modules/sitemap/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_sitemap", $all_tables)) {
        echo "<br />Install Sitemap<br />";
        require_once WB_PATH . "/modules/sitemap/install.php";
    } else {
        echo "<br />Update Sitemap<br />";
        wbce_run_module_upgrade('sitemap');
    }
}

// Visitor statistics
$file_name = WB_PATH . "/modules/wbstats/info.php";
if (file_exists($file_name)) {
    if (!in_array("mod_wbstats_day", $all_tables)) {
        echo "<br />Install Visitor statistics<br />";
        require_once WB_PATH . "/modules/wbstats/install.php";
    } else {
        echo "<br />Update Visitor statistics<br />";
        wbce_run_module_upgrade('wbstats');
    }
}

// check again all tables, to get a new array
if (sizeof($all_tables) < sizeof($table_list)) {
    $all_tables = check_wb_tables();
}

/**********************************************************
 * check tables coming with WBCE
 */

$check_text = 'total ';
// $check_tables = mysqlCheckTables( DB_NAME ) ;

if (sizeof($all_tables) !== sizeof($table_list)) {
    status_msg('<br />Can\'t run Update, missing tables', 'warning', 'div');
    echo '<h4>Missing required tables. You can install them in backend->addons->modules->advanced. Then again run update.php</h4>';
    $result = array_diff($table_list, $all_tables);
    echo '<h4 class="warning"><br />';
    foreach ($result as $val) {
        echo TABLE_PREFIX . $val . ' ' . $FAIL . '<br>';
    }
    echo '<br /></h4>';
    echo '<br /><form action="' . $_SERVER['PHP_SELF'] . '">';
    echo '<input type="submit" value="kick me back" />';
    echo '</form>';
    if (defined('ADMIN_URL')) {
        echo '<form action="' . ADMIN_URL . '" target="_self">';
        echo '&nbsp;<input type="submit" value="kick me to the Backend" />';
        echo '</form>';
    }
    echo '</td></tr></tbody></table></div></body></html>';
    exit();
}

/**********************************************************
 * Adding or removing Settings
 */

echo "<br />Update errorlevel<br />";
Settings::Set('er_level', 'E3');

echo "<br />Update rename_files_on_upload<br />";
Settings::Set('rename_files_on_upload', 'ph.*?,cgi,pl,pm,exe,com,bat,pif,cmd,src,asp,aspx,js,lnk,inc,tpl', false);

echo "<br />Add Settings for PHPmailer<br />";
Settings::Set('wbmailer_smtp_secure', '', false);
Settings::Set('wbmailer_smtp_port', '', false);

echo "<br />Add sec_anchor<br />";
Settings::Set('sec_anchor', 'wb_', false);

echo "<br />Add redirect timer<br />";
Settings::Set('redirect_timer', '1500', false);

echo "<br />Add mediasettings<br />";
Settings::Set('mediasettings', '', false);

echo "<br />Add Secureform Settings<br />";
// Settings::Set ("wb_maintainance_mode", false, false);
Settings::Set("wb_secform_secret", "5609bnefg93jmgi99igjefg", false);
Settings::Set("wb_secform_secrettime", '86400', false);
Settings::Set("wb_secform_timeout", '7200', false);
Settings::Set("wb_secform_tokenname", 'formtoken', false);
Settings::Set("wb_secform_usefp", false, false);
Settings::Set('fingerprint_with_ip_octets', '0', false);
$legacyTimezone = defined('DEFAULT_TIMEZONE') ? (int)DEFAULT_TIMEZONE : 0;
$timezoneIdentifier = ($legacyTimezone === 3600 && defined('DEFAULT_LANGUAGE') && DEFAULT_LANGUAGE === 'DE')
    ? 'Europe/Berlin'
    : sprintf('%s%02d:%02d', $legacyTimezone < 0 ? '-' : '+', intdiv(abs($legacyTimezone), 3600), intdiv(abs($legacyTimezone) % 3600, 60));
Settings::Set('default_timezone_identifier', $timezoneIdentifier, false);
wbce_update_progress('Zeitzonenfeld wird geprüft …');
if (wbce_column_needs_change(TABLE_PREFIX.'users','timezone','varchar(64)')) $database->query("ALTER TABLE `{TP}users` MODIFY `timezone` varchar(64) NOT NULL DEFAULT ''");

echo "<br />Remove Secureform selector<br />";
Settings::Del('secure_form_module'); // No longer needed as Singletab is removed

/**********************************************************
 * Adding DB Fields
 */

// Add field "redirect_type" to table "mod_menu_link"
echo "<br />Add field redirect_type to mod_menu_link table<br />";
db_add_field('redirect_type', 'mod_menu_link', "INT NOT NULL DEFAULT '302' AFTER `target_page_id`");

// Add field "namesection" to table "sections"
echo "<br />Add field namesection to sections table<br />";
db_add_field('namesection', 'sections', "VARCHAR( 255 ) NULL");
db_add_field('active', 'addons', "TINYINT(1) NOT NULL DEFAULT '1' AFTER `license`");

/**********************************************************
 * Update users tables
 */

$table = TABLE_PREFIX . "users";

// Schema was normalized above; avoid a second full table rebuild on every update.
wbce_update_progress('Benutzerkonten werden geprüft …');

if ($needsLegacyUserMigration) {
    wbce_update_progress('Historische Benutzerkonten werden migriert …');
    // Normalize legacy numeric timezone values only during the initial migration.
    $sql = "UPDATE $table SET `timezone` = '' WHERE `timezone` REGEXP '^-?[0-9]+$'";
    $database->query($sql);
    echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');

    // Set group_id to the first legacy groups_id value and retain administrator group 1.
    $sql = "UPDATE $table SET `group_id` = CAST(groups_id AS SIGNED) WHERE `group_id` IS NULL OR `group_id` <> CAST(groups_id AS SIGNED)";
    $database->query($sql);
    echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');
    $sql = "UPDATE $table SET `group_id` = 1 WHERE FIND_IN_SET('1', groups_id) > '0'";
    $database->query($sql);
    echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');
} else {
    echo 'Benutzerkonten sind bereits im aktuellen Schema. ' . $OK . '<br />';
}

// Alter Table so it can store ipv6
wbce_update_progress('Anmeldeprotokoll wird geprüft …');
if (wbce_column_needs_change($table,'login_ip','varchar(50)')) $database->query("ALTER TABLE $table CHANGE `login_ip` `login_ip` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';");
echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');

// insert new fields
$database->field_add($table, "signup_checksum", "varchar(64) collate utf8_unicode_ci NOT NULL DEFAULT ''");
echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');

$database->field_add($table, "signup_timestamp", "int(11) NOT NULL DEFAULT '0'");
echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');

$database->field_add($table, "signup_timeout", "int(11) NOT NULL DEFAULT '0'");
echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');

$database->field_add($table, "signup_confirmcode", "varchar(64) collate utf8_unicode_ci NOT NULL DEFAULT ''");
echo($database->is_error() ? __LINE__ . ': ' . $database->get_error() . '<br />' : '');

/**********************************************************
 * Update search no results database filed to create
 * valid XHTML if search is empty
 */

if (version_compare(WB_VERSION, '2.8', '<')) {
    echo "<br />Updating database field `no_results` of search table: ";
    $search_no_results = addslashes('<tr><td><p>[TEXT_NO_RESULTS]</p></td></tr>');
    $sql = 'UPDATE `' . TABLE_PREFIX . 'search` ';
    $sql .= 'SET `value`=\'' . $search_no_results . '\' ';
    $sql .= 'WHERE `name`=\'no_results\'';
    echo ($database->query($sql)) ? ' OK<br />' : ' FAIL<br />';
}

echo "<br />Updating database field `cfg_enable_old_search` of search table: ";    
    $sql = 'UPDATE `' . TABLE_PREFIX . 'search` ';
    $sql .= 'SET `value`=\'false\' ';
    $sql .= 'WHERE `name`=\'cfg_enable_old_search\'';
    echo ($database->query($sql)) ? ' OK<br />' : ' FAIL<br />';

/**********************************************************
 * update media folder index protect files
 */

$dir = (WB_PATH . MEDIA_DIRECTORY);
echo '<br />Update ' . MEDIA_DIRECTORY . '/ index.php protect files<br />';
$array = rebuildFolderProtectFile($dir);
if (sizeof($array)) {
    print 'Update ' . MEDIA_DIRECTORY . '/ ' . sizeof($array) . ' protect files.' . " $OK .<br /><br />";
} else {
    print 'Update ' . MEDIA_DIRECTORY . '/ protect files' . " $FAIL!<br /><br />";
    print implode('<br />', $array);
}

/**********************************************************
 * update posts folder index protect files
 */

$sPostsPath = WB_PATH . PAGES_DIRECTORY . '/posts';
echo 'Update /posts/ index.php protect files<br />';
$array = rebuildFolderProtectFile($sPostsPath);
if (sizeof($array)) {
    print 'Update /posts/ ' . sizeof($array) . ' protect files.' . " $OK .";
} else {
    print 'Update /posts/ protect files' . " $FAIL!";
    print implode('<br /><br />', $array);
}

                ?></td>
            </tr>
        </tbody>
    </table>
    <table class="step3">
        <thead>
            <tr>
                <th class="step-row"> <?php echo '<h2 class="step-row">Step ' . ($stepID++) . '</h2> &nbsp;Remove deprecated and old files and folders'; ?> </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php

/**********************************************************
 * check for deprecated or never needed files
 */

if (sizeof($filesRemove)) {
    echo 'Removing files<br />';
}

$searches = array(
    '[ROOT]',
    '[ACCOUNT]',
    '[ADMIN]',
    '[FRAMEWORK]',
    '[INCLUDE]',
    '[LANGUAGES]',
    '[MEDIA]',
    '[MODULES]',
    '[PAGES]',
    '[TEMPLATE]'
);

$replacements = array(
    '',
    '/account',
    substr(ADMIN_PATH, strlen(WB_PATH) + 1),
    '/framework',
    '/include',
    '/languages',
    MEDIA_DIRECTORY,
    '/modules',
    PAGES_DIRECTORY,
    '/templates'
);

foreach ($filesRemove as $filesId) {
    $msg = '';
    foreach ($filesId as $file) {
        $file = str_replace($searches, $replacements, $file);
        $file = WB_PATH . '/' . $file;
        if (file_exists($file)) {
            // try to unlink file
            if (!is_writable($file) || !unlink($file)) {
                // save in err-list, if failed
                $msg .= $file . '<br />';
            }
        }
    }

    if ($msg != '') {
        $msg = '<br /><br />Following files are deprecated, outdated or a security risk and can not be removed automatically.<br /><br />Please delete them using FTP and restart update script!<br /><br />' . $msg . '<br />';
        status_msg($msg, 'error warning', 'div');
        echo '<p class="error"><strong>WARNING: The update script failed ...</strong></p>';

        echo '<form action="' . $_SERVER['SCRIPT_NAME'] . '">';
        echo '&nbsp;<input name="send" type="submit" value="Restart update script" />';
        echo '</form>';
        echo '</td></tr></tbody></table></div></body></html>';
        exit;
    }
}

/**********************************************************
 * check for deprecated or never needed directories
 */

if (sizeof($dirRemove)) {
    echo '<br />Removing folders';

    $searches = array(
        '[ROOT]',
        '[ACCOUNT]',
        '[ADMIN]',
        '[FRAMEWORK]',
        '[INCLUDE]',
        '[LANGUAGES]',
        '[MEDIA]',
        '[MODULES]',
        '[PAGES]',
        '[TEMPLATE]'
    );

    $replacements = array(
        '',
        '/account',
        substr(ADMIN_PATH, strlen(WB_PATH) + 1),
        '/framework',
        '/include',
        '/languages',
        MEDIA_DIRECTORY,
        '/modules',
        PAGES_DIRECTORY,
        '/templates'
    );

    $msg = '';
    foreach ($dirRemove as $dir) {
        $dir = str_replace($searches, $replacements, $dir);
        $dir = WB_PATH . '/' . $dir;
        if (is_dir($dir)) {
            // try to delete dir
            if (!rm_full_dir($dir)) {
                // save in err-list, if failed
                $msg .= $dir . '<br />';
            }
        }
    }
    if ($msg != '') {
        $msg = '<br /><br />Following files are deprecated, outdated or a security risk and can not be removed automatically.<br /><br />Please delete them using FTP and restart update script!<br /><br />' . $msg . '<br />';
        status_msg($msg, 'error warning', 'div');
        echo '<p class="error"><strong>WARNING: The update script failed ...</strong></p>';
        echo '<form action="' . $_SERVER['SCRIPT_NAME'] . '">';
        echo '&nbsp;<input name="send" type="submit" value="Restart update script" />';
        echo '</form>';
        echo '<</td></tr></tbody></table></div></body></html>';
        exit;
    }
}

                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="step4">
        <thead>
            <tr>
                <th class="step-row"> <?php echo '<h2 class="step-row">Step ' . ($stepID++) . '</h2> &nbsp;Reload all addons database entry (no update)'; ?> </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?php

/**********************************************************
 * Reload all addons
 */

// dev.2 accidentally shipped optional CAPTCHA provider directories inside the
// CMS archive. Remove only copies for which install.php never created provider
// settings. Properly installed providers are marked and remain untouched.
$optionalCaptchaProviders = array(
    'captcha_cap' => array('setting' => 'captcha_cap_endpoint', 'marker' => 'captcha_cap_installed'),
    'captcha_altcha' => array('setting' => 'captcha_altcha_secret', 'marker' => 'captcha_altcha_installed'),
    'captcha_icon' => array('setting' => 'captcha_icon_timeout', 'marker' => 'captcha_icon_installed'),
    'captcha_phpcapcha' => array('setting' => 'captcha_phpcapcha_length', 'marker' => 'captcha_phpcapcha_installed'),
);
foreach ($optionalCaptchaProviders as $providerDirectory => $providerState) {
    $providerPath = WB_PATH . '/modules/' . $providerDirectory;
    if (!is_dir($providerPath)) continue;
    $installedValue = Settings::Get($providerState['setting'], '__wbce_not_installed__');
    if ($installedValue === '__wbce_not_installed__') {
        rm_full_dir($providerPath);
        echo 'Removed accidentally bundled optional module: ' . htmlspecialchars($providerDirectory) . '<br />';
    } else {
        Settings::Set($providerState['marker'], '1');
    }
}

// Truncate addons
$database->query("TRUNCATE `" . TABLE_PREFIX . "addons`");
echo 'Truncate addons table<br />';

// Load all modules
if (($handle = opendir(WB_PATH . '/modules/'))) {
    while (false !== ($file = readdir($handle))) {
        if ($file != '' && substr($file, 0, 1) != '.' && $file != 'admin.php' && $file != 'index.php') {
            load_module(WB_PATH . '/modules/' . $file);
        }
    }
    closedir($handle);
}
echo '<br />Modules reloaded<br />';

// Load all templates
if (($handle = opendir(WB_PATH . '/templates/'))) {
    while (false !== ($file = readdir($handle))) {
        if ($file != '' && substr($file, 0, 1) != '.' && $file != 'index.php') {
            load_template(WB_PATH . '/templates/' . $file);
        }
    }
    closedir($handle);
}
echo '<br />Templates reloaded<br />';

// Load all languages
if (($handle = opendir(WB_PATH . '/languages/'))) {
    while (false !== ($file = readdir($handle))) {
        if ($file != '' && substr($file, 0, 1) != '.' && $file != 'index.php') {
            load_language(WB_PATH . '/languages/' . $file);
        }
    }
    closedir($handle);
}
echo '<br />Languages reloaded<br />';

/**********************************************************
 * Set Version to new Version
 */

// WBCE 1.7: opaque, single-use password reset links.
$passwordResetTable = TABLE_PREFIX . 'password_resets';
$database->query("CREATE TABLE IF NOT EXISTS `$passwordResetTable` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `selector` CHAR(18) NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `expires_at` INT NOT NULL,
    `used_at` INT NOT NULL DEFAULT 0,
    `requested_at` INT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    INDEX `user_requested` (`user_id`, `requested_at`)
)");

echo '<br />Update database version number to ' . NEW_WBCE_VERSION . ' (Tag: ' . NEW_WBCE_TAG . ')';
// The updater reads the installed release from the settings table.  Do not
// report a completed update until this persistent marker was actually written.
$versionSettings = array(
    'wbce_version' => NEW_WBCE_VERSION,
    'wbce_tag' => NEW_WBCE_TAG,
    'wb_version' => VERSION,
    'wb_revision' => REVISION,
    'wb_sp' => SP,
);
$versionWriteFailure = '';
foreach ($versionSettings as $settingName => $settingValue) {
    $writeResult = Settings::Set($settingName, $settingValue);
    if ($writeResult !== false) {
        $versionWriteFailure = 'Die Versionskennung konnte nicht gespeichert werden (' . $settingName . '): ' . $writeResult;
        break;
    }
    $storedValue = db_get_key_value('settings', $settingName);
    if ((string)$storedValue !== (string)$settingValue) {
        $versionWriteFailure = 'Die Versionskennung wurde nicht bestätigt (' . $settingName . '). Erwartet: ' . $settingValue . ', gespeichert: ' . (string)$storedValue;
        break;
    }
}
if ($versionWriteFailure !== '') {
    status_msg($versionWriteFailure, 'error', 'div');
    echo '</td></tr></tbody></table></div></body></html>';
    exit;
}

$updaterUpdateLog = WB_PATH . '/modules/updater/UpdateLog.php';
if (is_file($updaterUpdateLog)) {
    require_once $updaterUpdateLog;
    if (function_exists('updater_update_log')) updater_update_log((string)NEW_WBCE_VERSION, 'Success', 'Datenbank-Update', 'CMS-Datenbank-Update erfolgreich abgeschlossen.');
}

/**********************************************************
 * End of update script only some output stuff down here
 */

if (!defined('DEFAULT_THEME')) {
    define('DEFAULT_THEME', $DEFAULT_THEME);
}
if (!defined('THEME_PATH')) {
    define('THEME_PATH', WB_PATH . '/templates/' . DEFAULT_THEME);
}

                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="step5">
        <thead>
            <tr>
                <th class="step-row"> Congratulations: The update script is finished ...</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
<?php
$file_name = WB_PATH . "/modules/colorbox/info.php";
if (file_exists($file_name)) {
    echo '<p style="margin:10px 0">[EN] <strong>Notice:</strong> Colorbox snippet detected. After loggin in to WBCE CMS, please update the Colorbox snippet to version 1.6.5, <a href="https://addons.wbce.org/pages/addons.php?do=item&item=32" target="_blank">available from the WBCE Add-On repository</a>, if not already done (make sure existing files will be overwritten).</p> <p style="margin:10px 0">[DE] <strong>Hinweis:</strong> Colorbox-Snippet entdeckt. Nach der Anmeldung an WBCE CMS bitte das Colorbox-Snippet auf Version 1.6.5 aus dem <a href="https://addons.wbce.org/pages/addons.php?do=item&item=32" target="_blank">WBCE Add-On-Verzeichnis</a> aktualisieren, sofern noch nicht geschehen (dabei vorhandene Dateien überschreiben).</p>';    
}
$file_name = WB_PATH . "/modules/cookieconsent/settings/settings.js";
if (file_exists($file_name)) {
    echo '<p style="margin:10px 0">[EN] <strong>Notice:</strong>Cookie Consent module detected. After loggin in to WBCE CMS, please uninstall the module and replace it with a more timely consent banner solution.</p> <p style="margin:10px 0">[DE] <strong>Hinweis:</strong> Cookie-Consent-Snippet ist installiert. Nach der Anmeldung an WBCE CMS bitte das alte Cookie-Consent-Modul deinstallieren und durch ein beliebiges anderes zeitgemäßes Consent-Script ersetzen.</p>';    
}
?>				
				
                    <p>Please Login in the Backend to check your Website</p>
                    <?php
                        if (defined('ADMIN_URL')) {
                            echo '<form action="' . ADMIN_URL . '/">';
                            echo '&nbsp;<input type="submit" value="Login to the Backend" />';
                            echo '</form>';
                        }

                        // Keep the authenticated administrator session and its
                        // configured cookie name intact. Rotating the cookie
                        // name and truncating all sessions forced an otherwise
                        // unnecessary login directly after every update.

                        wbce_do_action('system.update.completed', array(
                            'version' => defined('NEW_WBCE_VERSION') ? NEW_WBCE_VERSION : null,
                        ));
                        wbce_do_action('cache.invalidate', array('scope' => 'all', 'source' => 'system-update'));

                        // Restore public access only when the WBCE updater
                        // itself activated maintenance mode before extraction.
                        $updaterMaintenanceMarker = WB_PATH . '/temp/.wbce-updater-maintenance';
                        if (is_file($updaterMaintenanceMarker)) {
                            Settings::Set('wb_maintainance_mode', '0');
                            if (!(string)Settings::Get('wb_maintainance_mode')) {
                                @unlink($updaterMaintenanceMarker);
                                wbce_do_action('system.maintenance.completed', array('source' => 'wbce_updater'));
                            }
                        }
                        // Do not regenerate the session ID here. The updater can
                        // run inside an embedded same-origin frame; rotating the
                        // ID after files have changed can invalidate the parent
                        // admin request and force an unnecessary login.
                    ?>
                    <?php
                    // Register the verified CMS changelog independently from the
                    // administrator session. It is shown on the dashboard after
                    // the update, never inside the updater progress overlay.
                    if ($updateChangeLog !== '' && class_exists('Settings')) {
                        $changeLogVersion = defined('NEW_WBCE_VERSION') ? (string)NEW_WBCE_VERSION : '';
                        $safeVersion = preg_replace('/[^0-9A-Za-z._-]+/', '-', $changeLogVersion) ?: 'current';
                        $changeLogDirectory = WB_PATH . '/var/update-changelog';
                        if (!is_dir($changeLogDirectory)) @mkdir($changeLogDirectory, 0750, true);
                        $changeLogName = trim($safeVersion, '.-') . '.txt';
                        $registeredChangeLog = $changeLogDirectory . '/' . $changeLogName;
                        if (@file_put_contents($registeredChangeLog, $updateChangeLog, LOCK_EX) !== false) {
                            @chmod($registeredChangeLog, 0640);
                            Settings::Set('wbce_update_changelog_version', $changeLogVersion);
                            Settings::Set('wbce_update_changelog_file', $changeLogName);
                            Settings::Set('wbce_update_changelog_dismissed_version', '');
                        }
                    }
                    @unlink($updateChangeLogFile);
                    // fetch() does process Set-Cookie, but it does not execute
                    // the returned page. Explicitly renew the recovered session
                    // cookie before the parent window is sent to the dashboard.
                    if ($updaterRestoredSessionId !== '' && $updaterRestoredSessionName !== '') {
                        @setcookie($updaterRestoredSessionName, $updaterRestoredSessionId, array(
                            'expires' => 0,
                            'path' => '/',
                            'secure' => (defined('DOMAIN_PROTOCOLL') ? DOMAIN_PROTOCOLL : 'https') === 'https',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ));
                    }
                    // Keep the snapshot for one final, one-time restoration in
                    // admin/start. The browser receives the token as HttpOnly
                    // cookie even though this response was fetched in the
                    // background and its HTML is never rendered.
                    if ($updaterSessionToken !== '') {
                        @setcookie('WBCE-updater-recovery', $updaterSessionToken, array(
                            'expires' => time() + 300,
                            'path' => '/',
                            'secure' => (defined('DOMAIN_PROTOCOLL') ? DOMAIN_PROTOCOLL : 'https') === 'https',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ));
                    }
                    // Persist the handover before JavaScript replaces the parent
                    // frame with the dashboard.
                    if (session_status() === PHP_SESSION_ACTIVE) @session_write_close();
                    ?>
                    <?php if (defined('ADMIN_URL')) { ?>
                    <script>
                    window.setTimeout(function () {
                        var dashboardUrl = <?php echo json_encode(ADMIN_URL . '/start/index.php?update=completed'); ?>;
                        (window.top || window).location.replace(dashboardUrl);
                    }, 0);
                    </script>
                    <?php } ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>
