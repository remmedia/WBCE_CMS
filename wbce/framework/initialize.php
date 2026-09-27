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
 *
 * The main initialization file for WBCE CMS.
 * Takes care of the set up (initialization) of variables and constants,
 * the autoloader class, sessions and settings.
 *
 * Usualy this is included by loading the config.php
 * in the main directory(webroot).
 *
 */

// no direct file access
if (count(get_included_files()) == 1) {
    header("Location: ../index.php", true, 301);
}

// Stop execution if PHP version is too old
$sReqPhpVersion = '8.2.0';
if (version_compare(PHP_VERSION, $sReqPhpVersion, '<')) {
    $sMsg = 'PHP ' . PHP_VERSION . ' running on this system, but at least PHP ' . $sReqPhpVersion . ' required!<br />';
    $sMsg .= 'Please upgrade your PHP Version and try running WBCE CMS again.';
    die($sMsg);
}

// Starting Output buffering
ob_start();

// SOME EARLY CONSTANT HANDLING
// The absolute minimum needed for autoloader and DB

// WB_DEBUG can be overwritten via WBCE config.php (if enabled, max. PHP error output is shown)
defined('WB_DEBUG') or define('WB_DEBUG', false);
// define WB_PATH as it isn't yet defined, installer issue whith missing wbpath
defined("WB_PATH") or define("WB_PATH", dirname(__DIR__));

// INITIALIZE AUTOLOADER
require_once dirname(__FILE__) . "/class.autoload.php";

// PREDB MODULES LOADED HERE
//
// load all predb.php files form module folders that start whith predb_
// These are especially for registering classes that override classes from the main framework
// For example a DB class that uses PDO instead of MYSQLI.
// This one Loads modules even if they are not installed in the DB.
//
// load all predb.php files form folders that start whith predb_
$aPreDb = array();
$aPreDb = glob(dirname(__DIR__) . "/modules/predb_*");
if ($aPreDb !== false && !empty($aPreDb)) {
    foreach ($aPreDb as $sModule) {
        if (file_exists($sModule . "/predb.php")) {
            require_once($sModule . "/predb.php");
        }
    }
}

// INITIALIZE DATABASE CLASS
//
// Database credentials are configured outside the application code.  A failed
// connection must therefore not turn every request into an uncaught exception
// and a blank HTTP 500 response.  Keep the technical detail in the server log
// and return a safe, cacheable error page to the browser instead.
try {
    $database = new database();
} catch (Throwable $exception) {
    error_log('WBCE database startup failed: ' . $exception->getMessage());

    if (PHP_SAPI === 'cli') {
        throw $exception;
    }

    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');
        header('Retry-After: 60');
    }

    echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Dienst vorübergehend nicht verfügbar</title></head><body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;max-width:42rem;margin:12vh auto;padding:0 1.5rem;color:#1f2937"><h1>Dienst vorübergehend nicht verfügbar</h1><p>Die Website kann derzeit keine Verbindung zur Datenbank herstellen.</p><p>Bitte versuchen Sie es in wenigen Minuten erneut. Administratoren finden weitere Informationen im Server-Protokoll.</p></body></html>';
    exit;
}

// SYSTEM CONSTANTS
//
// Now we start definig System constants if not already set
// Lots of compatibility work here, please only use the WB_ constants in future stuff

defined('ADMIN_DIRECTORY') or define('ADMIN_DIRECTORY', 'admin');
defined('ADMIN_URL') or define('ADMIN_URL', WB_URL . '/' . ADMIN_DIRECTORY);
defined('ADMIN_PATH') or define('ADMIN_PATH', WB_PATH . '/' . ADMIN_DIRECTORY);

// first check if someone added crap in the config
if (!preg_match('/xx[a-z0-9_][a-z0-9_\-\.]+/i', 'xx' . ADMIN_DIRECTORY)) {
    die('Invalid admin-directory: ' . ADMIN_DIRECTORY);
}

