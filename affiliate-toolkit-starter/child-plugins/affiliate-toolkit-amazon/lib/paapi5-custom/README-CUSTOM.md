# PA-API 5 PHP SDK - Custom Version

## Wichtig: Customized Fork

Dies ist eine angepasste Version des Original PA-API 5 PHP SDK von drunomics.
Das Package wurde aus dem Composer Vendor-Verzeichnis herausgelöst, um umfangreiche 
Anpassungen vornehmen zu können.

## Original Package
- **Original**: `drunomics/paapi5-php-sdk`
- **Version**: 1.1.2
- **Lizenz**: Apache-2.0

## Änderungen gegenüber dem Original
- Datum: 30.12.2025
- Herausgelöst aus Composer Vendor Management
- Lokale Integration über PSR-4 Autoloading

## Verwendung

Das SDK wird automatisch über den PSR-4 Autoloader geladen:
```php
require_once __DIR__ . '/vendor/autoload.php';

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
// ... weitere Klassen
```

Der Namespace bleibt identisch: `Amazon\ProductAdvertisingAPI\v1`

## Anpassungen vornehmen

Sie können jetzt alle Dateien im `src/` Verzeichnis nach Bedarf anpassen.
Nach Änderungen sollte der Autoloader neu generiert werden:

```bash
cd lib
composer dump-autoload
```

## Wichtige Verzeichnisse
- `src/` - SDK Quellcode (hier können Anpassungen vorgenommen werden)
- `src/com/amazon/paapi5/v1/` - Hauptklassen (Item, SearchItemsRequest, etc.)
- `src/com/amazon/paapi5/v1/api/` - API Klassen
- `src/com/amazon/paapi5/v1/auth/` - Authentication Helper

## Rückkehr zum Original Package

Sollten Sie später zum Original Package zurückkehren wollen:

1. Verzeichnis `paapi5-custom/` löschen
2. `composer.json` anpassen:
   ```json
   {
       "require": {
           "drunomics/paapi5-php-sdk": "^1.0"
       }
   }
   ```
3. `composer install` ausführen

