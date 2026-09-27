<?php

require_once '../../config.php';

$challenge = WbceAuthFactorManager::current();
$provider = WbceAuthFactorManager::currentProvider();
if (!$challenge || !$provider) {
    WbceAuthFactorManager::cancel();
    header('Location: ' . ADMIN_URL . '/login/index.php');
    exit;
}

$result = $database->query(sprintf(
    'SELECT * FROM `{TP}users` WHERE `user_id` = %d AND `active` = 1',
    (int)$challenge['user_id']
));
$user = $result->fetchRow(MYSQLI_ASSOC);
if (!$user) {
    WbceAuthFactorManager::cancel();
    header('Location: ' . ADMIN_URL . '/login/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nonce = isset($_POST['auth_nonce']) ? (string)$_POST['auth_nonce'] : '';
    if (!hash_equals($challenge['nonce'], $nonce)) {
        $error = 'Die Sicherheitsprüfung ist fehlgeschlagen. Bitte erneut anmelden.';
        WbceAuthFactorManager::cancel();
    } elseif ($provider->verify($user, $_POST)) {
        wbce_do_action('auth.challenge.passed', $provider->getId(), (int)$user['user_id']);
        if (WbceAuthFactorManager::advance()) {
            $completed = WbceAuthFactorManager::consume();
            WbceAuthenticationSession::complete($user, $database);
            $redirect = wbce_apply_filters('auth.login.redirect', $completed['return_url'], $user);
            $redirect = wbce_safe_redirect_url($redirect, ADMIN_URL . '/start/index.php');
            header('Location: ' . $redirect);
            exit;
        }
        header('Location: ' . ADMIN_URL . '/login/factor.php');
        exit;
    } else {
        $error = 'Der eingegebene Sicherheitscode ist ungültig.';
        wbce_do_action('auth.challenge.failed', $provider->getId(), (int)$user['user_id']);
        if (WbceAuthFactorManager::recordFailure()) {
            header('Location: ' . ADMIN_URL . '/login/index.php');
            exit;
        }
    }
}

header('Content-Type: text/html; charset=UTF-8');
?><!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zusätzliche Anmeldung</title>
    <style>
        body{font:16px/1.5 system-ui,sans-serif;background:#f2f4f6;margin:0;color:#20252a}
        main{max-width:28rem;margin:10vh auto;background:#fff;padding:2rem;border-radius:.5rem;box-shadow:0 3px 20px #0002}
        input,button{box-sizing:border-box;width:100%;padding:.75rem;margin:.4rem 0;font:inherit}
        .error{padding:.75rem;background:#fee;color:#900}
    </style>
</head>
<body><main>
    <?php echo $provider->renderChallenge($user, $error); ?>
    <input type="hidden" form="wbce-auth-factor" name="auth_nonce" value="<?php echo htmlspecialchars($challenge['nonce'], ENT_QUOTES, 'UTF-8'); ?>">
</main></body></html>