// Load framework functions before preinit files so we can use functions right away.
require_once(WB_PATH . '/framework/functions.php');
require_once(WB_PATH . '/framework/RuntimeProfiler.php');
WbceRuntimeProfiler::start();
WbceRuntimeProfiler::mark('framework.functions.loaded');
require_once(WB_PATH . '/framework/Php82Compatibility.php');
require_once(WB_PATH . '/framework/HookDispatcher.php');
wbce_do_action('request.initialized', array(
    'method' => (string)($_SERVER['REQUEST_METHOD'] ?? 'CLI'),
    'path' => (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH),
    'sapi' => PHP_SAPI,
));
require_once(WB_PATH . '/framework/CaptchaManager.php');
require_once(WB_PATH . '/framework/AuthenticationProviderInterface.php');
require_once(WB_PATH . '/framework/AuthenticationProviderManager.php');
if (function_exists('wbce_add_filter')) {
    wbce_add_filter('user.preferences.sections', static function (array $sections, int $userId): array {
        $section = WbceAuthenticationProviderManager::renderUserSection($userId);
        if ($section !== '') $sections[] = $section;
        return $sections;
    }, 5);
}
require_once(WB_PATH . '/framework/AuthFactorProviderInterface.php');
require_once(WB_PATH . '/framework/AuthFactorManager.php');
require_once(WB_PATH . '/framework/AuthenticationSession.php');
require_once(WB_PATH . '/framework/PasswordResetService.php');

// During a regular WBCE update the new files are copied before update.php can
// add the WBCE 1.7 database columns. Keep bootstrap functional in that window.
$wbceAddonActiveSql = $database->field_exists('{TP}addons', 'active')
    ? "`active` = 1 AND "
    : '';
defined('WBCE_ADDON_ACTIVE_SQL') or define('WBCE_ADDON_ACTIVE_SQL', $wbceAddonActiveSql);

// Block direct PHP entry points of disabled WBCE 1.7 modules. Normal module
// includes run through the central loaders checked below.
if (PHP_SAPI !== 'cli' && !empty($_SERVER['SCRIPT_FILENAME'])) {
    $moduleRoot = realpath(WB_PATH . '/modules');
    $scriptPath = realpath($_SERVER['SCRIPT_FILENAME']);
    if ($moduleRoot && $scriptPath && strpos($scriptPath, $moduleRoot . DIRECTORY_SEPARATOR) === 0) {
        $relative = substr($scriptPath, strlen($moduleRoot) + 1);
        $moduleDirectory = strtok(str_replace('\\', '/', $relative), '/');
        if ($moduleDirectory && !wbce_addon_is_active($moduleDirectory, 'module')) {
            http_response_code(503);
            exit('This module is disabled.');
        }
    }
}


// PRE_INIT MODULES
//
// Pre init, modules may change everyting as almost nothing is already set here
// Module may hook here to change page_id, Language or whatever. Even most System Constants.
// As DB is already available here we rely on installed modules.
//
// @todo check if we need to make more modifications to the core to get this fully running
// @todo check if we better use  MYSQL FIND_IN_SET (http://forum.wbce.org/viewtopic.php?id=84)

// Query gives back false on failure
if (($resSnippets = $database->query("SELECT `directory` FROM `{TP}addons` WHERE " . $wbceAddonActiveSql . "`function` LIKE '%preinit%'"))) {
    while ($rec = $resSnippets->fetchRow()) {
        $sModFilePath = dirname(__DIR__) . '/modules/' . $rec['directory'] . '/preinit.php';
        if (file_exists($sModFilePath)) {
            WbceRuntimeProfiler::begin('module.preinit.' . $rec['directory']);
            include $sModFilePath;
            WbceRuntimeProfiler::end('module.preinit.' . $rec['directory']);
        }
    }
}


// define DOMAIN_PROTOCOLL constant
$protocoll = "http";
// $_SERVER['HTTPS'] alone is not reliable ... :-(
// https://github.com/dmikusa-pivotal/cf-php-apache-buildpack/issues/6
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $protocoll = "https";
}
if (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == 443) {
    $protocoll = "https";
}
if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] and $_SERVER['HTTPS'] != "off") {
    $protocoll = "https";
}
define("DOMAIN_PROTOCOLL", $protocoll);

