## 1.7.0-dev.202

- Benutzer- und Gruppenformulare laden die htmx-Bibliothek über einen zuverlässigen Kernpfad; die Schaltflächen „hinzufügen“ öffnen wieder die Formulare.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- WBCE CMS Admin-Theme

## 1.7.0-dev.201

- Der Update-Assistent entfernt zurückgezogene Outputfilter ohne Modulabhängigkeit und bricht bei einem bereits fehlenden Verzeichnis nicht ab.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.200

- Der Update-Assistent entfernt alte Outputfilter ohne Abhängigkeit von einer optionalen Modul-Funktion.
- Die Gruppenübersicht registriert ihre Berechtigungsanzeige wieder als Twig-Funktion.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.199

- Asynchrone Benutzer- und Gruppenformulare erhalten wieder ein gültiges CSRF-Token und lassen sich speichern.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.198

- Neuinstallationen enthalten die Add-on-Aktivierungsspalte (`addons.active`) direkt im Datenbankschema.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- WBCE Bootstrap Installer

## 1.7.0-dev.197

- Filtert Mailer-Provider nach der gewählten Versand-Engine.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- Mailer

## 1.7.0-dev.192

- Enthält im CMS-Paket nur den Standard-Mailer und den SMTP-Mailer; weitere Mailer bleiben eigenständige Pakete.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.191

- Installiert bei Neuinstallationen als einzigen zweiten Faktor die Authenticator-App (TOTP).

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.190

- Aktualisiert das enthaltene Log Center auf Version 1.1.51.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.207

- Stellt die Bearbeitung und Anzeige von Benutzern und Gruppen wieder her: Die Links erzeugen ihre Kennungen im aktuellen Backend-Kontext, sodass die HTMX-Formulare sie zuverlässig öffnen können.
- Korrigiert die HTMX-Erkennung in den Benutzer- und Gruppenaktionen.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.206

- Korrigiert die HTMX-Erkennung im Gruppenformular. Die Schaltfläche zum Hinzufügen einer Gruppe öffnet das Formular wieder.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.205

- Stellt die fehlenden Verzeichnisfunktionen wieder bereit, die das Benutzerformular für die Auswahl eines Medienordners benötigt. Dadurch endet das Öffnen des Formulars nicht mehr mit HTTP 500.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.204

- Lädt die Klassen der Benutzer- und Gruppenverwaltung auch in den asynchronen Formular-Endpunkten. Die Formulare zum Hinzufügen lassen sich dadurch wieder öffnen.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.203

- Lädt in der Benutzer- und Gruppenverwaltung die benötigten Bedienkomponenten wieder über den korrekten Include-Pfad. Dadurch öffnen die Schaltflächen zum Hinzufügen wieder ihre Formulare.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.189

- Wählt bei Neuinstallationen **WBCE CMS** als Admin-Theme und **MEDIA Horizon** als Frontend-Template.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.188

- Erstellt beim Installationsspeichern die frische `config.php` zuverlässig aus `config.php.new`.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.187

- Erkennt eine bestehende WBCE-Installation im Installer anhand ihrer tatsächlichen Datenbank-Konfiguration statt anhand der Dateigröße von `config.php`.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.186

- Ergänzt stabile UUID-Metadaten für alle im CMS-Paket enthaltenen Kernmodule.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.185

- Ergänzt im Bootstrap-Installer eine nach dem CMS-Setup ausgeführte Paketwarteschlange mit UUID, optionaler Version, Abhängigkeitsauflösung und Legacy-Fallback.
- Führt Bootstrap-Installationen nach erfolgreichem CMS-Setup sicher zu Ende und entfernt den geprüften Bootstrap-Installer automatisch.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.184

- Ergänzt im Bootstrap-Installer eine nach dem CMS-Setup ausgeführte Paketwarteschlange mit UUID, optionaler Version, Abhängigkeitsauflösung und Legacy-Fallback.

### Aktualisierte Pakete

- accessibility_tools 1.3.22
- addon_monitor 1.1.13
- auth_wbce 1.0.2
- authentication 1.0.2
- captcha_cap 1.0.20
- captcha_captchafox 1.0.20
- captcha_friendly 1.0.20
- captcha_icon 1.0.20
- captcha_phpcapcha 1.0.20
- captcha_recaptcha 1.0.20
- captcha_trustcaptcha 1.0.19
- cookie_banner 1.0.34
- cookie_banner_osano 1.0.19
- cookie_banner_safebanner 1.0.15
- cookie_banner_silktide 1.0.11
- force_password_change 2.0.17
- languages 1.2.2
- log_center 1.1.50
- mailer_amazon_ses 1.0.8
- mailer_brevo 1.0.4
- mailer_cleverreach 1.0.7
- mailer_mailgun 1.0.6
- mailer_mailtrap 1.0.4
- mailer_phpmailer 1.0.2
- mailer_postmark 1.0.4
- mailer_rapidmail 1.0.7
- mailer_resend 1.0.4
- mailer_scaleway 1.0.6
- mailer_sendgrid 1.0.4
- mailer_symfony 1.0.2
- miniform 0.23.16
- mod_opf_auto_placeholder 1.3.8
- mod_opf_csstohead 1.0.12
- mod_opf_insert 1.0.12
- mod_opf_move_stuff 1.0.12
- mod_opf_remove_system_ph 1.1.11
- mod_opf_replace_stuff 1.0.12
- mod_opf_wblink 1.0.11
- news_img 5.2.3
- simplepagehead 0.8.5
- sitemap 4.1.2
- tool_account_settings 0.7.22
- tool_debug_dump 1.0.0
- two_factor 1.1.40
- two_factor_email 1.0.22
- two_factor_totp 2.1.21
- two_factor_webauthn 1.1.20
- two_factor_whatsapp 1.1.18
- wbSeoTool 0.8.5
- wbstats 0.2.8.5
- worker 1.10.43

