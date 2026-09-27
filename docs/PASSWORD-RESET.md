# Zweistufiges Zurücksetzen von Passwörtern

WBCE 2 setzt bei einer „Passwort vergessen“-Anfrage kein Passwort mehr. Backend-
und Frontend-Formular verwenden denselben zweistufigen Ablauf.

## Ablauf

1. Der Besucher gibt seine E-Mail-Adresse ein und löst das Captcha.
2. WBCE zeigt unabhängig vom Vorhandensein des Kontos dieselbe Bestätigung an.
3. Für ein aktives, bestätigtes Konto wird ein zufälliger Einmal-Link erzeugt.
4. In der Datenbank stehen nur Selektor und SHA-256-Hash des geheimen Token-Teils.
5. Der per E-Mail versandte Link ist eine Stunde gültig.
6. Auf `/account/reset-password.php` wählt der Benutzer selbst ein neues Passwort.
7. Erst nach erfolgreicher Passwortprüfung wird das bisherige Passwort ersetzt.
8. Der verwendete Link und alle weiteren offenen Links dieses Benutzers werden
   ungültig.
9. Alle noch bestehenden Anmeldesitzungen des Benutzers werden widerrufen.

Bleibt die E-Mail aus oder wird der Link nicht benutzt, gilt das vorhandene
Passwort unverändert weiter.

## Sicherheitsmerkmale

- 256 Bit zufälliges Token plus separater Selektor
- nur gehashter geheimer Tokenteil in `{TP}password_resets`
- Einmalverwendung und feste Ablaufzeit
- höchstens eine neue gültige Anfrage je Konto innerhalb von fünf Minuten
- neue Anfrage entwertet ältere offene Links
- erfolgreicher Reset beendet alle bestehenden Sitzungen des Kontos
- identische öffentliche Antwort bei bekannter und unbekannter E-Mail-Adresse
- kein Konto-, Token- oder Passwortwert in Logs
- Passwortregeln des WBCE-Kerns gelten unverändert
- altes und neues Passwort müssen unterschiedlich sein
- zusätzliches formulargebundenes Session-Nonce
- `Referrer-Policy: no-referrer` und restriktive Content Security Policy auf der
  Reset-Seite

## Erweiterungspunkt

Nach erfolgreichem Zurücksetzen führt der Kern aus:

```php
wbce_do_action('user.password.reset_completed', $userId);
```

Module können damit eigene, benutzerbezogene Zustände aktualisieren. Der Hook
läuft erst, nachdem das neue Passwort gespeichert und alle offenen Reset-Tokens
verbraucht wurden. Module dürfen das neue Passwort oder den Reset-Token nicht
anfordern; beide Werte werden absichtlich nicht als Hook-Argumente veröffentlicht.

Das Modul `force_password_change` nutzt diesen Hook, um eine noch vorhandene
Pflicht zur Passwortänderung zu entfernen: Der Benutzer hat beim Reset bereits
selbst ein neues Passwort gewählt.

## Datenhaltung

Die Tabelle `{TP}password_resets` wird bei Neuinstallationen mit dem Kern angelegt.
Bei aktualisierten Installationen stellt `WbcePasswordResetService` die Tabelle
beim ersten Aufruf kompatibel bereit. Eine spätere zentrale Upgrade-Migration kann
diese Kompatibilitätshilfe ersetzen.
