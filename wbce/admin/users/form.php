<?php
/**
 * WBCE CMS — htmx endpoint: User form fragment
 *
 * GET:  ?action=add / ?user_id={IDKEY} → load form
 * POST: save form data → return updated form fragment
 *
 * @since 1.7.0
 */

require '../../config.php';

// Non-htmx fallback
if (empty($_SERVER['HTTP_HX_REQUEST'])) {
    $redirect = ADMIN_URL . '/users/';
    if (isset($_GET['user_id'])) {
        $redirect .= 'index.php?user_id=' . urlencode($_GET['user_id']) . '&modify=1';
    }
    header('Location: ' . $redirect);
    exit;
}

$users   = new UserManager();
$alerts = new Alerts(useSession: false);

require_once ADMIN_PATH . '/interface/timezones.php';
require_once ADMIN_PATH . '/interface/languages.php';

$isAdd     = false;
$userId    = 0;
$errorMsgs = [];
$saveData  = null;
$wasAdded  = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // =================================================================
    //  POST: Save
    // =================================================================
    $userId = intval($_POST['user_id'] ?? 0);
    $isAdd  = ($userId === 0);
    $previousUser = $isAdd ? null : $users->getUser($userId);

    $admin = new Admin('Access', $isAdd ? 'users_add' : 'users_modify', false);

    if (!$isAdd && $userId < 2) {
        http_response_code(422);
        $alerts->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
        echo $alerts->renderFragment();
        exit;
    }

    if (!$admin->checkFTAN()) {
        $alerts->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
        echo $alerts->renderFragment();
        exit;
    }

    // --- Validate ---
    $username    = mb_strtolower(trim((string)($admin->get_post('username') ?? '')), 'UTF-8');
    $errorMsgs   = array_merge($errorMsgs, $users->validateUsername($username, $userId));

    $displayName = trim($admin->get_post('display_name') ?? '');
    if ($displayName === '') {
        $errorMsgs[] = $MESSAGE['GENERIC_FILL_IN_ALL'] ?? 'Display name required';
    }

    $email     = trim($admin->get_post('email') ?? '');
    $errorMsgs = array_merge($errorMsgs, $users->validateEmail($admin, $email, $userId));

    $groupsId = '';
    if (empty($_POST['groups'])) {
        $errorMsgs[] = $MESSAGE['USERS_NO_GROUP'] ?? 'Please select at least one group';
    } else {
        $groupsId = implode(',', array_map('intval', (array) $_POST['groups']));
    }

    $firstGroup = !empty($_POST['groups']) ? intval(reset($_POST['groups'])) : 0;

    $saveData = [
        'group_id'           => $firstGroup,
        'groups_id'          => $groupsId,
        'active'             => isset($_POST['active']) ? 1 : 0,
        'username'           => ($username !== 'admin') ? $username : '',
        'display_name'       => $displayName,
        'email'              => $email,
        'home_folder'        => trim($admin->get_post('home_folder') ?? ''),
        'timezone'           => in_array((string)($admin->get_post('timezone') ?? ''), DateTimeZone::listIdentifiers(DateTimeZone::ALL), true)
            ? (string)$admin->get_post('timezone') : '',
        'language'           => $admin->get_post('language') ?? DEFAULT_LANGUAGE,
        'signup_timestamp'   => time(),
        'signup_confirmcode' => 'by admin uid: ' . $admin->get_user_id(),
    ];

    $saveData = wbce_apply_array_filters($isAdd ? 'user.beforeCreate' : 'user.beforeUpdate', $saveData, $userId, $_POST);

    // Password
    if ($isAdd || (isset($_POST['change_pswd']) && $_POST['change_pswd'] == 1)) {
        $pwResult = $users->validatePassword(
            $admin,
            $admin->get_post('password') ?? '',
            $admin->get_post('password2') ?? ''
        );
        if (is_array($pwResult)) {
            $errorMsgs = array_merge($errorMsgs, $pwResult);
        } else {
            $saveData['password'] = $pwResult;
        }
    }

    // --- Save ---
    if (empty($errorMsgs)) {
        if ($isAdd) {
            $userId = $users->createUser($saveData);
            if ($userId > 0) {
                $wasAdded = true;
                wbce_do_action('user.created', $userId, $saveData, $_POST);
            }
        } else {
            $users->updateUser($userId, $saveData);
            wbce_do_action('user.updated', $userId, $saveData, $_POST);
            if ($previousUser !== null && array_key_exists('active', $saveData)
                && (int)$previousUser['active'] !== (int)$saveData['active']) {
                wbce_do_action((int)$saveData['active'] === 1 ? 'user.activated' : 'user.deactivated', $userId, $saveData);
            }
            if ($previousUser !== null && isset($saveData['email'])
                && (string)$previousUser['email'] !== (string)$saveData['email']) {
                wbce_do_action('user.email.changed', $userId, $previousUser['email'], $saveData['email']);
            }
            if (isset($saveData['password'])) wbce_do_action('user.password.changed', $userId, 'admin');
        }

        if ($database->hasError()) {
            $errorMsgs[] = $database->getError();
        }
    }

    $saveData['user_id'] = $userId;
    $saveData['groups']  = array_map('intval', explode(',', $groupsId));

} else {
    // =================================================================
    //  GET: Load form
    // =================================================================
    $isAdd = (($_GET['action'] ?? '') === 'add');

    if ($isAdd) {
        $admin = new Admin('Access', 'users_add', false);
    } else {
        $admin  = new Admin('Access', 'users_modify', false);
        $userId = intval($admin->checkIDKEY('user_id', 0, 'GET', true));

        if ($userId < 2) {
            http_response_code(422);
            $alerts->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
            echo $alerts->renderFragment();
            exit;
        }
    }
}