## 1.7.0-dev.183

- Führt konfliktfreie Aktualisierungen aus dem offiziellen WBCE-1.7.0-Branch zusammen und behält die bestehenden Kompatibilitäts- und Modul-Erweiterungen bei.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- Droplets
- elFinder
- Account Settings

## 1.7.0-dev.182

- OPF E-Mail und das dafür benötigte Outputfilter Dashboard sind Bestandteile des CMS-Kernpakets.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.181

- Der Wartungsmodus ist jetzt Bestandteil des CMS-Kernpakets.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.180

- Beschleunigt Datenbankaktualisierungen: unveränderte Kernmodule überspringen ihre Upgrade-Skripte; WBStats und MiniForm vermeiden wiederholte Tabellenumbauten und Index-Neuaufbauten.

### Aktualisierte Pakete

- accessibility_tools 1.3.22
- addon_monitor 1.1.13
- captcha_cap 1.0.20
- captcha_captchafox 1.0.20
- captcha_friendly 1.0.20
- captcha_icon 1.0.20
- captcha_phpcapcha 1.0.20
- captcha_recaptcha 1.0.20
- captcha_trustcaptcha 1.0.19
- cookie_banner 1.0.34
- cookie_banner_osano 1.0.19
- cookie_banner_safebanner 1.0.15
- cookie_banner_silktide 1.0.11
- force_password_change 2.0.17
- languages 1.2.2
- log_center 1.1.49
- mailer_amazon_ses 1.0.8
- mailer_brevo 1.0.4
- mailer_cleverreach 1.0.7
- mailer_mailgun 1.0.6
- mailer_mailtrap 1.0.4
- mailer_phpmailer 1.0.2
- mailer_postmark 1.0.4
- mailer_rapidmail 1.0.7
- mailer_resend 1.0.4
- mailer_scaleway 1.0.6
- mailer_sendgrid 1.0.4
- mailer_symfony 1.0.2
- maintainance_mode 1.1.17
- miniform 0.23.16
- mod_opf_auto_placeholder 1.3.8
- mod_opf_csstohead 1.0.12
- mod_opf_email 1.1.23
- mod_opf_insert 1.0.12
- mod_opf_move_stuff 1.0.12
- mod_opf_remove_system_ph 1.1.11
- mod_opf_replace_stuff 1.0.12
- mod_opf_wblink 1.0.11
- news_img 5.2.3
- outputfilter_dashboard 1.6.23
- simplepagehead 0.8.5
- sitemap 4.1.2
- tool_account_settings 0.7.21
- tool_debug_dump 1.0.0
- two_factor 1.1.40
- two_factor_email 1.0.22
- two_factor_totp 2.1.21
- two_factor_webauthn 1.1.20
- two_factor_whatsapp 1.1.18
- updater 1.0.107
- wbSeoTool 0.8.5
- wbstats 0.2.8.4
- worker 1.10.43

## 1.7.0-dev.179

- Zielpaket für die erneute Prüfung des Diff-Updates.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.178

- Bereits registrierte Aufgaben für Log Rotate laden ihre Registrierung bei Bedarf direkt aus dem installierten Modul nach. Dadurch laufen auch bestehende Aufgaben nach einem Update zuverlässig weiter.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.177

- Zielpaket für die Prüfung des Diff-Updates.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.176

- Der Updater verwendet geprüfte lokale Store-Angebote bis zu 45 Sekunden weiter, damit die Update-Auswahl bei einem langsamen Store sofort erscheint.

### Aktualisierte Pakete

- Updater 1.0.104
- WBCE CMS-Kernpaket

## 1.7.0-dev.175

- Der Updater nutzt vorhandene Store-Deltas auch dann, wenn ältere Kataloge nur die Delta-ID liefern.

### Aktualisierte Pakete

- Updater 1.0.103
- WBCE CMS-Kernpaket

## 1.7.0-dev.174

- Der Updater vergleicht Delta-Ausgangsversionen semantisch, damit ein vorhandenes `dev.168 → dev.172`-Paket auch bei Formatunterschieden erkannt wird.

### Aktualisierte Pakete

- Updater 1.0.102
- WBCE CMS-Kernpaket

## 1.7.0-dev.173

- Worker laden bei jedem geplanten Lauf die Registrierungen installierter Module. Verwaltete Aufgaben wie „Error Log rotieren“ finden dadurch ihre Ausführungsroutine zuverlässig.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.172

- Vorhandene Diff-Updates werden wieder für jede exakt passende installierte Ausgangsversion angeboten; die Vollversion bleibt als Alternative auswählbar.

### Aktualisierte Pakete

- Updater 1.0.101
- WBCE CMS-Kernpaket

## 1.7.0-dev.170

- Die Update-Changelog-Logik einschließlich der asynchronen Schließfunktion gehört vollständig zum Updater. Das Dashboard bindet sie nur über den Widget-Hook ein.

### Aktualisierte Pakete

- Updater 1.0.100
- WBCE CMS-Kernpaket

## 1.7.0-dev.169

- Das Schließen der Update-Changelog-Anzeige im Dashboard wird ohne Neuladen der Seite gespeichert.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.168

- Diff-Update-Karten bieten zusätzlich den direkten Download derselben Zielversion als Vollversion an.

### Aktualisierte Pakete

- Updater 1.0.99
- WBCE CMS-Kernpaket

## 1.7.0-dev.167

- Eine manuell gewählte Store-Version stoppt den automatischen Countdown der empfohlenen neuesten Version zuverlässig.
- Der Updater bindet eine Store-Freigabe erneut an Zielversion, Prüfsumme und Paketadresse der ausgewählten Karte.

### Aktualisierte Pakete

- Updater 1.0.98
- WBCE CMS-Kernpaket

