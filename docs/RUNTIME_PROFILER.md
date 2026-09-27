# Laufzeitprotokoll

Das Laufzeitprotokoll wird im Admin-Tool **Error Logger** aktiviert. Es ist im
Normalbetrieb ausgeschaltet und verursacht dann keinen Mess- oder Schreibaufwand.

- **Laufzeitprotokoll** schreibt nur langsame Requests und Schritte. Der
  Standard-Schwellwert beträgt 250 ms.
- **Detaillierter Laufzeit-Trace** schreibt zusätzlich jeden gemessenen Schritt.
  Er ist für eine kurze Fehlersuche gedacht und sollte danach wieder deaktiviert
  werden.

Das Log liegt unter `var/logs/runtime.log.php` und kann im Error Logger über die
Logdateiauswahl angezeigt werden. Jeder Eintrag hat eine Request-ID. Damit lassen
sich alle Schritte eines Seitenaufrufs zusammenfassen.

Erfasst werden der Framework-Start, das Laden der Einstellungen, `preinit.php`
und `initialize.php` jedes aktivierten Moduls sowie Ausführungen der WBCE-Hooks.
Im Request-Abschluss stehen Laufzeit, maximaler Speicherverbrauch und die Schritte,
die den Schwellwert überschritten haben. Die URL wird ohne Query-Parameter
gespeichert.
