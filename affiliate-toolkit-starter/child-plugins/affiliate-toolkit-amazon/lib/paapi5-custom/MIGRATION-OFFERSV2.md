# PA-API 5 OffersV2 Migration - Änderungsprotokoll

## Datum: 30. Dezember 2025

## Zusammenfassung
Das PA-API 5 PHP SDK wurde von `Offers` auf `OffersV2` migriert, wie in der Amazon PA-API 5 Dokumentation beschrieben:
https://webservices.amazon.com/paapi5/documentation/offersV2.html

## Durchgeführte Änderungen

### 1. SDK Resource-Dateien
Alle `Offers.*` Resource-Konstanten wurden auf `OffersV2.*` aktualisiert:

- ✅ **GetItemsResource.php** - Alle 22 Offers-Konstanten auf OffersV2 geändert
- ✅ **SearchItemsResource.php** - Alle 22 Offers-Konstanten auf OffersV2 geändert  
- ✅ **GetVariationsResource.php** - Alle 22 Offers-Konstanten auf OffersV2 geändert

**Beispiel:**
```php
// ALT:
const OFFERSLISTINGSPRICE = 'Offers.Listings.Price';

// NEU:
const OFFERSLISTINGSPRICE = 'OffersV2.Listings.Price';
```

### 2. Item-Klasse (Item.php)
Die Item-Klasse wurde erweitert, um sowohl `offers` (alt) als auch `offersV2` (neu) zu unterstützen:

#### Hinzugefügte Properties:
- `offersV2` Property hinzugefügt (zusätzlich zu `offers`)
- Beide Properties verwenden den gleichen Typ: `\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Offers`

#### Getter-Methoden mit Fallback-Logik:
```php
// getOffers() - Fallback zu offersV2 wenn offers leer
public function getOffers() {
    if ($this->container['offers'] === null && $this->container['offersV2'] !== null) {
        return $this->container['offersV2'];
    }
    return $this->container['offers'];
}

// getOffersV2() - Fallback zu offers wenn offersV2 leer  
public function getOffersV2() {
    if ($this->container['offersV2'] === null && $this->container['offers'] !== null) {
        return $this->container['offers'];
    }
    return $this->container['offersV2'];
}
```

#### Setter-Methoden:
- `setOffers($offers)` - Setzt das alte offers Property
- `setOffersV2($offersV2)` - Setzt das neue offersV2 Property

### 3. Provider (atkp_shop_provider_amazon.php)
**Keine Änderungen erforderlich!** ✅

Der Provider verwendet bereits `$result->getOffers()`, welches durch die Fallback-Logik automatisch auf `offersV2` zurückfällt, wenn die Amazon API die neue Version zurückgibt.

Verwendete Stellen im Provider:
- Zeile 1107-1125: Preis- und Verfügbarkeitsdaten aus Offers
- Zeile 2062-2100: Detaillierte Offer-Informationen (BuyBoxWinner, Prime, Shipping)

## Abwärts- und Vorwärtskompatibilität

### Szenario 1: Amazon API gibt `Offers` zurück (alt)
- `getOffers()` → Gibt Offers zurück ✅
- `getOffersV2()` → Gibt Offers zurück (Fallback) ✅

### Szenario 2: Amazon API gibt `OffersV2` zurück (neu)
- `getOffers()` → Gibt OffersV2 zurück (Fallback) ✅
- `getOffersV2()` → Gibt OffersV2 zurück ✅

### Szenario 3: Amazon API gibt beide zurück
- `getOffers()` → Gibt Offers zurück
- `getOffersV2()` → Gibt OffersV2 zurück

## Resource-Konstanten

Die folgenden Resource-Konstanten wurden aktualisiert (von `Offers.` zu `OffersV2.`):

```php
// Listings
OffersV2.Listings.Availability.MaxOrderQuantity
OffersV2.Listings.Availability.Message
OffersV2.Listings.Availability.MinOrderQuantity
OffersV2.Listings.Availability.Type
OffersV2.Listings.Condition
OffersV2.Listings.Condition.ConditionNote
OffersV2.Listings.Condition.SubCondition
OffersV2.Listings.DeliveryInfo.IsAmazonFulfilled
OffersV2.Listings.DeliveryInfo.IsFreeShippingEligible
OffersV2.Listings.DeliveryInfo.IsPrimeEligible
OffersV2.Listings.DeliveryInfo.ShippingCharges
OffersV2.Listings.IsBuyBoxWinner
OffersV2.Listings.LoyaltyPoints.Points
OffersV2.Listings.MerchantInfo
OffersV2.Listings.Price
OffersV2.Listings.ProgramEligibility.IsPrimeExclusive
OffersV2.Listings.ProgramEligibility.IsPrimePantry
OffersV2.Listings.Promotions
OffersV2.Listings.SavingBasis

// Summaries
OffersV2.Summaries.HighestPrice
OffersV2.Summaries.LowestPrice
OffersV2.Summaries.OfferCount
```

**Hinweis:** `RentalOffers.*` bleibt unverändert!

## Testing-Empfehlungen

1. **Preisabfragen testen:**
   - GetItems API Aufrufe
   - SearchItems API Aufrufe
   - GetVariations API Aufrufe

2. **Offer-Eigenschaften prüfen:**
   - BuyBox Winner
   - Prime Eligibility
   - Shipping Charges
   - Availability

3. **Resource-Parameter prüfen:**
   - Alle Resources mit `OffersV2.*` in API-Requests verwenden
   - Sicherstellen, dass die API korrekt antwortet

## Vorteile der Migration

✅ **Zukunftssicher**: Amazon migriert zu OffersV2 als Standard
✅ **Abwärtskompatibel**: Bestehender Code funktioniert weiter
✅ **Flexible Implementierung**: Unterstützt beide Versionen gleichzeitig
✅ **Keine Breaking Changes**: Provider-Code benötigt keine Anpassungen

## Bei Problemen

Falls die Amazon API Fehler zurückgibt:
1. Prüfen Sie die verwendeten Resources in den API-Requests
2. Stellen Sie sicher, dass `OffersV2.*` statt `Offers.*` verwendet wird
3. Prüfen Sie die Amazon PA-API Dokumentation auf weitere Änderungen

## Rollback-Möglichkeit

Falls ein Rollback nötig ist, können Sie die Resource-Konstanten wieder auf `Offers.*` ändern:

```bash
# In den drei Resource-Dateien:
sed -i '' 's/OffersV2\./Offers\./g' GetItemsResource.php
sed -i '' 's/OffersV2\./Offers\./g' SearchItemsResource.php  
sed -i '' 's/OffersV2\./Offers\./g' GetVariationsResource.php
```

Die Item.php Änderungen sollten jedoch beibehalten werden für maximale Kompatibilität.

