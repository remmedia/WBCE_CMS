# Authentisierungsfaktoren als WBCE-2-Module

WBCE 2 schließt die Anmeldung erst ab, wenn alle für einen Benutzer erforderlichen
Faktoren erfolgreich geprüft wurden. Der Kern kennt dabei weder TOTP noch andere
konkrete Verfahren.

Ein Modul mit zusätzlichem Faktor:

1. führt in `info.php` die Funktion `initialize` auf,
2. implementiert `WbceAuthFactorProviderInterface`,
3. registriert den Anbieter in `initialize.php` mit
   `WbceAuthFactorManager::register($provider)`,
4. speichert seine Einstellungen in eigenen Tabellen,
5. hängt seine Benutzeroberfläche optional über den Filter
   `user.preferences.sections` in die Profileinstellungen ein.

```php
final class ExampleFactor implements WbceAuthFactorProviderInterface
{
    public function getId() { return 'example'; }
    public function isRequired(array $user) { return true; }
    public function renderChallenge(array $user, $error = '') { return '<form id="wbce-auth-factor" method="post">...</form>'; }
    public function verify(array $user, array $input) { return true; }
}

WbceAuthFactorManager::register(new ExampleFactor());
```

## Sicherheitsvertrag

- `isRequired()` darf keine Anmeldung abschließen oder Sessionrechte setzen.
- `renderChallenge()` muss ein Formular mit der ID `wbce-auth-factor` liefern
  und sämtliche dynamischen Werte HTML-escapen. Der Kern ergänzt daran seinen
  kurzlebigen Challenge-Nonce.
- `verify()` erhält nur den Benutzer-Datensatz und die Formulareingabe. Es gibt
  ausschließlich `true` oder `false` zurück.
- Geheimnisse gehören in modulspezifische, verschlüsselte Speicherung und nie in
  die WBCE-Benutzertabelle, Session, URL oder Logs.
- Jeder Anbieter muss Fehlversuche persistent begrenzen.
- Nach fünf Minuten verwirft der Kern eine nicht abgeschlossene Voranmeldung.
- Erst `WbceAuthenticationSession::complete()` erzeugt die vollständige Session;
  Faktor-Module rufen diese Methode nicht selbst auf.

Mehrere Anbieter sind möglich. Der Manager prüft sie in Registrierungsreihenfolge.
Der Filter `auth.factors.required` kann die Liste ergänzen oder nach zentralen
Regeln verändern.

