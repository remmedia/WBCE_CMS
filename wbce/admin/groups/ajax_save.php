<?php
require '../../config.php';
require_once WB_PATH . '/framework/Admin.php';
header('Content-Type: application/json; charset=utf-8');

$admin = new admin('Access', 'groups', false, false);
$reply = static function (bool $ok, string $message, array $extra = array()): void {
    if (!$ok) { http_response_code(400); }
    echo json_encode(array_merge(array('ok'=>$ok, 'message'=>$message), $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

try {
    if (!$admin->is_authenticated() || !$admin->checkFTAN()) {
        throw new RuntimeException($MESSAGE['GENERIC_SECURITY_ACCESS']);
    }
    $idKey = (string)($_POST['group_id'] ?? '');
    $groupId = $idKey !== '' ? (int)$admin->checkIDKEY('group_id', 0, 'POST') : 0;
    if ($idKey !== '' && $groupId < 2) { throw new RuntimeException($MESSAGE['GENERIC_SECURITY_ACCESS']); }
    $isUpdate = $groupId >= 2;
    $requiredPermission = $isUpdate ? 'groups_modify' : 'groups_add';
    if (!$admin->get_permission($requiredPermission)) {
        throw new RuntimeException($MESSAGE['GENERIC_SECURITY_ACCESS']);
    }
    $groupName = trim(strip_tags((string)$admin->get_post('group_name')));
    if ($groupName === '') { throw new InvalidArgumentException($MESSAGE['GROUPS_GROUP_NAME_BLANK']); }
    $escapedName = $database->escapeString($groupName);
    $duplicateSql = "SELECT COUNT(*) FROM `{TP}groups` WHERE `name`='" . $escapedName . "'" . ($isUpdate ? ' AND `group_id`!=' . $groupId : '');
    if ((int)$database->get_one($duplicateSql) > 0) { throw new InvalidArgumentException($MESSAGE['GROUPS_GROUP_NAME_EXISTS']); }

    require ADMIN_PATH . '/groups/get_permissions.php';
    if ($isUpdate) {
        $database->query("UPDATE `{TP}groups` SET `name`='".$escapedName."',`system_permissions`='".$database->escapeString($system_permissions)."',`module_permissions`='".$database->escapeString($module_permissions)."',`template_permissions`='".$database->escapeString($template_permissions)."' WHERE `group_id`=".$groupId);
        $message = $MESSAGE['GROUPS_SAVED'];
    } else {
        $database->query("INSERT INTO `{TP}groups` (`name`,`system_permissions`,`module_permissions`,`template_permissions`) VALUES ('".$escapedName."','".$database->escapeString($system_permissions)."','".$database->escapeString($module_permissions)."','".$database->escapeString($template_permissions)."')");
        $groupId = (int)$database->get_one('SELECT LAST_INSERT_ID()');
        $message = $MESSAGE['GROUPS_ADDED'];
    }
    if ($database->is_error()) { throw new RuntimeException($database->get_error()); }
    $reply(true, $message, array('groupId'=>$groupId, 'groupIdKey'=>$admin->getIDKEY($groupId)));
} catch (Throwable $exception) {
    $reply(false, $exception->getMessage());
}