// =====================================================================
//  Build form data
// =====================================================================
if ($wasAdded && $userId > 0) {
    $isAdd    = false;
    $userData = $users->getUser($userId) ?? $saveData;
} elseif ($saveData !== null) {
    $userData = $saveData;
} elseif (!$isAdd && $userId > 0) {
    $userData = $users->getUser($userId) ?? [];
} else {
    $userData = [
        'user_id'      => 0,
        'username'     => '',
        'display_name' => '',
        'email'        => '',
        'active'       => 1,
        'timezone'     => '',
        'language'     => DEFAULT_LANGUAGE,
        'home_folder'  => '',
    ];
}


// =====================================================================
//  HX-Triggers + Messages
// =====================================================================

// Row update triggers FIRST (toast() will merge with these)
if ($wasAdded && $userId > 0 && empty($errorMsgs)) {
    $newIDKEY = $admin->getIDKEY($userId);
    header('HX-Trigger: {"userAdded": {"idkey": "' . $newIDKEY . '", "userId": ' . $userId . '}}');
} elseif ($saveData !== null && empty($errorMsgs) && !$wasAdded) {
    $savedIDKEY = $admin->getIDKEY($userId);
    header('HX-Trigger: {"userSaved": {"idkey": "' . $savedIDKEY . '", "userId": ' . $userId . '}}');
}

// Messages
if (!empty($errorMsgs)) {
    foreach ($errorMsgs as $msg) {
        $alerts->toast(h($msg), 'error');
    }
} elseif ($saveData !== null) {
    // Success → toast (merges into existing HX-Trigger)
    $label = $wasAdded ? '{MESSAGE:USERS_ADDED}' : '{MESSAGE:USERS_SAVED}';
    $alerts->toast($label, 'success');
}


// =====================================================================
//  Render
// =====================================================================
$doModify   = !$isAdd;
$changePswd = (isset($_POST['change_pswd']) && $_POST['change_pswd'] == 1);

$groupsForForm = $users->getGroupsForForm(
    $userId,
    ($wasAdded) ? [] : ($saveData['groups'] ?? []),
    in_array(1, $admin->get_groups_id())
);

$homeFolders = $users->getHomeFolders(
    $userId,
    $saveData['home_folder'] ?? ''
);

$toTwig = [
    'MESSAGE_BOX'        => $alerts->renderFragment(),
    'FTAN'               => $admin->getFTAN(),
    'ACTION_URL'         => ADMIN_URL . '/users/form.php',
    'do_modify_user'     => $doModify,
    'use_home_folders'   => HOME_FOLDERS,
    'user'               => $userData,
    'TIME_ZONES'         => getTimeZonesArray($TIMEZONES, true),
    'LANGUAGES'          => getLanguagesArray(),
    'groups'             => $groupsForForm,
    'home_folders'       => $homeFolders,
    'change_pswd'        => $changePswd,
    'INPUT_NEW_PASSWORD' => $admin->passwordField('password', '', 0, ($saveData !== null && !empty($errorMsgs)) ? ($_POST['password'] ?? '') : ''),
    'is_htmx_fragment'   => true,
];

$formContext = wbce_apply_array_filters('admin.user.form.context', [
    'user_id' => (int)$userId,
    'is_new' => (bool)$isAdd,
    'user' => $userData,
], $userData);
$toTwig['MODULE_SECTIONS'] = (string)wbce_apply_filters(
    'admin.user.form.sections', '', (int)$userId, (bool)$isAdd, $formContext
);

$admin->getThemeFile('access_users_form.twig', $toTwig);
