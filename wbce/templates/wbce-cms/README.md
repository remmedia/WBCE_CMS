# WBCE CMS 1.0.6

Eigenständiges WBCE-Admin-Theme für WBCE 1.6.8 und 1.7.x. Das Paket enthält seine komplette technische Basis, benötigt weder ein anderes Backend-Theme noch Netzwerkzugriff und verwendet ausschließlich Laufzeitpfade unter `templates/wbce-cms`.

Twig ist die primäre Templatebasis. WBCE 1.7 verwendet die mitgelieferten nativen Twig-Ansichten für Login, Benutzer- und Gruppenverwaltung, Einstellungen und Admin-Tools. Die isolierten HTT-Dateien sind ausschließlich die rückwärtskompatible Laufzeitschicht für jene WBCE-1.6.8-Seiten, deren Core-Controller noch keine Twig-Ansicht aufrufen.

Der WBCE-Projektlink und der Copyright-Hinweis sind fester Bestandteil der Administration und bleiben immer sichtbar.

## Struktur

- `api/`: lokale Logo-, Favicon- und Systeminfo-Endpunkte
- `css/`: eingebettete Basis und WBCE-CMS-Designschicht
- `fonts/`, `images/`, `js/`: vollständig lokale Assets
- `languages/`, `patch/`, `templates/`: WBCE-Integration und Backend-Templates
- `LICENSES/`: GPLv3- und Copyright-Nachweise der übernommenen Basis

`js/media-cms.js` speichert geeignete, nicht-destruktive POST-Formulare sowie typische Status-, Toggle- und Aktivierungsaktionen progressiv per `fetch`. Installieren, Deinstallieren, Löschen, Uploads, Umbenennen sowie Speichern-und-zurück bleiben beim normalen Browserablauf. Formulare können mit `data-no-ajax` ausgenommen oder mit `data-media-cms-ajax="true"` ausdrücklich aktiviert werden.

Nach dem Anlegen einer neuen Seite wird die Seitenübersicht automatisch im Hintergrund neu abgerufen, im DOM ersetzt und der Seitenbaum erneut initialisiert.

Editorinhalte von CKEditor und TinyMCE werden vor dem AJAX-Speichern ausdrücklich in die zugehörigen Formularfelder synchronisiert. Modul-eigene Submit-Handler laufen vor der WBCE-CMS-Verarbeitung.