## 1.7.0-dev.166

- Der Updater verwendet bei größeren Entwicklungssprüngen wieder das vollständige CMS-Paket statt eines Diff-Updates.
- Die serverseitige Update-Freigabe bleibt nach der Vorbereitung eine Stunde gültig und übersteht damit längere Downloads oder eine kurze Browserunterbrechung.

### Aktualisierte Pakete

- Updater 1.0.97
- WBCE CMS-Kernpaket

## 1.7.0-dev.165

- Die Datenbank-Fortschrittsausgabe des Updaters verwendet wieder eine gültige PHP-Zeichenverkettung und bricht nicht mehr vor dem Versionsschreiben ab.
- Die Dashboard-Rückkehr öffnet und übernimmt die gesicherte Administrationssitzung auch dann, wenn die neue CMS-Version noch keine PHP-Sitzung gestartet hat.
- Der Updateabschluss korrigiert eine nach dem Datenbanklauf noch fehlende CMS-Versionskennung abgesichert und bestätigt sie anschließend erneut.

### Aktualisierte Pakete

- Updater 1.0.96
- WBCE CMS-Kernpaket

## 1.7.0-dev.163

- Der Updater erkennt Store-Deltas beim erneuten Abruf korrekt über `from_version` und lädt dann das Diff-Paket statt des Vollupdates.

### Aktualisierte Pakete

- Updater 1.0.95
- WBCE CMS-Kernpaket

## 1.7.0-dev.162

- Der Updateabschluss bestätigt jetzt die CMS-Version in der Datenbank. Fehlende oder abweichende Versionskennungen brechen den Abschluss mit einer konkreten Meldung ab.
- Das Datenbank-Update prüft jeden gespeicherten Versionswert unmittelbar nach dem Schreiben.

### Aktualisierte Pakete

- Updater 1.0.94
- WBCE CMS-Kernpaket

## 1.7.0-dev.161

- Die Anmeldeseite zeigt nur registrierte, aktivierte und vollständig konfigurierte Authentifizierungsanbieter an.
- Die Zugangsfelder passen sich an den gewählten Anbieter an; die Anbieter-Auswahl steht nun unter den Zugangsfeldern.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.160

- Datenbank-Updates von bestehenden WBCE-1.7-Installationen überspringen die bereits abgeschlossenen, historischen Benutzer-Migrationen.
- Die notwendigen Schema-Prüfungen und die vollständige Migration älterer Installationen bleiben erhalten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.159

- Der Updater überträgt Dateifortschritt dichter über den direkten Browser-Stream, ohne die robuste Fortschrittsprotokollierung zu verlangsamen.
- Unveränderte Dateien werden per CRC erkannt und ohne Wiederherstellungskopie oder erneutes Schreiben übersprungen.

### Aktualisierte Pakete

- Updater 1.0.92
- WBCE CMS-Kernpaket

## 1.7.0-dev.157

- Store-Abfragen laden wieder ohne Delta-Erzeugung sofort aus dem vorhandenen Katalog.
- Diff-Pakete werden im Store gezielt über „Diff-Update erstellen“ mit sichtbarem Fortschritts-Layover erstellt.

### Aktualisierte Pakete

- Store Server 4.2.59
- Updater 1.0.91
- WBCE CMS-Kernpaket

## 1.7.0-dev.156

- Der Store erzeugt bei einer Update-Abfrage bei Bedarf direkt das passende Diff-Paket von der installierten zur neuesten angebotenen CMS-Version.
- Der Updater übermittelt seine installierte Ausgangsversion an den Store und verwendet das bereitgestellte Diff-Paket automatisch.

### Aktualisierte Pakete

- Store Server 4.2.57
- Updater 1.0.90
- WBCE CMS-Kernpaket

## 1.7.0-dev.155

- Der Updater zeigt vor dem Start eindeutig an, ob ein passendes Diff-Update verfügbar ist oder ein Vollupdate geladen wird, einschließlich der jeweiligen Paketgröße.
- Store-Deltas werden anhand der tatsächlich gelieferten Ausgangsversion erkannt und mit ihrer eigenen Größe sowie Prüfsumme geladen.

### Aktualisierte Pakete

- Updater 1.0.89
- WBCE CMS-Kernpaket

## 1.7.0-dev.154

- Versionsanhebung für ein Delta-Update.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.153

- Backend-Sitzungen werden nur durch echte Benutzeraktionen verlängert. Automatische AJAX-, Polling- und Worker-Aufrufe verlängern die Leerlaufzeit nicht.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.152

- Backend-Anmeldungen erhalten zusätzlich zur Leerlaufzeit eine feste Ablaufzeit ab dem Login. Hintergrundaufrufe können eine privilegierte Sitzung nicht mehr unbegrenzt verlängern.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.151

- Das CMS-Paket enthält nur noch den definierten Kernbestand. Der zentrale Mailer und SMTP bleiben enthalten; die Sprachverwaltung sowie optionale Mail-, Login-, 2FA-, CAPTCHA- und Funktionsanbieter werden separat ausgeliefert.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.150

- Die Hooks-Bridge gehört ausschließlich als separates Kompatibilitätspaket zu WBCE 1.6.8 und wird nicht mehr im nativen WBCE-1.7-CMS-Paket ausgeliefert.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.149

- Die zentrale Authentifizierung und der geschützte WBCE-Provider sind die einzigen mitgelieferten Authentifizierungsbestandteile.
- Externe Anmeldemethoden werden ausschließlich als installierbare Provider geführt und zentral unter Authentifizierung verwaltet.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- Authentifizierungs-Provider

## 1.7.0-dev.149

- Korrigiert die Backend-Sitzungsablaufzeit: Sie folgt `wb_session_timeout` und nicht mehr der Laufzeit von SecureForm-Tokens.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.146

