# Primäre Login-Provider

Provider werden im `initialize.php` eines Moduls registriert und authentifizieren vor der unabhängigen Zwei-Faktor-Prüfung.

```php
final class ExampleProvider implements WbceAuthenticationProviderInterface {
  public function getId() { return 'example'; }
  public function getName() { return 'Example Login'; }
  public function authenticate(array $user, array $credentials) {
    // verify the external credential for this already existing, active WBCE user
    return false;
  }
}
WbceAuthenticationProviderManager::register(new ExampleProvider());
```

Use `auth.login.providers` to alter presentation metadata and `auth.provider.verified` / `auth.provider.failed` for audit logging. The manager applies global provider rules (`all`, `users`, `disabled`) and per-user grants stored in `auth_login_providers`. The built-in `wbce` provider is protected: it cannot be uninstalled, and remains available to administrators as recovery access.

## Mail-based providers

Magic-link and email-code providers must use the CMS `Mailer` class (or the configured `mailer.providers` transport registry). They must not invoke `mail()` or include legacy PHPMailer files directly. Send a cryptographically random, hashed, single-use token with a short expiry; the provider itself owns token storage and revocation.

## Personal provider settings

A provider can add its user-specific setup to the central preferences page:

```php
wbce_add_filter('auth.login.user_sections', static function (array $sections, array $user, array $allowed) {
    if (!isset($allowed['example'])) return $sections;
    $sections[] = '<!-- provider-specific, asynchronously saved UI -->';
    return $sections;
}, 10);
```

The core owns access policy. Providers must use FTAN-protected asynchronous endpoints for their personal settings and may never grant themselves to a user.
