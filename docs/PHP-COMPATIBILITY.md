# PHP-Kompatibilität in WBCE 2

Der WBCE-2-Kern wird gegen PHP 8.5 entwickelt. Die Mindestversion ist PHP 8.2.
PHP 8.6 wird erst nach einer stabilen Veröffentlichung zur neuen Basis.

Alle Sonderwege für PHP 8.2, 8.3 und 8.4 liegen in
`framework/Php82Compatibility.php`. Außerhalb dieser Datei verwendet der Kern
keine nachgebildeten PHP-Funktionen.

## Entfernbare Bereiche

- PHP 8.2 und 8.3: Rückfälle für `array_find()`, `mb_ucfirst()` und
  `http_get_last_response_headers()`.
- PHP 8.2 bis 8.4: explizite Freigabe älterer cURL-, FileInfo-, GD- und
  XML-Ressourcen. Unter PHP 8.5 übernimmt PHP deren Objekt-Lebenszyklus.

Nach Anhebung der Mindestversion können die jeweils markierten Methoden und
ihre Aufrufe ohne Änderung der Geschäftslogik entfernt werden.
