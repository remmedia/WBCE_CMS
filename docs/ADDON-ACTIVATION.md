# Module in WBCE 2 aktivieren und deaktivieren

WBCE 2 ergänzt die Tabelle `addons` um das Feld `active`. Eine Deaktivierung
entfernt weder Dateien noch Datenbanktabellen und ruft weder `uninstall.php` noch
`install.php` auf. Vorhandene Sections und Moduleinstellungen bleiben erhalten.

Deaktivierte Module werden nicht als `preinit`, `initialize` oder `snippet`
geladen. Ihre Frontend-Sections, Backend-Bearbeitungsmasken, Admin-Tools,
Moduldateien und direkten PHP-Endpunkte werden nicht ausgeführt. Nach dem
Aktivieren stehen die bestehenden Daten wieder zur Verfügung.

## API

```php
wbce_addon_is_active('example_module', 'module');
wbce_set_addon_active('example_module', false, 'module');
wbce_set_addon_active('example_module', true, 'module');
```

Die zentrale Änderung ist ausschließlich für Module vorgesehen. Templates und
Admin-Templates können nicht darüber deaktiviert werden, weil aktive Seiten- und
Backend-Templates eine gesonderte Ersatzlogik benötigen.

## Hooks

Der Zustandswechsel stellt vier Actions bereit:

- `addon.beforeDisable($directory, $type)`
- `addon.afterDisable($directory, $type)`
- `addon.beforeEnable($directory, $type)`
- `addon.afterEnable($directory, $type)`

Beispiel:

```php
wbce_add_action('addon.afterDisable', function ($directory, $type) {
    // Externe Caches leeren, ohne Moduldaten zu löschen.
});
```

Die Actions sind optionale Benachrichtigungen. Module müssen sie nicht
implementieren, um deaktiviert werden zu können.

## Kompatibilität des Modul-Stores

Der Modul-Store prüft `WBCE_VERSION` und die Verfügbarkeit von
`wbce_set_addon_active()`. Die Schaltflächen erscheinen nur unter WBCE 2. Unter
WBCE 1.x bleibt derselbe Client für Installation, Updates und Deinstallation
funktionsfähig, bietet aber keine Aktivierungsfunktion an.
