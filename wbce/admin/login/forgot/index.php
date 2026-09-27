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

require realpath('../../../config.php');


$admin  = new Admin('Start', 'start', false, false);
$alerts = new Alerts(useSession: false);

$nocookie = defined('NO_SESSION_COOKIE') && NO_SESSION_COOKIE;
$email    = '';
$showForm = true;
$formSubmitted = $_SERVER['REQUEST_METHOD'] === 'POST';

// === Handle POST =============================================================
if ($formSubmitted && !empty($_POST['email'])) {

    $email = strip_tags(trim($admin->get_post('email') ?? ''));

    // --- FTAN check ---
    if (!$admin->checkFTAN()) {
        $alerts->error('MESSAGE:GENERIC_SECURITY_ACCESS');
        $email = '';
    }

    // --- Captcha validation (FIXED: no timestamp fallback) ---
    if ($email !== '' && !$nocookie) {
        $captchaInput = $_POST['captcha'] ?? '';

        if ($captchaInput === '') {
            $alerts->error('MESSAGE:MOD_FORM_INCORRECT_CAPTCHA');
            $email = '';
        } else {
            // Only compare against actual session captcha values.
            // NEVER fall back to time() — that's the vulnerability.
            $validCaptcha1 = $_SESSION['captcha']            ?? null;
            $validCaptcha2 = $_SESSION['captchaloginforgot'] ?? null;

            if ($validCaptcha1 === null && $validCaptcha2 === null) {
                // No captcha in session = session expired or tampered
                $alerts->error('MESSAGE:MOD_FORM_INCORRECT_CAPTCHA');
                $email = '';
            } elseif ($captchaInput != $validCaptcha1 && $captchaInput != $validCaptcha2) {
                $alerts->error('MESSAGE:MOD_FORM_INCORRECT_CAPTCHA');
                $email = '';
            }
        }
    }

    // --- Validate email format ---
    if ($email !== '' && !$admin->validate_email($email)) {
        $alerts->error('MESSAGE:USERS_INVALID_EMAIL');
        $email = '';
    }

    // Issue a single-use token without disclosing account existence or rate limits.
    if ($email !== '') {
        $service = new WbcePasswordResetService($database);
        $issued = $service->issue($email);
        if ($issued) {
            $resetUrl = WB_URL . '/account/reset-password.php?token=' . rawurlencode($issued['token']);
            $subject = 'Passwort für ' . WEBSITE_TITLE . ' zurücksetzen';
            $body = "Hallo " . $issued['user']['display_name'] . ",\n\n"
                . "über den folgenden Link kannst du innerhalb einer Stunde ein neues Passwort festlegen:\n\n"
                . $resetUrl . "\n\nWenn du diese Anfrage nicht gestellt hast, kannst du diese E-Mail ignorieren.";
            if (!$admin->mail(SERVER_EMAIL, $issued['user']['email'], $subject, $body)) $service->revoke($issued['selector']);
        }
        $alerts->success('Wenn ein aktives Konto zu dieser Adresse gehört, wurde ein Link versendet.');
        $showForm = false;
    }
}

// Default info message when no errors shown
if ($formSubmitted && empty($_POST['email']) && !$alerts->hasErrors() && $showForm) {
    $alerts->info('MESSAGE:FORGOT_PASS_NO_DATA');
}


// Captcha HTML
$captchaHtml = '';
if (!$nocookie) {
    ob_start();
    Captcha::render('widget');
    $captchaHtml = ob_get_clean();
}

// === Render ==================================================================
$toTwig = [
    'MESSAGE'      => $alerts->render(),
    'WB_URL'       => WB_URL,
    'ADMIN_URL'    => ADMIN_URL,
    'THEME_URL'    => THEME_URL,
    'LANGUAGE'     => strtolower(LANGUAGE),
    'EMAIL'        => h($email),
    'SHOW_FORM'    => $showForm,
    'FORM_SUBMITTED' => $formSubmitted,
    'ACTION_URL'   => defined('FRONTEND') ? 'forgot.php' : 'index.php',
    'LOGIN_URL'    => defined('FRONTEND') ? WB_URL . '/account/login.php' : ADMIN_URL,
    'CAPTCHA'      => $captchaHtml,
    'CHARSET'      => defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8',
];

$admin->getThemeFile('login_forgot.twig', $toTwig);
