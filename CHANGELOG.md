## 1.7.0-dev.114

- Erweitert die konfigurierbare Laufzeit-, Access- und Trace-Anzeige einschließlich der optionalen Laufzeit in den Admin-Themes.

## 1.7.0-dev.113

- Das optionale Anforderungsprotokoll im Log Center bietet einzeln schaltbare Regeln für langsame Aufrufe, HTTP-Fehler, AJAX/API-Fehler, Worker/Cron und Stichproben erfolgreicher Aufrufe.

## 1.7.0-dev.112

- Log Center ergänzt ein optionales Anforderungsprotokoll für langsame Aufrufe, Fehlerantworten sowie Worker- und Cron-Aufrufe.

## 1.7.0-dev.111

- Log Center führt die Logrotation bei Installationen ohne Worker einmal je Sitzung als sicheren Fallback aus.

## 1.7.0-dev.110

- Log Center übernimmt die automatische Rotation des PHP-Fehlerprotokolls; das separate Logrotate-Modul ist nicht mehr Bestandteil des CMS. Laufzeit- und Trace-Ereignisse werden zusätzlich nicht blockierend an die Log-Center-Warteschlange übergeben.

## 1.7.0-dev.109

- Log Center: optimierte Filter- und Suchfelder, funktionierende Trace-Details sowie sichtbare Einstellungsnavigation mit Remote-Server- und Laufzeitprotokoll-Optionen.

## 1.7.0-dev.108

- Log Center enthält die lokale Loganzeige mit Filtern, Live-Aktualisierung, Detailansicht und Archivierung. Die Log-Server-Verbindung liegt auf einer eigenen Einstellungsseite.

## 1.7.0-dev.107

- Der gebündelte Error Logger wurde entfernt. Log Center übernimmt die lokale PHP-Fehlererfassung mit eigenen Klassen und bleibt zu einem separat installierten Original-Error-Logger kompatibel.

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

## 1.7.0-dev.94

- Bündelt Updater 1.0.51 mit unabhängiger Fortschrittsanzeige und gesichertem Sitzungsübergang.
- Bündelt Log Center 1.0.19, Error Logger 1.1.69 und Worker 1.10.37 für die nicht blockierende Remote-Protokollierung.
- Aktualisiert das integrierte Admin-Theme WBCE CMS auf 1.0.30.

## 1.7.0-dev.76

- Vereinfacht den manuellen Updater-Upload auf ZIP und optionale SHA-256-Datei.

## 1.7.0-dev.75

- Ergänzt Mehrfach-Upload für Update-ZIP und SHA-256-Prüfsumme.

## 1.7.0-dev.74

- Gibt den nächsten Updater-Schritt nach dem Backup-Download frei.

## 1.7.0-dev.73

- Aktualisiert die Backup-Rückmeldung des Updaters.

## 1.7.0-dev.72

- Formatiert die Backup-Zeit im Updater mit der WBCE-Zeitzone.

## 1.7.0-dev.71

- Überarbeitet den Backup-Schritt des Updaters mit Download des neuesten lokalen Backups.

## 1.7.0-dev.70

- Führt Backup, Store-Auswahl und manuellen Upload im Updater schrittweise.

## 1.7.0-dev.69

- Bezieht CMS-Updates kontrolliert aus verbundenen Store-Quellen.


## 1.7.0-dev.68

- Erkennt das CMS-Kernpaket als eigenständiges, prüfbares Store-Release.
- Ergänzt den Bootstrap-Installer sowie die Store-/Datei-Auswahl im Updater.

