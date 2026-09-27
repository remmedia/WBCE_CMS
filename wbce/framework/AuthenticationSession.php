<?php

final class WbceAuthenticationSession
{
    public static function complete(array $user, Database $database): void
    {
        $userId = (int)$user['user_id'];
        session_regenerate_id(true);
        // Start a new idle timeout for the authenticated session. It is renewed
        // only by the verified user-activity endpoint, never by background requests.
        $timeout = max(60, (int) Settings::get('wb_session_timeout', 7200));
        WSession::Set('discard_after', time() + $timeout);
        $_SESSION['USER_ID'] = $userId;
        $_SESSION['GROUP_ID'] = $user['group_id'];
        $_SESSION['GROUPS_ID'] = $user['groups_id'];
        $_SESSION['USERNAME'] = $user['username'];
        $_SESSION['DISPLAY_NAME'] = $user['display_name'];
        $_SESSION['EMAIL'] = $user['email'];
        $_SESSION['HOME_FOLDER'] = $user['home_folder'];
        if ($user['language'] !== '') $_SESSION['LANGUAGE'] = $user['language'];
        self::setting($user, 'timezone', 'TIMEZONE', 'USE_DEFAULT_TIMEZONE');
        self::setting($user, 'date_format', 'DATE_FORMAT', 'USE_DEFAULT_DATE_FORMAT');
        self::setting($user, 'time_format', 'TIME_FORMAT', 'USE_DEFAULT_TIME_FORMAT');

        $_SESSION['SYSTEM_PERMISSIONS'] = [];
        $_SESSION['MODULE_PERMISSIONS'] = [];
        $_SESSION['TEMPLATE_PERMISSIONS'] = [];
        $_SESSION['GROUP_NAME'] = [];
        $_SESSION['DEFAULT_MODULE'] = '';
        $first = true;
        foreach (explode(',', (string)$user['groups_id']) as $groupId) {
            $group = $database->fetchRow('SELECT * FROM `{TP}groups` WHERE `group_id` = ?', [(int)$groupId]);
            if (!$group) continue;
            $_SESSION['GROUP_NAME'][$groupId] = $group['name'];
            if ($first) $_SESSION['DEFAULT_MODULE'] = $group['default_module'] ?? '';
            if ($group['system_permissions'] !== '') {
                $_SESSION['SYSTEM_PERMISSIONS'] = array_merge($_SESSION['SYSTEM_PERMISSIONS'], explode(',', $group['system_permissions']));
            }
            if ($group['module_permissions'] !== '') {
                $values = explode(',', $group['module_permissions']);
                $_SESSION['MODULE_PERMISSIONS'] = $first ? $values : array_intersect($_SESSION['MODULE_PERMISSIONS'], $values);
            }
            if ($group['template_permissions'] !== '') {
                $values = explode(',', $group['template_permissions']);
                $_SESSION['TEMPLATE_PERMISSIONS'] = $first ? $values : array_intersect($_SESSION['TEMPLATE_PERMISSIONS'], $values);
            }
            $first = false;
        }
        $permissions = wbce_apply_array_filters('permissions.resolved', [
            'system' => array_values(array_unique($_SESSION['SYSTEM_PERMISSIONS'])),
            'modules' => array_values($_SESSION['MODULE_PERMISSIONS']),
            'templates' => array_values($_SESSION['TEMPLATE_PERMISSIONS']),
        ], $userId, $user);
        $_SESSION['SYSTEM_PERMISSIONS'] = $permissions['system'] ?? [];
        $_SESSION['MODULE_PERMISSIONS'] = $permissions['modules'] ?? [];
        $_SESSION['TEMPLATE_PERMISSIONS'] = $permissions['templates'] ?? [];

        $database->upsertRow('{TP}users', 'user_id', [
            'user_id' => $userId, 'login_when' => time(), 'login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        wbce_do_action('auth.session.created', $userId, session_id());
        wbce_do_action('auth.login.succeeded', $userId);
        wbce_do_action('auth.login.completed', $userId, $user);
    }

    private static function setting(array $user, string $field, string $key, string $defaultKey): void
    {
        if (($user[$field] ?? '') !== '') {
            $_SESSION[$key] = $user[$field];
            unset($_SESSION[$defaultKey]);
        } else {
            $_SESSION[$defaultKey] = true;
            unset($_SESSION[$key]);
        }
    }
}