- Enthält den aktuellen konsolidierten CMS-Stand einschließlich der korrigierten Backend-Sitzungsablaufzeit.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.145

- Backend-Sitzungen verwenden für ihre tatsächliche Ablaufzeit wieder `wb_session_timeout` statt des unabhängigen SecureForm-Timeouts.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.146

- Die zentrale Authentifizierung und der eingebaute WBCE-Provider sind zusätzlich gegen eine Deinstallation über die Add-on-Verwaltung geschützt.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.145

- Primäre Login-Provider können nach erfolgreicher externer Prüfung optional lokale Benutzer mit ausdrücklich konfigurierten Standarddaten anlegen.
- Die Erstellung bleibt auf global freigegebene Provider und bereits vorhandene lokale Gruppen beschränkt.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- Authentifizierungs-Provider

## 1.7.0-dev.144

- Die zentrale Authentifizierung erscheint in den persönlichen Benutzereinstellungen und stellt registrierten Login-Providern einen eigenen Hook für asynchron gespeicherte persönliche Konfiguration bereit.
- Berechtigungen für primäre Login-Provider verbleiben im Kern und können nicht durch ein Provider-Modul erhöht werden.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.143

- Führt eine modulare Hook-Registry für primäre Login-Provider vor der unabhängigen Zwei-Faktor-Prüfung ein.
- Der geschützte WBCE-Provider bleibt für Administratoren als Wiederherstellungszugang verfügbar; externe Provider können global, pro Benutzer oder deaktiviert freigegeben werden.
- Die Hook-Bridge stellt die Registrierungs-Schnittstelle auch unter WBCE 1.6.8 bereit. Mail-basierte Provider verwenden den zentralen modernen Mailer.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- Hook-Bridge 1.3.1

## 1.7.0-dev.142

- Backend-Outputfilter stellen FTAN-Sicherheitsfelder nach ihrer Verarbeitung zentral wieder her. Asynchrone Modulaktionen behalten damit ihren CSRF-Schutz und werden nicht mehr durch leere Tokens abgewiesen.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket
- Security Center 1.0.85 (separates Modulpaket)

## 1.7.0-dev.141

- Die E-Mail-2FA verwendet ausschließlich den zentralen Mailer und prüft dessen aktivierte, erfolgreich getestete Konfiguration vor der Freigabe.
- Die 2FA-Konfiguration zeigt den Bereitschaftsstatus des zentralen Mailers an.

### Aktualisierte Pakete

- Mailer 1.0.48
- 2FA - E-Mail-Code 1.0.22
- Log Center 1.1.48

## 1.7.0-dev.140

- Registriert die geprüfte Changelog eines abgeschlossenen CMS-Updates dauerhaft und zeigt sie direkt unter dem Dashboard-Kopf als schließbare, scrollbare Karte an.
- Der Updater übernimmt für das Dashboard die vollständige CMS-Changelog der tatsächlich installierten Version.

### Aktualisierte Pakete

- Updater 1.0.82
- Log Center 1.1.41
- WBCE CMS Admin-Theme 1.0.35
- MEDIA CMS Admin-Theme 1.6.96
- Security Center aus dem CMS-Paket entfernt (weiterhin separat verfügbar)

## 1.7.0-dev.139

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.138

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.137

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.40

## 1.7.0-dev.136

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.39
- security_center 1.0.83

## 1.7.0-dev.135

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.38
- security_center entfernt
- updater 1.0.81

## 1.7.0-dev.134

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.133

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.36
- security_center 1.0.83

## 1.7.0-dev.132

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- security_center entfernt

## 1.7.0-dev.131

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- updater 1.0.78

## 1.7.0-dev.130

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.35

## 1.7.0-dev.129

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- wbce-cms 1.0.34

## 1.7.0-dev.128

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.30

## 1.7.0-dev.127

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.28

## 1.7.0-dev.126

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.125

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.124

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.26

## 1.7.0-dev.123

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.21

## 1.7.0-dev.122

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.121

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.120

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.16
- updater 1.0.76

## 1.7.0-dev.119

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.13

## 1.7.0-dev.118

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.117

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- WBCE CMS-Kernpaket

## 1.7.0-dev.116

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.11
- media_horizon 1.0.8

## 1.7.0-dev.115

- Aktualisiert das CMS-Kernpaket und die nachfolgend aufgeführten enthaltenen Komponenten.

### Aktualisierte Pakete

- log_center 1.1.8

## 1.7.0-dev.114

- Erweitert die konfigurierbare Laufzeit-, Access- und Trace-Anzeige einschließlich der optionalen Laufzeit in den Admin-Themes.

### Aktualisierte Pakete

- log_center 1.1.7

## 1.7.0-dev.113

- Das optionale Anforderungsprotokoll im Log Center bietet einzeln schaltbare Regeln für langsame Aufrufe, HTTP-Fehler, AJAX/API-Fehler, Worker/Cron und Stichproben erfolgreicher Aufrufe.

### Aktualisierte Pakete

- log_center 1.1.6

## 1.7.0-dev.112

- Log Center ergänzt ein optionales Anforderungsprotokoll für langsame Aufrufe, Fehlerantworten sowie Worker- und Cron-Aufrufe.

### Aktualisierte Pakete

- log_center 1.1.5

## 1.7.0-dev.111

- Log Center führt die Logrotation bei Installationen ohne Worker einmal je Sitzung als sicheren Fallback aus.

### Aktualisierte Pakete

- log_center 1.1.4

## 1.7.0-dev.110

- Log Center übernimmt die automatische Rotation des PHP-Fehlerprotokolls; das separate Logrotate-Modul ist nicht mehr Bestandteil des CMS. Laufzeit- und Trace-Ereignisse werden zusätzlich nicht blockierend an die Log-Center-Warteschlange übergeben.

### Aktualisierte Pakete

- log_center 1.1.3
- logrotate entfernt

