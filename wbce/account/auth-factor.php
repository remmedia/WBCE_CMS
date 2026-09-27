<?php

require_once dirname(__DIR__) . '/config.php';

$challenge = WbceAuthFactorManager::current();
$provider = WbceAuthFactorManager::currentProvider();
if (!$challenge || !$provider) {
    WbceAuthFactorManager::cancel();
    header('Location: ' . ADMIN_URL . '/login/index.php');
    exit;
}

$user = $database->fetchRow('SELECT * FROM `{TP}users` WHERE `user_id` = ? AND `active` = 1', [(int)$challenge['user_id']]);
if (!$user) {
    $cancelUrl = wbce_safe_redirect_url((string)($challenge['cancel_url'] ?? ''), ADMIN_URL . '/login/index.php');
    WbceAuthFactorManager::cancel();
    header('Location: ' . $cancelUrl);
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $nonce = (string)($_POST['_auth_nonce'] ?? '');
    if (!hash_equals((string)$challenge['nonce'], $nonce) || !$provider->verify($user, $_POST)) {
        $locked = WbceAuthFactorManager::recordFailure();
        wbce_do_action('auth.challenge.failed', (int)$user['user_id'], $provider->getId(), $locked);
        if ($locked) {
            header('Location: ' . wbce_safe_redirect_url((string)($challenge['cancel_url'] ?? ''), ADMIN_URL . '/login/index.php'));
            exit;
        }
        $error = 'Der Sicherheitscode ist ungültig.';
    } else {
        wbce_do_action('auth.challenge.verified', (int)$user['user_id'], $provider->getId());
        if (WbceAuthFactorManager::advance()) {
            $completed = WbceAuthFactorManager::consume();
            WbceAuthenticationSession::complete($user, $database);
            $returnUrl = wbce_apply_filters('auth.login.redirect', (string)$completed['return_url'], ['user_id' => (int)$user['user_id']]);
            header('Location: ' . wbce_safe_redirect_url((string)$returnUrl));
            exit;
        }
        header('Location: ' . WB_URL . '/account/auth-factor.php');
        exit;
    }
}

header('Content-Type: text/html; charset=UTF-8');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
$content = $provider->renderChallenge($user, $error);
$nonceField = '<input type="hidden" name="_auth_nonce" value="'
    . htmlspecialchars((string)$challenge['nonce'], ENT_QUOTES, 'UTF-8') . '">';
if (stripos($content, '<form') !== false && stripos($content, '</form>') !== false) {
    $content = preg_replace('/<\/form>/i', $nonceField . '</form>', $content, 1) ?? $content;
} else {
    $content = '<form method="post" autocomplete="off">' . $nonceField . $content . '</form>';
}
?><!doctype html>
<html lang="<?= htmlspecialchars(strtolower(defined('LANGUAGE') ? LANGUAGE : 'de'), ENT_QUOTES, 'UTF-8') ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Anmeldung bestätigen</title>
<style>body{margin:0;background:#f3f6fa;font:16px/1.5 system-ui,sans-serif;color:#253247}.auth{max-width:34rem;margin:8vh auto;background:#fff;border:1px solid #dce4ee;border-radius:14px;box-shadow:0 12px 35px #27364b1a;overflow:hidden}.auth h1{margin:0;padding:1.2rem 1.5rem;background:#1769aa;color:#fff;font-size:1.3rem}.auth main{padding:1.5rem}.auth button{background:#1769aa;color:#fff;border:0;border-radius:7px;padding:.7rem 1.1rem;font-weight:650;cursor:pointer}</style></head>
<body><section class="auth"><h1>Anmeldung bestätigen</h1><main><?= $content ?></main></section></body></html>
