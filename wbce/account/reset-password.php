<?php
require_once dirname(__DIR__) . '/config.php';

$service = new WbcePasswordResetService($database);
$helper = new Admin('', 'start', false, false);
$token = (string)($_POST['token'] ?? $_GET['token'] ?? '');
$reset = $service->validate($token);
$error = '';
$success = false;

if ($reset && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $_SESSION['PASSWORD_RESET_FORM_NONCE'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $nonce = (string)($_POST['reset_nonce'] ?? '');
    $sessionNonce = (string)($_SESSION['PASSWORD_RESET_FORM_NONCE'] ?? '');
    if (!$reset || $sessionNonce === '' || !hash_equals($sessionNonce, $nonce)) {
        $error = 'Dieser Link ist ungültig oder abgelaufen.';
    } else {
        $password = (string)($_POST['new_password'] ?? '');
        $confirmation = (string)($_POST['new_password_confirmation'] ?? '');
        $encoded = $helper->checkPasswordPattern($password, $confirmation);
        if (is_array($encoded)) {
            $error = implode('<br>', array_map('h', $encoded));
        } elseif ($helper->doCheckPassword((int)$reset['user_id'], $password)) {
            $error = 'Das neue Passwort muss sich vom bisherigen Passwort unterscheiden.';
        } elseif ($service->complete($token, $encoded)) {
            unset($_SESSION['PASSWORD_RESET_FORM_NONCE']);
            $success = true;
        } else {
            $error = 'Das Passwort konnte nicht gespeichert werden.';
        }
    }
}

header('Content-Type: text/html; charset=UTF-8');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Passwort zurücksetzen</title><style>body{font:16px/1.5 system-ui,sans-serif;background:#f2f4f6;color:#20252a;margin:0}main{max-width:30rem;margin:10vh auto;background:#fff;padding:2rem;border-radius:.5rem;box-shadow:0 3px 20px #0002}label{display:block;margin:1rem 0}input,button{box-sizing:border-box;width:100%;padding:.75rem;font:inherit}.error{background:#fee;color:#900;padding:.75rem}.success{background:#efe;color:#174f17;padding:.75rem}</style>
</head><body><main><h1>Neues Passwort festlegen</h1>
<?php if ($success): ?><p class="success">Das Passwort wurde geändert. Du kannst dich jetzt anmelden.</p><p><a href="<?= h(ADMIN_URL) ?>/login/">Zur Anmeldung</a></p>
<?php elseif (!$reset): ?><p class="error">Dieser Link ist ungültig, abgelaufen oder wurde bereits verwendet.</p><p><a href="<?= h(ADMIN_URL) ?>/login/forgot/">Neuen Link anfordern</a></p>
<?php else: ?><?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?><form method="post" autocomplete="off">
<input type="hidden" name="token" value="<?= h($token) ?>"><input type="hidden" name="reset_nonce" value="<?= h($_SESSION['PASSWORD_RESET_FORM_NONCE'] ?? '') ?>">
<label>Neues Passwort<input type="password" name="new_password" required autocomplete="new-password"></label>
<label>Passwort wiederholen<input type="password" name="new_password_confirmation" required autocomplete="new-password"></label>
<button type="submit">Passwort speichern</button></form><?php endif; ?></main></body></html>