## 1.7.0-dev.109

- Log Center: optimierte Filter- und Suchfelder, funktionierende Trace-Details sowie sichtbare Einstellungsnavigation mit Remote-Server- und Laufzeitprotokoll-Optionen.

### Aktualisierte Pakete

- log_center 1.1.2

## 1.7.0-dev.108

- Log Center enthält die lokale Loganzeige mit Filtern, Live-Aktualisierung, Detailansicht und Archivierung. Die Log-Server-Verbindung liegt auf einer eigenen Einstellungsseite.

### Aktualisierte Pakete

- log_center 1.1.1

## 1.7.0-dev.107

- Der gebündelte Error Logger wurde entfernt. Log Center übernimmt die lokale PHP-Fehlererfassung mit eigenen Klassen und bleibt zu einem separat installierten Original-Error-Logger kompatibel.

### Aktualisierte Pakete

- errorlogger entfernt
- log_center 1.1.0

## 1.7.0-dev.106

- Der Worker begrenzt Scheduler-Läufe durch eine globale Start-Sperre, ein Laufzeitbudget, eine maximale Aufgabenanzahl und konfigurierbare Pausen zwischen Hintergrundstarts.
- Der angezeigte System-Cron verwendet eine niedrige CPU-Priorität.

## 1.7.0-dev.105

- Ergänzt ein gezielt aktivierbares Laufzeitprotokoll für langsame Seitenaufrufe, Modulinitialisierungen und Hooks.
- Der detaillierte Laufzeit-Trace protokolliert jeden gemessenen Bootstrap-, Modul- und Hook-Schritt im Error Logger.

## 1.7.0-dev.104

- Prüft die Backend-Anmeldung über die generische CAPTCHA-Provider-Schnittstelle, damit jeder aktive CAPTCHA-Anbieter gleich behandelt wird.
- Bündelt CAPTCHA - ALTCHA 1.1.32 mit sitzungsgebundener Nachweisprüfung für stabile Anmeldungen.

## 1.7.0-dev.103

- Stellt die Administrator-Sitzung beim Dashboard-Rücksprung einmalig über einen geschützten Updater-Übergabetoken wieder her.

## 1.7.0-dev.102

- Erneuert beim asynchronen Update-Abschluss die wiederhergestellte Administrator-Sitzung für den Dashboard-Rücksprung.
- Bündelt Updater 1.0.65 mit Sitzungsname in der Update-Wiederherstellung.

## 1.7.0-dev.101

- Bündelt Updater 1.0.60 mit stabiler Download-Freigabe für die im Updater erzeugten Sicherungen.

## 1.7.0-dev.100

- Bündelt Updater 1.0.58: Store-Versionen werden absteigend angezeigt, der aktuellste Stand wird ausgewählt und die automatische Installation verarbeitet Ablehnungen ohne JSON-Abbruch.

## 1.7.0-dev.99

- Bündelt die aktuellen Versionen der enthaltenen Module, Sprachdateien und Templates.
- Ergänzt vollständige Store-Metadaten und Vorschaubilder für alle enthaltenen Module und Templates.
- Aktualisiert den integrierten Updater auf 1.0.56 mit der gemeinsamen Store-Anbindung.

## 1.7.0-dev.98

- Korrigiert die Store-Metadaten: Die Paketversion entspricht nun der CMS-Version und kann als neue Version im Store bereitgestellt werden.

## 1.7.0-dev.97

- Erhöht die Entwicklungskennung für die aktuelle Paketveröffentlichung.

## 1.7.0-dev.96

- Bündelt Updater 1.0.55 mit gleich großen Bestätigungsbuttons und Security Center 1.0.79 mit blauem Direktlink zu offenen Funden im Dashboard.

## 1.7.0-dev.95

- Aktualisiert den integrierten Updater auf 1.0.54: Er erstellt bei fehlendem Backup Center eigenständig eine lokale ZIP-Sicherung mit CMS-Dateien und Datenbankexport und lädt sie automatisch herunter.

## 1.7.0-dev.93

- Das neue Datenbank-Sitzungssystem übernimmt vorhandene WBCE-1.x-Sitzungen während eines Updates ohne die Sitzungstabelle zu ersetzen. Der Dashboard-Rücksprung bleibt angemeldet.

## 1.7.0-dev.92

- Der Update-Abschluss übernimmt die Administrator-Sitzung auch dann zuverlässig, wenn die neue Laufzeit noch keine Sitzung geöffnet hat. Der Rücksprung zum Dashboard bleibt dadurch angemeldet.
- Updater und Wartungsmodus aktualisieren das Wartungs-Symbol im Admin-Kopf ohne Neuladen; die Store-Prüfung zeigt einen Ladekreis.

## 1.7.0-dev.62

- Die Hook-Schnittstelle erhält WordPress-orientierte Dispatcher-Abfragen, Request- und Add-on-Validierungshooks sowie eine vollständige Entwicklerdokumentation.
- Die WBCE-1.6.8-Hook-Bridge verwendet den identischen Dispatcher-Vertrag.

## 1.7.0-dev.63

- Mailer-Provider verwenden getrennte SMTP-Profile; feste Vorgaben werden korrekt dargestellt und Auswahlkarten folgen dem Admin-Theme.

