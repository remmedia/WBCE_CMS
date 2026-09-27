# Hooks nach erfolgreicher Anmeldung

WBCE 2 trennt Passwortprüfung, zusätzliche Authentisierungsfaktoren, Aufbau der
Benutzersitzung und verpflichtende Schritte nach der Anmeldung.

## Reihenfolge

1. Benutzername und Passwort werden geprüft.
2. Anbieter aus `WbceAuthFactorManager` werden ausgeführt, beispielsweise TOTP.
3. `WbceAuthenticationSession::complete()` erzeugt die vollständige Session.
4. Die Action `auth.login.succeeded` wird mit der Benutzer-ID ausgelöst.
5. Die Action `auth.login.completed` wird mit Benutzer-ID und Benutzer-Datensatz
   ausgelöst.
6. Der Filter `auth.login.redirect` erhält das normale Weiterleitungsziel und den
   Benutzer-Datensatz. Sein Rückgabewert wird als nächstes Ziel verwendet.

Damit läuft der Nach-Login-Hook auch dann erst, wenn ein vorgeschalteter zweiter
Faktor erfolgreich abgeschlossen wurde.

## Beobachten einer abgeschlossenen Anmeldung

```php
wbce_add_action('auth.login.completed', function ($userId, array $user) {
    // Protokollieren oder modulspezifischen Zustand aktualisieren.
});
```

Die Action ist nur für Reaktionen gedacht. Sie darf keine Header senden und den
Request nicht beenden.

## Verpflichtenden Folgeschritt einfügen

```php
wbce_add_filter('auth.login.redirect', function ($redirect, array $user) {
    if (!my_module_requires_action($user['user_id'])) {
        return $redirect;
    }

    $_SESSION['MY_MODULE_RETURN_URL'] = $redirect;
    return WB_URL . '/modules/my_module/required-step.php';
});
```

Regeln für Redirect-Module:

- nur lokale Ziele unterhalb von `WB_URL` akzeptieren;
- das ursprüngliche Ziel serverseitig in der Session speichern;
- eine Schleife verhindern, wenn der aktuelle Request bereits der Pflichtseite
  entspricht;
- Abmeldung immer erreichbar lassen;
- Status erst nach erfolgreicher, CSRF-geschützter Verarbeitung löschen;
- bei mehreren Redirect-Modulen den bereits gefilterten Wert respektieren;
- keine Passwörter, Codes oder Tokens in URLs ablegen.

## Benutzer-Lebenszyklus und Admin-Formular

Für Module mit benutzerbezogenen Regeln stehen zusätzlich zur Verfügung:

```php
wbce_add_action('user.created', function ($userId, array $userData, array $input) {});
wbce_add_action('user.updated', function ($userId, array $changes, array $input) {});

wbce_add_filter(
    'admin.user.form.sections',
    function ($html, $userId, $isNew, array $context) { return $html; }
);
```

`admin.user.form.sections` ist eine vertrauenswürdige Backend-Erweiterungsstelle.
Module müssen alle dynamischen Inhalte selbst escapen. Das Kernformular übernimmt
den vorhandenen FTAN-/CSRF-Schutz. Einstellungen gehören weiterhin in eigene
Modultabellen und nicht als neue Spalten in `{TP}users`.

Der Kern setzt in beiden mitgelieferten Backend-Themes ausschließlich den
Platzhalter `{USER_MODULE_SECTIONS}`. Beim Aufbau des Formulars wird dessen Inhalt
so ermittelt:

```php
$html = wbce_apply_filters(
    'admin.user.form.sections',
    '',
    $userId, // 0 beim Anlegen
    $isNew,
    $context
);
```

Ohne registriertes Modul bleibt `$html` leer und es erscheint kein zusätzliches
Feld. Ein Modul registriert den Filter aus seiner `initialize.php`; dadurch wird
die Erweiterung nur geladen, wenn das Modul installiert ist und in der
Add-on-Tabelle die Funktion `initialize` besitzt.

Der Filter dient ausschließlich zur Darstellung. Das Modul speichert seine
Formularwerte getrennt über `user.created` beziehungsweise `user.updated`. Der
Kern kennt weder Feldnamen noch Bedeutung der Moduleinstellung. Mehrere Module
können HTML an den bereits vorhandenen Wert anhängen:

```php
wbce_add_filter('admin.user.form.sections', function ($html, $userId, $isNew) {
    return $html . '<div class="row">...</div>';
});
```

Ein Modul darf den von vorherigen Filtern erzeugten Inhalt nicht verwerfen.
Feldnamen müssen mit dem Modulnamen präfigiert werden, damit mehrere Erweiterungen
nicht kollidieren. Für zustandsändernde Zusatz-Endpunkte ist ein eigener
FTAN-/CSRF-Check erforderlich.

## Referenzmodul

`force_password_change` verwendet diese Hooks wie folgt:

- neue Benutzer werden über `user.created` markiert;
- die Admin-Checkbox wird über `admin.user.form.sections` eingefügt;
- Änderungen werden über `user.updated` gespeichert;
- `auth.login.redirect` führt nach Passwort und gegebenenfalls 2FA zur
  verpflichtenden Passwortänderung;
- andere Seiten und direkte Schreibzugriffe bleiben bis zum Abschluss gesperrt;
- nach erfolgreicher Änderung löst das Modul
  `user.password.forced_change_completed` aus.