Please visit the [WBCE Github](https://github.com/WBCE/WBCE_CMS/commits) repository for the documentation of recent changes to the code.

## 1.7.0-dev.67

- Paketstruktur: Backup Center, Backup Server und die Speicheranbieter werden wieder ausschließlich als eigenständige Add-on-Pakete ausgeliefert.

## 1.7.0-dev.66

- CMS-Bestandteile: Backup Center, Backup Server, alle Backup-Speicheranbieter, Logrotate und der Updater sind auf den aktuellen geprüften Stand angehoben und werden bei jedem CMS-Update mitgeliefert.

## 1.7.0-dev.65

- Updater: Der Abschluss übernimmt die bestehende Administrator-Sitzung unverändert, damit der automatische Wechsel zum Dashboard nicht erneut zur Anmeldung führt.

## 1.7.0-dev.64

- Updater: Der Abschluss hält die Administrator-Sitzung, wechselt automatisch zum Dashboard und zeigt die geprüften Release Notes dort in einem schließbaren, scrollbareren Bereich.

## 1.7.0-dev.60

- Mailer: API-Felder, unterstützte Verbindungsarten und DSN-Erzeugung stammen aus den jeweiligen Provider-Paketen.

## 1.7.0-dev.59

- Mailer: Provider wählen bei unterstützten Diensten zwischen API und SMTP; die jeweiligen Zugangsdaten werden getrennt und provider-spezifisch gespeichert.

## 1.7.0-dev.58

- Auswahlkacheln: Gewählte Anbieter bleiben weiß, erhalten einen blauen Rahmen und eine kräftig grüne Aktiv-Markierung.

## 1.7.0-dev.57

- Auswahlkacheln: CAPTCHA, Cookie Banner und Mailer kennzeichnen die aktive Auswahl mit einer grünen Statusmarke.

## 1.7.0-dev.56

- Cookie Banner: Anbieter-Kacheln besitzen pro Rasterzeile eine einheitliche Höhe.

## 1.7.0-dev.55

- Mailer: Symfony Mailer und PHPMailer verwenden für SMTP dieselben zentral gespeicherten Zugangsdaten und Absenderangaben.

## 1.7.0-dev.54

- Grundeinstellungen: Die bisherige Mailer- und SMTP-Konfiguration wird ausschließlich im zentralen Mailer gepflegt und erscheint nicht mehr in den Grundeinstellungen.

## 1.7.0-dev.45

- Grundeinstellungen: Die grafische Umgestaltung bleibt auf die Admin-Themes WBCE CMS und MEDIA CMS beschränkt. WBCE Flat Theme und Argos verwenden wieder ihre ursprünglichen Einstellungsseiten und Standardschalter.

## 1.7.0-dev.44

- Bootstrap und Logging: Log-Ereignisse aus Webrequests werden ausschließlich in die lokale Queue geschrieben; der Worker übernimmt den Versand. Dadurch beendet der Error Logger keine Seitenantwort mehr vorzeitig. Die Session funktioniert auch mit älteren Settings-Klassen ohne `getFromDb()`.

## 1.7.0-dev.43

- Direktausgabe: Leere oder nur aus Leerzeichen bestehende Legacy-Ausgaben beenden den Seitenaufruf nicht mehr; Frontend und Backend liefern ihren normalen Inhalt aus.

## 1.7.0-dev.42

- Update-Assistent: Die laufende Paketverarbeitung zeigt einen klar erkennbaren, dunklen Fortschrittshintergrund statt einer leeren weißen Seite.

## 1.7.0-dev.41

- Frontend: Die Legacy-Frontend-Basisklasse stellt `sendDirectOutput()` als kompatiblen Aufruf von `DirectOutput()` bereit; Seitenaufrufe brechen dadurch nicht mehr ab.

## 1.7.0-dev.40

- Grundeinstellungen: Das asynchrone Speichern bestätigt Erfolg oder Fehler als Toast; alle Schalter verwenden die gemeinsame kompakte CMS-Schalterdarstellung.

## 1.7.0-dev.39

- Installation und Update: Die Legacy-Session liest Datenbankwerte über `Settings::getFromDb()`; der fehlerhafte Methodenname `GetDB()` bricht den Abschluss nicht mehr ab.

## 1.7.0-dev.38

- Update-Assistent: Der abschließende Datenbank-Update-Schritt verwendet die Fortschrittskarte der Installation mit weißem Hintergrund, sichtbarem Status und einer scrollbaren vollständigen Änderungsübersicht.

## 1.7.0-dev.37

- Startfehler bei nicht erreichbarer Datenbank liefern im Browser eine sichere Wartungsseite mit HTTP 503 statt einer leeren Fehler-500-Seite; Details bleiben im Server-Protokoll.

## 1.7.0-dev.36

- Zugriffsverwaltung: Benutzer und Gruppen verwenden den etablierten Legacy-Asset-Loader; doppelte Deklarationen der globalen Asset-Helfer treten nicht mehr auf.

## 1.7.0-dev.35

- Admin-Themes: PHP- und Viewport-Angaben sind aus der linken Seitenleiste entfernt.

## 1.7.0-dev.34

- Admin-Themes: Der verlinkte Release-Tag wird nicht mehr in der linken Seitenleiste angezeigt.

## 1.7.0-dev.33

- Admin-Themes: Der vollständige WBCE- und GPL-Lizenzhinweis des originalen 1.7-Themes steht wieder in der linken Navigation.

## 1.7.0-dev.32

- Admin-Themes: Die vollständigen Systeminformationen der ursprünglichen 1.7-Navigation sind wieder in der linken Seitenleiste verfügbar.

## 1.7.0-dev.31

- Admin-Themes: Die Kennzeichnung „WBCE CMS“ in der linken Navigation bleibt vollständig sichtbar.

## 1.7.0-dev.30

- Zugriffsverwaltung: Benutzer und Gruppen laden ihre Legacy-Klassen direkt; der Bereich „Einstellungen ändern“ ist als klarer Einstellungsbereich gestaltet.

## 1.7.0-dev.29

- Secure Form Switcher: sichtbares Speichern und eine zuverlässig geladene Secret-Anzeige.

## 1.7.0-dev.28

- Log Center: eine zuvor als fehlgeschlagen gespeicherte Verbindung blockiert die asynchrone Fehlerübertragung nicht mehr; Einträge bleiben bei einem Ausfall in der Warteschlange.

## 1.7.0-dev.27

- Grundeinstellungen: kompakte Schalter ohne Text und ein funktionsfähiger Schalter für den Papierkorb.

## 1.7.0-dev.26

- Erweiterungsübersicht: Module, Templates und Sprachen verwenden kompakte Karten in einer Dreispalten-Anordnung.

## 1.7.0-dev.25

- Admin-Tools: Löschschalter der Suche, Beschreibungen und Einstellungs-Schalter sind im Admin-Theme sauber ausgerichtet.

## 1.7.0-dev.24

- Legacy-Twig lädt die kompatible Berechtigungsfunktion `has_permission()` wieder; Zugriffs-, Benutzer- und Gruppenansichten können sie verwenden.

## 1.7.0-dev.23

- Admin-Themes: Die Zugriffs-Navigation wird als obere Leiste ausgegeben; die Erweiterungsübersicht zeigt drei Bereiche nebeneinander.

## 1.7.0-dev.22

- Zugriffsverwaltung bindet die Auswahllisten ohne die in der Legacy-Twig-Umgebung nicht verfügbare Funktion `loadPlugin` ein.

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
