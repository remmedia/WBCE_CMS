# WBCE 1.7 Hook-Schnittstelle

WBCE 1.7 stellt eine zentrale, abwärtskompatible Hook-Schnittstelle bereit. Bestehende Module müssen nicht angepasst werden. Neue Module registrieren ihre Hooks in ihrer `initialize.php`; WBCE lädt diese Datei für installierte Module wie bisher.

## API

```php
$handle = wbce_add_action('page.updated', function ($pageId, array $page) {
    // Folgeaktion ausführen
}, 10);

wbce_add_filter('page.url', function ($url, $pageId, $link) {
    return $url;
});

wbce_remove_hook($handle);
wbce_has_hook('page.updated');
```

Kleinere Prioritätswerte werden zuerst ausgeführt; bei gleicher Priorität gilt die Registrierungsreihenfolge. Actions verändern keinen Rückgabewert. Filter müssen den – gegebenenfalls veränderten – ersten Parameter zurückgeben. Array-Verträge müssen immer ein Array zurückgeben, sonst löst WBCE eine `UnexpectedValueException` aus.

Abbruch-Hooks sind Filter mit dieser Form:

```php
wbce_add_filter('page.beforeDelete', function ($allowed, array $context) {
    return $allowed && my_module_may_delete($context['page_id']);
});
```

Ein Abbruch-Hook stoppt den Vorgang ausschließlich bei `false`. Passwörter, Sitzungsgeheimnisse und SMTP-Zugangsdaten werden nie als Hook-Daten übergeben.

## Seiten und Abschnitte

| Hook | Art | Daten / Zweck |
|---|---|---|
| `page.beforeCreate` | Filter (Array) | Seitendaten vor dem Einfügen |
| `page.created` | Action | Seiten-ID, Seitendaten |
| `page.beforeUpdate` | Filter (Array) | Seitendaten vor dem Speichern |
| `page.updated` | Action | Seiten-ID, neue Seitendaten |
| `page.beforeMove` | Abbruch | ID, alter/neuer Elternknoten und Position |
| `page.moved` | Action | ID, alte/neue Position und Elternknoten |
| `page.beforeDelete` | Abbruch | Kontext der zu löschenden Seite |
| `page.deleted` | Action | ID und bisherige Seitendaten |
| `page.url` | Filter | erzeugte Seiten-URL, ID, interner Link |
| `section.beforeCreate` | Filter (Array) | neue Abschnittsdaten |
| `section.created` | Action | Abschnitts-ID und Daten |
| `section.beforeUpdate` | Action | Abschnitts-ID und Eingabedaten |
| `section.updated` | Action | Abschnitts-ID und gespeicherte Daten |
| `section.beforeDelete` | Abbruch | Abschnittskontext |
| `section.deleted` | Action | Abschnittskontext |

## Anmeldung, Sitzung und Benutzer

| Hook | Art | Daten / Zweck |
|---|---|---|
| `auth.login.attempted` | Action | Benutzername und Login-Kontext, niemals das Passwort |
| `auth.login.failed` | Action | Benutzername und Login-Kontext |
| `auth.login.succeeded` | Action | Benutzer-ID nach Passwortprüfung |
| `auth.factors.required` | Filter (Array) | erforderliche zusätzliche Anmeldefaktoren |
| `auth.challenge.started`, `auth.challenge.passed`, `auth.challenge.failed` | Action | Status einer zusätzlichen Prüfung |
| `auth.login.completed` | Action | vollständig abgeschlossene Anmeldung |
| `auth.login.redirect` | Filter | Ziel nach vollständiger Anmeldung |
| `auth.session.created`, `auth.session.destroyed` | Action | Sitzungslebenszyklus ohne Geheimnisse |
| `user.sessions.revoked` | Action | Benutzer-ID und optional beibehaltene Sitzungs-ID nach zentralem Sitzungswiderruf |
| `auth.logout.before`, `auth.logout.completed` | Action | Benutzer-ID beim Abmelden |
| `permissions.resolved` | Filter (Array) | aufgelöste System-, Modul- und Template-Rechte |
| `permissions.check` | Filter | Ergebnis einer einzelnen Rechteprüfung |
| `user.beforeCreate`, `user.beforeUpdate` | Filter (Array) | validierte Benutzerdaten vor Speicherung |
| `user.beforeDelete` | Abbruch | Benutzerkontext |
| `user.created`, `user.updated`, `user.deleted` | Action | Lebenszyklus eines Benutzers |
| `user.activated`, `user.deactivated` | Action | Statuswechsel |
| `user.email.changed`, `user.password.changed` | Action | relevante Sicherheitsänderung; kein Klartextpasswort |
| `user.password.reset_completed`, `user.password.forced_change_completed` | Action | Abschluss der jeweiligen Passwortänderung |
| `admin.user.form.context` | Filter (Array) | Benutzer-ID, Neu/Bearbeiten-Status und Benutzerdaten vor Aufbau des Adminformulars |
| `admin.user.form.sections` | Filter (HTML) | dynamische Modulbereiche im Sicherheitsabschnitt des Benutzerformulars |