// Conservative response headers that are compatible with legacy inline
// scripts and styles. Modules may add a stricter CSP for their own endpoints.
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: frame-ancestors 'self'");
    header('X-Frame-Options: SAMEORIGIN');
    if (DOMAIN_PROTOCOLL === 'https') {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

// MORE AUTOLOADER REGISTRATION
// Registering additional classes that are needed by the core

// Registering class for idna conversion (needed for email-checks)
WbAuto::AddFile("idna_convert", "/include/idna_convert/idna_convert.class.php");
WbAuto::AddFile("SecureForm", "/framework/SecureForm.php");

// Auto Load the Insert and I Classes
WbAuto::AddFile("Insert", "/framework/Insert.php");
WbAuto::AddFile("I", "/framework/I.php");

// Auto Load Mailer Class (subclass of PHPMailer)
WbAuto::AddFile("Mailer", "/framework/Mailer.php");
WbAuto::AddFile("wbmailer", "/framework/Mailer.php"); // fallback for older modules

// Auto Accounts Class
WbAuto::AddFile("Accounts", "/framework/Accounts.php"); // child class

// Auto MessageBox Class
WbAuto::AddFile("MessageBox", "/framework/MessageBox.php"); // child class

// Auto Load phpLib (the ancient Templating Engine)
WbAuto::AddFile("Template", "/include/phplib/template.inc");

// Connect to Twig TE (the contemporary Templating Engine)
require_once WB_PATH . '/include/Sensio/Twig/WBCETwigLoader.php';

// SETUP SYSTEM CONSTANTS (GLOBAL SETTINGS)
// We use Settings Class to fetch all Settings from DB
// Then we process all data into the coresponding constants.
Settings::Setup(); // Fetch all settings whith Settings class from framework
WbceRuntimeProfiler::mark('settings.loaded');
require_once WB_PATH . '/framework/Timezone.php';

// RESULTING CONSTANTS
// Some resulting constants need to be set manually

// Filemodes
$string_file_mode = STRING_FILE_MODE;
define('OCTAL_FILE_MODE', (int)octdec($string_file_mode));
define('WB_OCTAL_FILE_MODE', (int)octdec($string_file_mode));
// Dirmodes
$string_dir_mode = STRING_DIR_MODE;
define('OCTAL_DIR_MODE', (int)octdec($string_dir_mode));
define('WB_OCTAL_DIR_MODE', (int)octdec($string_dir_mode));

switch (true) {
    case (WB_DEBUG === true):
        // Debugging activated
        error_reporting(E_ALL);
        break;

    case (intval(ER_LEVEL) > 0):
        //Historical compatibility stuff
        error_reporting(E_ALL);
        break;

    case (ER_LEVEL == "-1"):
        //Historical compatibility stuff
        error_reporting(E_ALL);
        break;

    case (ER_LEVEL == "E0"):
        // system default (php.ini)
         error_reporting((int)ini_get('error_reporting'));
        break;

    case (ER_LEVEL == "E1"):
        // hide all errors and notices
        error_reporting(0);
        break;

    case (ER_LEVEL == "E2"):
        // show all errors and notices
        error_reporting(E_ALL);
        break;

    case (ER_LEVEL == "E3"):
        // show only errors, nothing else
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        break;

    default:
        // system default (php.ini)
        error_reporting(ini_get('error_reporting'));
}
// Adapt display_error directive in php.ini to reflect error_reporting level
ini_set('display_errors', (error_reporting() == 0) ? 0 : 1);

// Keep legacy date arithmetic in UTC. IANA conversion is performed through
// the WBCE timezone helpers so old code adding TIMEZONE is not shifted twice.
date_default_timezone_set('UTC');


//SESSION
//Initialize Custom Session Handler
//Stores Sessions to DB
//As session table possibly not installed, it may not run whith installer and upgradescript
//We then simply fallback to PHP default Session handling.

// Init custom session handler
$hCustomSessionHandler = new DbSession();

// Init  special Session handling and Start session.
WSession::Start();


// MODULES INITIALIZE.PHP
// For now we put modules initialize.php here
// Yes! All modules are now allowed to have a initialize.php. function='initialize'
// You can even change the $page_id, or maybe the Language .
// You can log users in or out and do what you like
// Initialize Modules normaly do not distinguish between FE and BE

$sSql = "SELECT `directory` FROM `{TP}addons` WHERE " . $wbceAddonActiveSql . "`type`='module'";
if (($resSnippets = $database->query($sSql))) {
    while ($rec = $resSnippets->fetchRow()) {
        $sFile = WB_PATH . '/modules/' . $rec['directory'] . '/initialize.php';
        if (file_exists($sFile)) {
            WbceRuntimeProfiler::begin('module.initialize.' . $rec['directory']);
            include $sFile;
            WbceRuntimeProfiler::end('module.initialize.' . $rec['directory']);
        }
    }
}

// SANITIZE REFERER
// sanitize $_SERVER['HTTP_REFERER']
// @todo Needs to be repaced ASAP.
// Currently it's the only way to have a halfway save refrerer string.
SanitizeHttpReferer();

// LANGUAGES
// Only if no module already did this
if (!defined("LANGUAGE")) {
    // Get users language
    if (isset($_GET['lang']) && preg_match('/^[A-Z]{2}$/', $_GET['lang'])) {
        define('LANGUAGE', $_GET['lang']);
        $_SESSION['LANGUAGE'] = LANGUAGE;
    } else {
        if (isset($_SESSION['LANGUAGE']) && $_SESSION['LANGUAGE'] != '') {
            define('LANGUAGE', $_SESSION['LANGUAGE']);
        } else {
            define('LANGUAGE', DEFAULT_LANGUAGE);
        }
    }
}


// Needed in account and Account AdminTool
if (FRONTEND_LOGIN) {
    if (!defined('ACCOUNT_PATH')) {
        // Set login menu constants
        defined('ACCOUNT_URL') or define('ACCOUNT_URL', WB_URL . '/account');
        define('ACCOUNT_PATH', str_replace(WB_URL, WB_PATH, ACCOUNT_URL));

        define('LOGIN_URL', ACCOUNT_URL . '/login.php');
        define('LOGOUT_URL', ACCOUNT_URL . '/logout.php');
        define('FORGOT_URL', ACCOUNT_URL . '/forgot.php');
        define('PREFERENCES_URL', ACCOUNT_URL . '/preferences.php');
        define('SIGNUP_URL', ACCOUNT_URL . '/signup.php');
    }
}


// Load default language file so even incomplete language files display at least the english text
if (!file_exists(WB_PATH . '/languages/EN.php')) {
    exit('Error loading default language file (EN), please check configuration and file');
} else {
    // we always load EN language file
    require_once WB_PATH . '/languages/EN.php';
}

// Load LC language file if LANGUAGE != EN
if (LANGUAGE != 'EN') {
    $sLangFile = WB_PATH . '/languages/' . LANGUAGE . '.php';
    if (file_exists($sLangFile)) {
        require_once $sLangFile;
    }
}
// include old languages format  only for compatibility only needed for some old modules
if (file_exists(WB_PATH . '/languages/old.format.inc.php')) {
    include WB_PATH . '/languages/old.format.inc.php';
}

// The registry based language API is used by current modules and Twig views.
// Keep it in sync with the language resolved above; otherwise it remains at
// its EN default even when the administrator selected German.
require_once WB_PATH . '/framework/i18n/init.php';
Lang::setLocale((string) LANGUAGE);
Lang::loadCore((string) LANGUAGE, WB_PATH . '/languages');
Lang::syncToGlobals();

define("LANGUAGE_LOADED", true);

// simple template switcher
if (defined('TEMPLATE_SWITCHER') && TEMPLATE_SWITCHER == true) {
    if (isset($_GET['reset_template'])) {
        unset($_SESSION['wb_preview_tpl']);
    }
    if (isset($_SESSION['wb_preview_tpl']) && !file_exists(WB_PATH.'/templates/'.$_SESSION['wb_preview_tpl'].'/info.php')) {
        unset($_SESSION['wb_preview_tpl']);		
    }
    if (isset($_GET['template'])) {
        $core_preview_template = preg_replace("/(\.\.\/)/","", $_GET['template']);
        if (isset($_GET['template']) && is_string($core_preview_template) && file_exists(WB_PATH.'/templates/'.$core_preview_template.'/info.php')) {
            $_SESSION['wb_preview_tpl'] = $core_preview_template;
            define('TEMPLATE', $_SESSION['wb_preview_tpl']);
        }    
    } elseif (isset($_SESSION['wb_preview_tpl'])) {
        if (is_string($_SESSION['wb_preview_tpl']) && file_exists(WB_PATH.'/templates/'.$_SESSION['wb_preview_tpl'].'/info.php')) {            
            define('TEMPLATE', $_SESSION['wb_preview_tpl']);
        }    
    }
}

// define more system constants
defined("THEME_URL") or define('THEME_URL', WB_URL . '/templates/' . DEFAULT_THEME);
defined("THEME_PATH") or define('THEME_PATH', WB_PATH . '/templates/' . DEFAULT_THEME);
defined("EDIT_ONE_SECTION") or define('EDIT_ONE_SECTION', false);
defined("EDITOR_WIDTH") or define('EDITOR_WIDTH', 0);

// TIMEZONE and DATE/TIME FORMAT constants
define('TIMEZONE_IDENTIFIER', wbce_timezone_identifier(isset($_SESSION['TIMEZONE']) ? $_SESSION['TIMEZONE'] : null));
define('TIMEZONE', wbce_timezone_offset());
define('DATE_FORMAT', isset($_SESSION['DATE_FORMAT']) ? $_SESSION['DATE_FORMAT'] : DEFAULT_DATE_FORMAT);
define('TIME_FORMAT', isset($_SESSION['TIME_FORMAT']) ? $_SESSION['TIME_FORMAT'] : DEFAULT_TIME_FORMAT);

// FETCH WBCE VERSION
// Version should be available not only in admin section(BE)
require_once ADMIN_PATH . '/interface/version.php';

// ////////////////////////////////////////////////////////////////////////////////
//  HELPER FUNCTIONS
//  Moved down here as they need to be removed/reworked sooner or later
// ////////////////////////////////////////////////////////////////////////////////

/**
 * @brief  sanitize $_SERVER['HTTP_REFERER']
 * @todo   Change WBCE so it uses the save referrer and no longer touches the basic referrer
 */
function SanitizeHttpReferer()
{
    $sTmpReferer = '';
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
        $aRefUrl = parse_url($_SERVER['HTTP_REFERER']);
        if ($aRefUrl !== false) {
            $aRefUrl['host'] = isset($aRefUrl['host']) ? $aRefUrl['host'] : '';
            $aRefUrl['path'] = isset($aRefUrl['path']) ? $aRefUrl['path'] : '';
            $aRefUrl['fragment'] = isset($aRefUrl['fragment']) ? '#' . $aRefUrl['fragment'] : '';
            $aWbUrl = parse_url(WB_URL);
            if ($aWbUrl !== false) {
                $aWbUrl['host'] = isset($aWbUrl['host']) ? $aWbUrl['host'] : '';
                $aWbUrl['path'] = isset($aWbUrl['path']) ? $aWbUrl['path'] : '';
                if (strpos($aRefUrl['host'] . $aRefUrl['path'], $aWbUrl['host'] . $aWbUrl['path']) !== false) {
                    $aRefUrl['path'] = preg_replace('#^' . $aWbUrl['path'] . '#i', '', $aRefUrl['path']);
                    $sTmpReferer = WB_URL . $aRefUrl['path'] . $aRefUrl['fragment'];
                }
                unset($aWbUrl);
            }
            unset($aRefUrl);
        }
    }
    $_SERVER['HTTP_REFERER'] = $sTmpReferer;
    $_SERVER['WB_SECURE_HTTP_REFERER'] = $sTmpReferer;
}
