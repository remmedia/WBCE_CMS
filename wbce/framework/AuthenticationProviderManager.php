<?php
/** Extensible registry and policy gate for primary WBCE authentication. */
final class WbceAuthenticationProviderManager
{
    private static array $providers = [];
    public static function register(WbceAuthenticationProviderInterface $provider): void
    {
        $id = (string)$provider->getId();
        if (!preg_match('/^[a-z][a-z0-9_.-]{1,63}$/', $id)) throw new InvalidArgumentException('Invalid authentication provider id');
        self::$providers[$id] = $provider;
        wbce_do_action('auth.provider.registered', $id, $provider);
    }
    public static function providers(): array
    {
        $items = ['wbce' => ['id'=>'wbce','name'=>'WBCE', 'protected'=>true]];
        foreach (self::$providers as $id=>$provider) $items[$id] = ['id'=>$id,'name'=>(string)$provider->getName(),'protected'=>false];
        $items = wbce_apply_filters('auth.login.providers', $items);
        return is_array($items) ? $items : ['wbce'=>['id'=>'wbce','name'=>'WBCE','protected'=>true]];
    }
    /**
     * Providers which may be selected before a user is known.  A provider must
     * be registered, enabled by the global policy and ready in its own config.
     */
    public static function loginProviders(): array
    {
        $policy = self::policy();
        $available = [];
        foreach (self::providers() as $id => $definition) {
            if ($id !== 'wbce' && !isset(self::$providers[$id])) continue;
            $rule = (array)($policy['providers'][$id] ?? []);
            if ($id !== 'wbce' && (empty($rule['enabled']) || (string)($rule['scope'] ?? 'disabled') === 'disabled')) continue;
            if ($id !== 'wbce' && !self::providerIsReadyForLogin($id)) continue;
            $available[$id] = $definition;
        }
        return $available ?: ['wbce' => ['id'=>'wbce', 'name'=>'WBCE', 'protected'=>true]];
    }

    /** A provider may expose this method when it has more precise readiness checks. */
    private static function providerIsReadyForLogin(string $id): bool
    {
        $provider = self::$providers[$id] ?? null;
        if (!$provider) return false;
        try {
            if (method_exists($provider, 'isAvailableForLogin')) return (bool)$provider->isAvailableForLogin();
        } catch (Throwable $error) {
            error_log('WBCE authentication provider '.$id.' readiness check failed: '.$error->getMessage());
            return false;
        }
        $settings = Settings::get($id.'_settings', []);
        return is_array($settings) && !empty($settings['enabled']) && !empty($settings['configured']);
    }

    /**
     * Declarative presentation profile for the common login form. Providers can
     * replace it with loginFieldProfile() without changing the CMS login page.
     */
    public static function loginFieldProfile(string $id): array
    {
        $profile = [
            'username' => true,
            'password' => true,
            'username_label' => function_exists('L_') ? L_('TEXT:USERNAME||Username') : 'Username',
            'password_label' => function_exists('L_') ? L_('TEXT:PASSWORD||Password') : 'Password',
            'username_type' => 'text',
            'submit_label' => function_exists('L_') ? L_('TEXT:LOGIN||Login') : 'Login',
        ];
        $emailLabel = function_exists('L_') ? L_('TEXT:EMAIL||E-mail address') : 'E-mail address';
        $builtInProfiles = [
            'auth_imap'       => ['username_label' => $emailLabel, 'username_type' => 'email'],
            'auth_ldap'       => ['username_label' => function_exists('L_') ? L_('TEXT:USERNAME||Username').' / '.$emailLabel : 'Username / E-mail address'],
            'auth_radius'     => ['username_label' => function_exists('L_') ? L_('TEXT:USERNAME||Username').' (RADIUS)' : 'Username (RADIUS)'],
            'auth_magic_link' => ['username_label' => $emailLabel, 'username_type' => 'email', 'password' => false, 'submit_label' => function_exists('L_') ? L_('TEXT:SEND||Send') : 'Send'],
            'auth_webauthn'   => ['password' => false, 'submit_label' => function_exists('L_') ? L_('TEXT:LOGIN||Login') : 'Login'],
            'auth_oidc'       => ['username_label' => $emailLabel, 'username_type' => 'email'],
            'auth_saml'       => ['username_label' => $emailLabel, 'username_type' => 'email'],
            'auth_qr_login'   => ['username_label' => function_exists('L_') ? L_('TEXT:USERNAME||Username').' (QR)' : 'Username (QR)'],
        ];
        if (isset($builtInProfiles[$id])) $profile = array_replace($profile, $builtInProfiles[$id]);
        $provider = self::$providers[$id] ?? null;
        if ($provider && method_exists($provider, 'loginFieldProfile')) {
            try {
                $custom = $provider->loginFieldProfile();
                if (is_array($custom)) $profile = array_replace($profile, $custom);
            } catch (Throwable $error) {
                error_log('WBCE authentication provider '.$id.' login form profile failed: '.$error->getMessage());
            }
        }
        return $profile;
    }