`admin.user.form.sections` erhält nach dem bisherigen HTML-Wert die Benutzer-ID,
den Neu-Status und den gefilterten Formularkontext. Der Bereich wird nur von
installierten und aktiven Modulen erzeugt. Für die Speicherung stehen
`user.beforeCreate`/`user.created` sowie `user.beforeUpdate`/`user.updated` zur
Verfügung; die beiden abschließenden Actions erhalten außerdem die geprüften
Formulareingaben.

## Frontend und Routing

| Hook | Art | Daten / Zweck |
|---|---|---|
| `routing.request.path` | Filter | normalisierter Anfragepfad |
| `routing.routes` | Filter (Array) | zusätzliche Routen als `pattern`/`callback` |
| `routing.resolved` | Action | von einem Modul aufgelöste Route |
| `routing.page_id` | Filter | ermittelte Seiten-ID |
| `routing.not_found` | Filter | alternative Ausgabe für nicht gefundene Seiten |
| `routing.redirect` | Filter | Umleitungsziel plus Status und Quelle |

Weiterleitungsziele aus `auth.login.redirect` werden nach allen Filtern durch
`wbce_safe_redirect_url()` auf die konfigurierte WBCE-Installation begrenzt.
Module können daher keine Anmeldung auf eine fremde Domain umleiten.
| `frontend.request.started` | Action | Start der Frontend-Anfrage |
| `frontend.page.resolved` | Action | geladene Seite |
| `frontend.section.output` | Filter | HTML eines einzelnen Abschnitts |
| `frontend.content.beforeRender` | Action | unmittelbar vor der Seitenausgabe |
| `frontend.page.output` | Filter | vollständige HTML-Ausgabe |
| `frontend.response.headers` | Filter (Array) | zusätzliche Antwort-Header |
| `frontend.response.completed` | Action | abgeschlossene Frontend-Antwort |

Zusätzliche Routen verwenden reguläre Ausdrücke. Ein Callback erhält Pfad, Treffer und Frontend-Objekt. Gibt er einen anderen Wert als `false` zurück, gilt die Anfrage als behandelt.

## Backend-Oberfläche

| Hook | Art | Daten / Zweck |
|---|---|---|
| `admin.navigation.items` | Filter (Array) | Einträge der Hauptnavigation |
| `admin.assets` | Filter (Array) | CSS/JS-Assets mit `type`, `url`, optional `id`, `position` |
| `admin.head`, `admin.footer` | Action | ergänzende Backend-Ausgabe |
| `admin.dashboard.widgets` | Filter (Array) | Dashboard-Bausteine als HTML-String oder Array mit Feld `html` |
| `admin.user.form.sections` | Filter (String) | dynamische Bereiche im Benutzerformular |
| `user.preferences.sections` | Filter (Array) | dynamische Bereiche in persönlichen Einstellungen |
| `admin.page.form.sections` | Filter (String) | zusätzliche Bereiche der Seiteneinstellungen |
| `admin.settings.sections` | Filter (String) | zusätzliche Bereiche der Systemeinstellungen |

Module müssen für schreibende eigene Formulare eine eigene FTAN-Prüfung und Rechteprüfung durchführen.

## Add-ons, Medien, Mail und Suche