Please visit the [WBCE Github](https://github.com/WBCE/WBCE_CMS/commits) repository for the documentation of recent changes to the code.

## 1.7.0-dev.53

- Add-on-Vorabprüfungen behandeln Entwicklungsstände eines Releases als kompatibel mit dessen Mindestversion.

## 1.7.0-dev.51

- Die gebündelte Version des Admin-Templates WBCE CMS ist auf 1.0.27 angehoben.

## 1.7.0-dev.50

- Admin-Templates laden ihre großen CSS-Dateien in eingebetteten Meldungen nicht mehr doppelt.

## 1.7.0-dev.49

- Die Add-on-Übersicht verwendet in WBCE CMS und MEDIA CMS drei kompakte Kacheln für Module, Templates und Sprachen.

## 1.7.0-dev.48

- Der Update-Assistent zeigt die klassischen Schritte und die Änderungen wieder vollständig auf einer normalen Seite an.

## 1.7.0-dev.47

- Die zentrale Sprachauflösung übergibt die eingestellte Backend-Sprache an aktuelle Module; vorhandene deutsche Sprachdateien überlagern wieder Englisch.

## 1.7.0-dev.46

- MEDIA Horizon wird im Installationspaket mitgeliefert und bei Neuinstallationen als Frontend-Template vorausgewählt.

## 1.7.0-dev.21

- Admin-Tools verwendet die Backend-Sprache für Twig-Texte; die Suchleiste bleibt innerhalb der Inhaltsbreite.

## 1.7.0-dev.20

- Admin-Tools verwendet für Einstellungen, Datenbankabfragen und Modul-Icons wieder die Legacy-kompatiblen Kernschnittstellen.

## 1.7.0-dev.19

- Die Sprachklassen werden unabhängig von einer bereits vorhandenen Legacy-Übersetzungsfunktion geladen; Admin-Tools startet dadurch zuverlässig.

## 1.7.0-dev.18

- Die aktuellen Admin-Templates WBCE CMS, WBCE Flat und Argos sind als eigenständige Pakete aktualisiert und mit dem CMS-Stand abgeglichen.

## 1.7.0-dev.17

- Aktiviert-/Deaktiviert-Auswahlfelder der Grundeinstellungen werden als zugängliche Schiebeschalter dargestellt.

## 1.7.0-dev.16

- Grundeinstellungen werden nach Prüfung des Formular-Tokens asynchron gespeichert; Erfolg und Fehler erscheinen direkt im Formular.

## 1.7.0-dev.15

- Der Fortschritts-Layer des Kern-Update-Assistenten verwendet einen vollständig weißen Hintergrund.
- Der letzte Update-Schritt zeigt wieder die mitgelieferten Änderungen in einer aufklappbaren, scrollbar begrenzten Liste an.

## 2.0.0-dev.30

- Der durch Seitenaufrufe ausgelöste Worker startet erst, nachdem PHP die fertige Antwort an den Browser übergeben und die Sitzung freigegeben hat. Store-Aktualisierungen und andere Hintergrundaufgaben können den auslösenden Webseitenaufruf dadurch nicht mehr blockieren.
- Log Center lädt die vorhandene Worker-Registry unabhängig von der alphabetischen Modulreihenfolge und führt Heartbeat sowie Warteschlangenzustellung nicht mehr blockierend im Webseitenaufruf aus.
- Der WBCE Update-Assistent überträgt große Update-ZIPs in Teilstücken und unterstützt dadurch Pakete bis 512 MB auch bei `upload_max_filesize` und `post_max_size` von 2 MB.
- Die Installation von Modulen und Templates verwendet denselben geschützten Chunk-Upload und unterstützt Pakete bis 512 MB.
- Zusammengesetzte Pakete bleiben an die angemeldete Admin-Sitzung gebunden und durchlaufen anschließend unverändert die vorhandenen ZIP-, Add-on- und Sicherheitsprüfungen.

## 2.0.0-dev.29

- „Seite hinzufügen“ nutzt unabhängig von älteren Rasterregeln des Admin-Templates die vollständige verfügbare Breite.

## 2.0.0-dev.28

- Der zunächst geschlossene Bereich „Seite hinzufügen“ steht jetzt vor der Seitenstruktur; die Seitenstruktur verwendet darunter die volle Breite.
- Die optionale Intro-Seitenkarte folgt erst nach der Seitenstruktur.

## 2.0.0-dev.27

- Die Seitenverwaltung verwendet eine breite Hauptkarte und eine am rechten Rand haftende Werkzeugkarte.
- „Neue Seite“ ist beim Aufruf geschlossen und wird erst auf Wunsch aufgeklappt; auf kleinen Bildschirmen bleibt der Bereich vollständig responsiv.
- Module, Templates und Sprachen sowie deren Detailseiten besitzen nun durchgängig den blauen Kopfbereich des aktiven Admin-Templates.

## 2.0.0-dev.26

- Erweiterte Grundeinstellungen werden über einen echten asynchronen Schalter nachgeladen; Inline-Navigation und vollständiger Seitenaufruf entfallen.
- Alle Admin-Templates einschließlich MEDIA CMS 1.5.3 verwenden dieselbe robuste Umschaltung.

## 2.0.0-dev.25

- Dashboard-Karten verwenden gültige Kartencontainer, sodass die Unterlinks für Module, Templates, Sprachen, Benutzer und Gruppen zuverlässig innerhalb ihrer Karte bleiben.
- Die Seitenliste nutzt die vollständige Inhaltsbreite; die Karten zum Anlegen einer Seite und zur Intro-Seite stehen in einem eigenen responsiven Raster darunter.
- MEDIA CMS 1.5.2 übernimmt dieselben Korrekturen als eigene Template-Komponenten.

## 2.0.0-dev.24

- Beide mitgelieferten Admin-Templates enthalten nun selbst die vollständigen dev.20-Komponenten und das identische modernisierte Markup für Seiten, Erweiterungen, Benutzer, Gruppen, Meine Daten, Grundeinstellungen und Admin-Tools.
- Layoutdateien werden vorrangig über `THEME_URL` aus dem aktiven Admin-Template geladen. Fehlende Komponenten kommen aus dem geschützten Kernpfad `admin/interface/css/components`, der nicht als Template deinstalliert werden kann.
- MEDIA CMS 1.5.1 enthält die sechs Layout-Komponenten zusätzlich selbst und übernimmt sie mit seiner eigenen blauen Farbpalette.
- Dadurch bleibt das dev.20-Erscheinungsbild unabhängig davon erhalten, ob `wbce_flat_theme` oder `argos_theme_reloaded` aktiv ist.

## 2.0.0-dev.23

- Admin-Template: das Erscheinungsbild der modernisierten Kernseiten entspricht wieder dev.20. Die dev.21-weiten globalen Layoutüberschreibungen wurden entfernt.
- Die unveränderten dev.20-Komponenten liegen weiterhin vollständig unter `templates/theme_fallbacks/css`; Primärfarben stammen über CSS-Variablen aus dem aktiven Admin-Template.
- Die zusätzliche Template-Schicht greift nur bei Admin-Tools und Modul-Integration ein und verändert nicht mehr das bewährte Layout von Seiten, Medien, Erweiterungen, Meine Daten, Grundeinstellungen sowie Benutzer und Gruppen.

## 2.0.0-dev.22

- Admin-Templates: sämtliche Layoutregeln für die modernisierten Kernseiten, Benutzerverwaltung, Admin-Tools und Modul-Header liegen nun unter `templates/theme_fallbacks/css`; der PHP-Kern enthält keine zugehörigen Seitenstyles mehr.
- Aktive Admin-Templates liefern ihre Farbvariablen und binden die gemeinsamen Template-Komponenten ein. Funktionale JavaScript-Dateien verbleiben bewusst im jeweiligen Kernbereich.
- Worker 1.9.10: neue Herkunftsregel für automatisch beziehungsweise über „Neue Aufgabe“ angelegte Aufgaben; die Versionsnummer wurde gegenüber der bereits veröffentlichten 1.9.9 eindeutig erhöht.
- Worker 1.9.11: einmalige sichere Herkunftsmigration schützt auch bereits vorhandene, von Modulen angelegte Aufgaben wie das Store-Autoupdate vor Änderungen in der Worker-Oberfläche.

## 2.0.0-dev.21

- Backend-Oberfläche: Seiten, Medien, Erweiterungen, Meine Daten, Grundeinstellungen sowie Benutzer und Gruppen verwenden wieder die Vorlagen des aktiven Admin-Templates und gemeinsame, templategesteuerte Komponenten.
- Admin-Tools: einheitlicher blauer Kopfbereich, Karten-, Formular- und Tabellenstil; Modulwerkzeuge ohne eigenen Kopf erhalten automatisch einen passenden Modul-Header.
- Worker 1.9.9: nur über „Neue Aufgabe“ erstellte Aufgaben sind im Worker frei verwaltbar; von Modulen angelegte Aufgaben erscheinen ohne besondere Markierung und ohne Bearbeiten-, Start-, Aktivierungs- oder Löschaktionen.

## 2.0.0-dev.20

- Zeitzonenauswahl: ungültige Metadateneinträge aus System-Zeitzonendaten werden übersprungen, sodass „Meine Daten“ und „Grundeinstellungen“ zuverlässig laden.
- Worker 1.9.7: keine `open_basedir`-Warnung mehr beim Ermitteln des PHP-Programms; CLI-Läufe stellen einen sicheren Request-Kontext für ältere Pre-Init-Module bereit.
- Worker 1.9.8: automatisch verwaltete Aufgaben sind schreibgeschützt; die tägliche Bereinigung der Worker-Protokolle wird bei Installation und Upgrade zuverlässig angelegt.

## 2.0.0-dev.19

- Admin-Tools: die zentral erzeugte, doppelte Seitenüberschrift auf den einzelnen Werkzeugseiten entfernt; jedes Werkzeug verwendet weiterhin seine eigene Modulüberschrift.

## 2.0.0-dev.18

- elFinder-Menü-, Navigations- und Kontextmenü-Symbole im hellen Design abgedunkelt
- Hover-, Aktiv- und Deaktiviert-Zustände der Mediensymbole klar unterscheidbar gemacht

## 2.0.0-dev.17

- Feste UTC-Stundenverschiebungen durch IANA-Zeitzonen mit Sommer-/Winterzeit ergänzt
- System- und Benutzerzeitzone über die Oberfläche auswählbar gemacht
- Bestehende deutsche UTC+1-Installationen sicher auf Europe/Berlin migriert
- UTC-Datenhaltung und dynamische lokale Darstellung klar getrennt
- Worker plant Cron-Ausdrücke und zeigt Laufzeiten in der CMS-Zeitzone an
- Medienverwaltung und elFinder vollständig auf das helle Farbschema umgestellt

## 2.0.0-dev.16

- Grundlegende und erweiterte Gruppenrechte ohne Seitenaufruf umschaltbar gemacht
- Gruppen lassen sich abgesichert asynchron anlegen und speichern
- Erfolgs- und Fehlermeldungen erscheinen unmittelbar im Gruppenformular
- Dashboard und Seiten auf eine gemeinsame Workspace-, Banner- und Kartenstruktur vereinheitlicht
- Seitenbaum, Seitenformular und Intro-Seite konsequent in dasselbe Kartenlayout überführt

## 2.0.0-dev.15

- Gruppen- und Benutzerverwaltung auf dieselbe zentrale Template-Grundlage gestellt
- Identische Kopfzeilen, Auswahlkarten, Feldbreiten und Aktionsleisten für Benutzer und Gruppen
- Gruppenformular an die Seitenhierarchie des Benutzerformulars angeglichen
- Themeabhängige Abweichungen bei Gruppenübersicht und Gruppenbearbeitung entfernt

## 2.0.0-dev.14

- Lesen-, Schreiben- und Ausführen-Rechte für Dateien und Verzeichnisse zeilenfest ausgerichtet
- Gruppenrechte konsequent untereinander statt in kollidierenden Float-Spalten dargestellt
- Gruppenübersicht strukturell vollständig an die Benutzerübersicht angeglichen
- Weiße Inhaltskarten für Dashboard und Seiten in beiden Backend-Themes vereinheitlicht

## 2.0.0-dev.13

- Beschriftungsspalte der Grundeinstellungen zugunsten der Eingabefelder verkleinert
- Dateisystem-Zugriffsrechte in ein eigenständiges, stabiles Berechtigungsraster überführt
- Dashboard in beiden Backend-Themes konsequent auf blaues Kopfbanner und Karten umgestellt
- Seitenübersicht und Seitenformular gestalterisch weiter vereinheitlicht
- Gruppenübersicht an das Layout der Benutzerübersicht angeglichen
- Einfache und erweiterte Gruppenrechte als responsive Berechtigungskarten neu aufgebaut

## 2.0.0-dev.12

- PHP 8.5 als Entwicklungs- und Laufzeitbasis festgelegt, Mindestversion PHP 8.2
- Isolierte und später entfernbare Kompatibilitätswege für PHP 8.2, 8.3 und 8.4 ergänzt
- Unter PHP 8.5 veraltete Casts, Ressourcenfreigaben und HTTP-Header-Zugriffe ersetzt
- Erste Zugangsseite der Benutzerverwaltung auf das zentrale Kartenlayout umgestellt
- Blaue Kopfbanner für Benutzer, Seiten, Medien und Erweiterungen ergänzt

## 2.0.0-dev.11

- Benutzerverwaltung in Übersicht und Formular auf das responsive Kartenlayout umgestellt
- Modulbereiche im Benutzerformular als dokumentierte Hook-Schnittstelle erweitert
- Passwortänderung beim nächsten Login beim Anlegen und Bearbeiten dynamisch auswählbar
- Verpflichtende Passwortänderung sperrt bis zum Abschluss auch direkte Schreibzugriffe
- Seitenverwaltung, Medienverwaltung und Erweiterungsübersicht gestalterisch vereinheitlicht

## 2.0.0-dev.10

- Bestehende Sitzungen werden nach Passwort-Reset und Passwortänderungen widerrufen
- Remote-Updates benötigen zwingend eine gültige SHA-256-Prüfsumme; manuelle Uploads bleiben möglich
- Updateausführung auf POST und eine einmalige, sitzungsgebundene Autorisierung umgestellt
- Sitzungscookies mit explizitem Secure-, HttpOnly- und SameSite-Schutz gehärtet
- Login-Weiterleitungen auf lokale CMS-Ziele beschränkt
- Unsichere Objekt-Deserialisierung in den zentralen Einstellungen unterbunden
- Globale Sicherheitsheader gegen Clickjacking, MIME-Sniffing und unerwünschte Referrer ergänzt

## 2.0.0-dev.9

- Grundeinstellungen auf ein zentrales, Admin-Theme-unabhängiges Store-Kartenlayout umgestellt
- Erweiterte Einstellungen werden ohne vollständiges Neuladen der Adminseite nachgeladen
- Ladezustand, Fehler-Rückfall und Wiederherstellung des geöffneten Einstellungsabschnitts ergänzt

## 2.0.0-dev.8

- Der Updater behält individuelle Backend-Themes bei, statt nur fest eingetragene Theme-Namen zu akzeptieren
- Ein Rückfall auf `wbce_flat_theme` erfolgt nur noch, wenn das konfigurierte Backend-Theme tatsächlich fehlt oder ungültig ist

## 2.0.0-dev.7

- „Meine Daten“ verwendet nun die zentrale WBCE-2-Profilvorlage und kann nicht mehr durch eine veraltete Vorlage des aktiven Admin-Themes ersetzt werden

## 2.0.0-dev.6

- Seite „Meine Daten“ in beiden offiziellen Admin-Themes auf das Store-Kartenlayout umgestellt
- Profil, Sprache/Zeit und Passwort als klar getrennte, responsive Einstellungsbereiche dargestellt
- Breitere Beschriftungsspalte und unveränderte Unterstützung dynamischer Moduleinstellungen

## 2.0.0-dev.5

- CAPTCHA-Anbieter vollständig aus dem CMS-Paket entfernt und als Einzelmodule paketiert
- Nur regulär installierte CAPTCHA-Module werden in der Auswahl registriert
- Getrennte CAPTCHA-Konfiguration für Admin-Login, Passwort-Reset und Registrierung
- Versehentlich durch dev.2 mitkopierte, nicht installierte Provider werden beim Update bereinigt

## 2.0.0-dev.4

- PHP-8-TypeError beim Laden der globalen CAPTCHA-Anbieterliste behoben
- Weiße Admin-Login-Seite nach dem Update auf dev.2 behoben

## 2.0.0-dev.3

- Admin-Login nach einer vom Update geleerten Sitzung wiederhergestellt
- Vom Updater aktivierter Wartungsmodus wird nach Abschluss automatisch beendet

## 2.0.0-dev.2

- Globale, Hook-basierte CAPTCHA-Provider-API mit kompatiblem `call_captcha()`
- Einzelmodule für CAP, ALTCHA, IconCaptcha und PHP-GD Text-CAPTCHA
- Zentrale CAPTCHA-Prüfung für Login, Registrierung, Passwort-Reset und MiniForm

## 2.0.0-dev.1
- Zentrale, abwärtskompatible Hook-Schnittstelle für Module
- Erweiterbare Anmeldung einschließlich zusätzlicher Authentifizierungsfaktoren
- Sichere zweistufige Passwortzurücksetzung
- Unicode-, Sonderzeichen- und E-Mail-taugliche Benutzernamen
- IANA-Zeitzonen mit automatischer Sommer-/Winterzeit
- Optionale Add-on-Aktivierung ohne Datenverlust
- Update-Migrationen für bestehende WBCE-Installationen
