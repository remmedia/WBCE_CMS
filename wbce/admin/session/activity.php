<?php
/**
 * Renew the backend idle timeout after a verified browser interaction.
 * This endpoint deliberately has no output and must not be called by polling.
 */
require dirname(__DIR__, 2) . '/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_SESSION['USER_ID'])) {
    http_response_code(403);
    exit;
}

$providedToken = (string) ($_SERVER['HTTP_X_WBCE_SESSION_ACTIVITY'] ?? '');
$sessionToken = (string) WSession::Get('session_activity_token', '');
if ($sessionToken === '' || !hash_equals($sessionToken, $providedToken)) {
    http_response_code(403);
    exit;
}

$timeout = max(60, (int) WSession::$Expire);
WSession::Set('discard_after', time() + $timeout);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'expires_at' => time() + $timeout]);
