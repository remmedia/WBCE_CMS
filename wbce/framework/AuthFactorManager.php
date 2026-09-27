<?php

final class WbceAuthFactorManager
{
    private const SESSION_KEY = 'WBCE_AUTH_FACTOR_CHALLENGE';
    private const LIFETIME = 300;
    private static array $providers = [];

    public static function register(WbceAuthFactorProviderInterface $provider): void
    {
        $id = $provider->getId();
        if (!preg_match('/^[a-z][a-z0-9_.-]{1,63}$/', $id)) {
            throw new InvalidArgumentException('Invalid authentication factor id');
        }
        self::$providers[$id] = $provider;
        wbce_do_action('auth.factor.registered', $id, $provider);
    }

    public static function provider(string $id): ?WbceAuthFactorProviderInterface
    {
        return self::$providers[$id] ?? null;
    }

    public static function requiredProviderIds(array $user): array
    {
        $required = [];
        foreach (self::$providers as $id => $provider) {
            if ($provider->isRequired($user)) $required[] = $id;
        }
        $required = wbce_apply_filters('auth.factors.required', $required, $user);
        return is_array($required) ? array_values(array_filter($required, static fn($id): bool => isset(self::$providers[$id]))) : [];
    }

    public static function begin(int $userId, array $providerIds, string $returnUrl): void
    {
        session_regenerate_id(true);
        $safeReturnUrl = wbce_safe_redirect_url($returnUrl, ADMIN_URL . '/start/index.php');
        $_SESSION[self::SESSION_KEY] = [
            'user_id' => $userId,
            'providers' => array_values($providerIds),
            'position' => 0,
            'attempts' => 0,
            'expires' => time() + self::LIFETIME,
            'nonce' => bin2hex(random_bytes(32)),
            'return_url' => $safeReturnUrl,
            'cancel_url' => str_starts_with($safeReturnUrl, ADMIN_URL . '/')
                ? ADMIN_URL . '/login/index.php'
                : WB_URL . '/account/login.php',
        ];
        wbce_do_action('auth.challenge.started', $userId, $providerIds);
    }

    public static function current(): ?array
    {
        $challenge = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($challenge) || (int)($challenge['expires'] ?? 0) < time()) {
            self::cancel();
            return null;
        }
        return $challenge;
    }

    public static function currentProvider(): ?WbceAuthFactorProviderInterface
    {
        $challenge = self::current();
        if (!$challenge) return null;
        return self::provider((string)($challenge['providers'][$challenge['position']] ?? ''));
    }

    public static function advance(): bool
    {
        $_SESSION[self::SESSION_KEY]['position']++;
        return $_SESSION[self::SESSION_KEY]['position'] >= count($_SESSION[self::SESSION_KEY]['providers']);
    }

    public static function recordFailure(int $maximum = 10): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) return true;
        $_SESSION[self::SESSION_KEY]['attempts']++;
        if ($_SESSION[self::SESSION_KEY]['attempts'] >= $maximum) {
            self::cancel();
            return true;
        }
        return false;
    }

    public static function consume(): ?array
    {
        $challenge = self::current();
        unset($_SESSION[self::SESSION_KEY]);
        return $challenge;
    }

    public static function cancel(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
