<?php
require_once dirname(__DIR__, 3) . '/config.php';
require_once WB_PATH . '/framework/Admin.php';

header('Content-Type: application/json; charset=UTF-8');
$admin = new admin('Preferences', 'start', false);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        require_once ADMIN_PATH . '/interface/languages.php';
        $languages = array();
        foreach (getLanguagesArray() as $language) {
            $languages[] = array('code' => (string)$language['CODE'], 'name' => (string)$language['NAME']);
        }
        echo json_encode(array('success' => true, 'current' => strtoupper(LANGUAGE), 'languages' => $languages, 'ftan' => $admin->getFTAN(false)));
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$admin->checkFTAN()) throw new RuntimeException('Sicherheitsprüfung fehlgeschlagen.');
    $language = strtoupper(trim((string)($_POST['language'] ?? '')));
    if (!preg_match('/^[A-Z]{2}$/', $language) || !is_file(WB_PATH . '/languages/' . $language . '.php')) throw new RuntimeException('Die ausgewählte Sprache ist nicht installiert.');
    $userId = (int)$admin->get_user_id();
    if (!$database->query("UPDATE `{TP}users` SET `language`='".$database->escapeString($language)."' WHERE `user_id`=".$userId)) throw new RuntimeException('Die Sprache konnte nicht gespeichert werden.');
    $_SESSION['LANGUAGE'] = $language;
    echo json_encode(array('success' => true, 'language' => $language));
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => $exception->getMessage()));
}