| Hook | Art | Daten / Zweck |
|---|---|---|
| `addon.install.validation` | Filter (Array) | Pakettyp, Verzeichnis, Version, Aktion und temporärer Archivpfad; `valid=false` lehnt ab |
| `addon.beforeInstall`, `addon.beforeUpgrade`, `addon.beforeUninstall` | Abbruch | Add-on-Kontext vor Dateisystemänderungen |
| `addon.installed`, `addon.upgraded`, `addon.uninstalled` | Action | erfolgreicher Abschluss |
| `addon.beforeEnable`, `addon.afterEnable`, `addon.beforeDisable`, `addon.afterDisable` | Action | Aktivierungsstatus eines Add-ons |
| `template.activated`, `adminTemplate.activated` | Action | Änderung des Standard-Templates |
| `media.upload.validation` | Filter (Array) | temporäre Datei, Ziel und Name; `valid=false` lehnt ab |
| `media.beforeUpload`, `media.beforeDelete`, `media.beforeRename` | Abbruch | elFinder-Befehl und Argumente |
| `media.uploaded`, `media.deleted`, `media.renamed` | Action | Ergebnis und Befehlsargumente |
| `mail.message` | Filter (Array) | Betreff, HTML-Text und Alternativtext |
| `mail.beforeSend`, `mail.sent`, `mail.failed` | Action | Versandstatus ohne Transport-Passwort |
| `search.query` | Filter | bereinigter Suchtext |
| `search.sources` | Filter (Array) | Reihenfolge/Umfang der durchsuchten Module |
| `search.result` | Filter (Array) | einzelnes Ergebnis einschließlich `html`; `html=false` blendet es aus |
| `search.results` | Filter (Array) | Liste gefundener Seiten-IDs vor der Leerprüfung |
| `search.completed` | Action | Suchtext, Modus und gefundene Seiten-IDs |

## System und Cache

| Hook | Art | Daten / Zweck |
|---|---|---|
| `settings.updated` | Action | geänderte Einstellungen mit altem/neuem Wert |
| `system.maintenance.started`, `system.maintenance.completed` | Action | Wechsel des Wartungsmodus |
| `system.update.started`, `system.update.completed` | Action | Beginn und erfolgreicher Abschluss des CMS-Updates |
| `cache.invalidate` | Action | fachlicher Bereich und betroffene IDs/Schlüssel |

`cache.invalidate` löscht nicht selbständig fremde Caches. Cache-Module abonnieren den Hook und löschen nur die von ihnen verwalteten Daten. Damit bleibt der Core unabhängig von einer konkreten Cache-Lösung.

## Globale CAPTCHA-Anbieter

Installierte Module registrieren einen Anbieter im Filter `captcha.providers`. Dadurch erscheint er automatisch in der CAPTCHA-Steuerung. Nach der Deinstallation verschwindet der Eintrag; eine ungültige Auswahl fällt sicher auf das klassische Rechen-CAPTCHA zurück.

```php
wbce_add_filter('captcha.providers', function (array $providers) {
    $providers['mein_captcha'] = array(
        'name' => 'Mein CAPTCHA',
        'description' => 'Beschreibung für die Einstellungen.',
        'render' => function (array $context) {
            return '<input name="mein_captcha_token">';
        },
        'verify' => function ($input, array $context) {
            return isset($context['request']['mein_captcha_token'])
                && my_verify($context['request']['mein_captcha_token']);
        },
    );
    return $providers;
});
```

Die globale API umfasst `wbce_captcha_render($action, $style, $sectionId, $context)`, `wbce_captcha_verify($input, $sectionId, $context)` und `wbce_captcha_providers()`. `call_captcha()` bleibt kompatibel und leitet moderne Anbieter automatisch weiter. Der Prüfkontext enthält mindestens `section_id`, `request` und `remote_address`; Aufrufer können einen `purpose` wie `login`, `signup`, `password_reset` oder `miniform` ergänzen.

| Hook | Art | Daten / Zweck |
|---|---|---|
| `captcha.providers` | Filter (Array) | installierte Anbieter registrieren |
| `captcha.settings.sections` | Filter (Array) | dynamische Konfiguration in der CAPTCHA-Steuerung |
| `captcha.settings.save` | Action | Einstellungen eines Anbieters FTAN-geschützt speichern |
| `captcha.validation.result` | Filter | abschließendes Prüfergebnis, Anbieter-ID und Kontext |
| `captcha.rendered` | Action | Anbieter-ID und Ausgabekontext |
| `captcha.verified`, `captcha.failed` | Action | Ergebnis mit Anbieter-ID und Kontext |

Die Prüfung muss serverseitig stattfinden. Geheimnisse gehören weder ins HTML noch in Hook-Kontexte. Erfolgreiche Token sollen nur einmal akzeptiert werden.

## Stabilität