    public static function policy(): array
    {
        $policy = Settings::get('auth_login_providers', []);
        if (!is_array($policy)) $policy=[];
        $policy += ['providers'=>['wbce'=>['enabled'=>true,'scope'=>'all']], 'users'=>[]];
        $policy['providers']['wbce'] = array_merge(['enabled'=>true,'scope'=>'all'], (array)($policy['providers']['wbce'] ?? []));
        return $policy;
    }
    public static function allowed(array $user): array
    {
        $policy=self::policy(); $available=[]; $isAdmin=in_array(1, array_map('intval', explode(',', (string)($user['groups_id'] ?? ''))), true);
        foreach (self::providers() as $id=>$definition) {
            $rule=(array)($policy['providers'][$id] ?? []); $enabled=!empty($rule['enabled']); $scope=(string)($rule['scope'] ?? 'disabled');
            if ($id==='wbce' && $isAdmin) { $available[$id]=$definition; continue; }
            if (!$enabled || $scope==='disabled') continue;
            if ($scope==='all' || ($scope==='users' && in_array($id, (array)($policy['users'][(int)($user['user_id'] ?? 0)] ?? []), true))) $available[$id]=$definition;
        }
        return $available;
    }
    /** Provision only after the provider has verified external credentials. */
    public static function provision(string $id, array $credentials, $database): ?array
    {
        $rule=(array)(self::policy()['providers'][$id] ?? []);
        if ($id === 'wbce' || empty($rule['enabled']) || (string)($rule['scope'] ?? 'disabled') !== 'all') return null;
        $provider=self::$providers[$id] ?? null;
        if (!$provider || !method_exists($provider, 'provisionUser')) return null;
        try { $user=$provider->provisionUser($credentials, $database); } catch (Throwable $error) { error_log('WBCE authentication provisioning '.$id.' failed: '.$error->getMessage()); return null; }
        return is_array($user) && !empty($user['user_id']) ? $user : null;
    }
    public static function verify(string $id, array $user, array $credentials, callable $wbceVerifier): bool
    {
        if (!isset(self::allowed($user)[$id])) return false;
        if ($id==='wbce') return (bool)$wbceVerifier();
        $provider=self::$providers[$id] ?? null;
        if (!$provider) return false;
        try { $valid=(bool)$provider->authenticate($user, $credentials); } catch (Throwable $error) { error_log('WBCE authentication provider '.$id.' failed: '.$error->getMessage()); $valid=false; }
        wbce_do_action($valid ? 'auth.provider.verified' : 'auth.provider.failed', $id, (int)$user['user_id']);
        return $valid;
    }
    /** Render the common, translated personal area; providers append their own controls by hook. */
    public static function renderUserSection(int $userId): string
    {
        global $database;
        $user = is_object($database) ? $database->fetchRow('SELECT * FROM `{TP}users` WHERE `user_id` = ?', [$userId]) : null;
        if (!is_array($user)) return '';
        $allowed = self::allowed($user); $items = [];
        foreach ($allowed as $id => $provider) $items[] = '<li>'.htmlspecialchars((string)($provider['name'] ?? $id), ENT_QUOTES, 'UTF-8').'</li>';
        $extra = wbce_apply_array_filters('auth.login.user_sections', [], $user, $allowed);
        $heading = function_exists('L_') ? L_('TEXT:LOGIN_METHOD||Login methods') : 'Login methods';
        $intro = function_exists('L_') ? L_('TEXT:LOGIN_METHODS_INFO||Manage the login methods available for your account.') : 'Manage the login methods available for your account.';
        return '<section class="wbce-auth-user-section wbce-admin-tool-shell"><header class="wbce-admin-hero"><i class="fa fa-key" aria-hidden="true"></i><div class="wbce-admin-hero__content"><h2>'.htmlspecialchars($heading, ENT_QUOTES, 'UTF-8').'</h2><p>'.htmlspecialchars($intro, ENT_QUOTES, 'UTF-8').'</p></div></header><div class="content-box"><ul>'.implode('', $items).'</ul>'.implode('', array_map('strval', is_array($extra) ? $extra : [])).'</div></section>';
    }

    public static function setPolicy(array $policy): bool
    {
        $configured=(array)($policy['providers'] ?? []); $active=0;
        foreach ($configured as $id=>$rule) if (!empty($rule['enabled']) && (string)($rule['scope'] ?? 'disabled') !== 'disabled') $active++;
        if ($active<1) throw new InvalidArgumentException('At least one login provider must remain enabled.');
        $policy['providers']=$configured; $policy['users']=(array)($policy['users'] ?? []);
        return Settings::set('auth_login_providers', $policy) === false;
    }
}