Hook-Namen und Argumentreihenfolgen sind öffentliche WBCE-1.7-Schnittstellen. Neue optionale Kontextfelder dürfen ergänzt werden. Bestehende Felder werden innerhalb der WBCE-1.7-Hauptversion nicht entfernt oder umgedeutet. Ausnahmen in Modul-Callbacks werden nicht verschluckt, damit fehlerhafte Erweiterungen eindeutig protokolliert werden.


## Erweiterte Dispatcher-API

Die API folgt dem WordPress-Prinzip: Actions beobachten Ereignisse und geben
nichts zurück; Filters verändern ausschließlich ihren ersten Parameter und
geben ihn zurück. Der optionale Sammel-Hook `all` erhält den Hook-Namen als
erstes Argument und ist ausschließlich für Diagnosen gedacht.

```php
$handle = wbce_add_action('mail.sent', $callback, 20);
wbce_remove_hook($handle);
wbce_remove_all_hooks('mail.sent');

if (wbce_doing_hook('frontend.page.output')) { /* Rekursion vermeiden */ }
$current = wbce_current_hook();
$count = wbce_did_hook('request.initialized');
```

| Funktion | Zweck |
|---|---|
| `wbce_add_action()` / `wbce_add_filter()` | Callback mit Priorität registrieren; kleine Werte laufen zuerst. |
| `wbce_do_action()` / `wbce_apply_filters()` | Action auslösen beziehungsweise Wert filtern. |
| `wbce_apply_array_filters()` | Filter mit verbindlichem Array-Rückgabewert. |
| `wbce_hook_allows()` | Abbruchfilter: nur der boolesche Wert `false` lehnt ab. |
| `wbce_remove_hook()` / `wbce_remove_all_hooks()` | Eine Registrierung oder alle eines Namens entfernen. |
| `wbce_has_hook()` | Prüft, ob ein Hook registriert ist. |
| `wbce_current_hook()` / `wbce_doing_hook()` / `wbce_did_hook()` | Laufenden Hook, Rekursion und bisherige Ausführungen abfragen. |

## Neu verfügbare Kernpunkte

| Hook | Art | Vertrag |
|---|---|---|
| `request.initialized` | Action | Kontext mit HTTP-Methode, Anfragepfad und SAPI, nachdem Datenbank und Hook-API bereitstehen. |
| `addon.install.validation` | Filter (Array) | `valid`, `archive`, `type`, `name`, `action`; `valid=false` stoppt einen Chunk-Upload vor der Installation. |
| `media.beforeUpload`, `media.beforeDelete`, `media.beforeRename` | Abbruchfilter | Kontext mit elFinder-Befehl und Argumenten. |
| `media.upload.validation` | Filter (Array) | `valid`, `name`, `temporary_file`, `target`; kann Datei ablehnen oder Namen ändern. |
| `media.uploaded`, `media.deleted`, `media.renamed` | Action | Ergebnis und ursprüngliche Befehlsargumente nach erfolgreicher Dateioperation. |
| `mail.message`, `mail.transport` | Filter (Array) | Nachricht bzw. Transportresultat; keine Zugangsdaten werden übergeben. |
| `mail.beforeSend`, `mail.sent`, `mail.failed` | Action | Versand-Lebenszyklus mit Nachricht, Fehler und Mailer-Instanz. |
| `worker.definitions` | Filter (Array) | Worker-Module registrieren ihre Aufgaben; Ausführung bleibt außerhalb eines Web-Requests. |
| `mailer.providers` | Filter (Array) | Mailer-Module registrieren Transportanbieter ohne Änderungen am Core. |

## Regeln für Module

- Hooks dürfen keine blockierenden Netzwerkzugriffe im Frontend ausführen. Dafür
  werden Daten in einen Worker oder eine Queue gelegt.
- Hook-Kontexte enthalten keine Passwörter, Tokens, Sitzungsschlüssel oder
  SMTP-Zugangsdaten.
- Filter prüfen ihren Eingabewert und geben stets den erwarteten Typ zurück.
- Ein `all`-Callback darf nur protokollieren und darf keine fachliche Logik
  verändern. Er ist wegen der Laufzeitkosten standardmäßig nicht zu verwenden.
- Neue Hook-Namen sind mit einem fachlichen Namespace wie `media.*`,
  `worker.*` oder `api.*` zu versehen und vor Veröffentlichung hier zu
  dokumentieren.
