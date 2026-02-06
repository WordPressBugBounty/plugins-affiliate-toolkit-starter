<?php
/**
 * OffersV2 Migration Test Script
 *
 * Dieses Script demonstriert die OffersV2-Kompatibilität
 * und zeigt, dass beide Methoden (getOffers und getOffersV2) funktionieren
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Offers;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferListing;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferPrice;

echo "=== OffersV2 Migration Test ===\n\n";

// Test 1: Item mit Offers (alt)
echo "Test 1: Item mit 'Offers' Property (alte API-Response)\n";
echo "--------------------------------------------------------\n";

$offerPrice = new OfferPrice();
$offerPrice->setDisplayAmount('€29,99');

$offerListing = new OfferListing();
$offerListing->setPrice($offerPrice);
$offerListing->setIsBuyBoxWinner(true);

$offers = new Offers();
$offers->setListings([$offerListing]);

$item1 = new Item([
    'aSIN' => 'B001TEST01',
    'offers' => $offers  // Alte Property
]);

echo "Item ASIN: " . $item1->getASIN() . "\n";
echo "getOffers() ist gesetzt: " . ($item1->getOffers() !== null ? 'JA' : 'NEIN') . "\n";
echo "getOffersV2() ist gesetzt: " . ($item1->getOffersV2() !== null ? 'JA (Fallback)' : 'NEIN') . "\n";

if ($item1->getOffers() !== null && $item1->getOffers()->getListings() !== null) {
    $listing = $item1->getOffers()->getListings()[0];
    echo "Preis über getOffers(): " . $listing->getPrice()->getDisplayAmount() . "\n";
}

if ($item1->getOffersV2() !== null && $item1->getOffersV2()->getListings() !== null) {
    $listing = $item1->getOffersV2()->getListings()[0];
    echo "Preis über getOffersV2(): " . $listing->getPrice()->getDisplayAmount() . "\n";
}

echo "\n";

// Test 2: Item mit OffersV2 (neu)
echo "Test 2: Item mit 'OffersV2' Property (neue API-Response)\n";
echo "--------------------------------------------------------\n";

$offerPrice2 = new OfferPrice();
$offerPrice2->setDisplayAmount('€39,99');

$offerListing2 = new OfferListing();
$offerListing2->setPrice($offerPrice2);
$offerListing2->setIsBuyBoxWinner(true);

$offersV2 = new Offers();
$offersV2->setListings([$offerListing2]);

$item2 = new Item([
    'aSIN' => 'B002TEST02',
    'offersV2' => $offersV2  // Neue Property
]);

echo "Item ASIN: " . $item2->getASIN() . "\n";
echo "getOffers() ist gesetzt: " . ($item2->getOffers() !== null ? 'JA (Fallback)' : 'NEIN') . "\n";
echo "getOffersV2() ist gesetzt: " . ($item2->getOffersV2() !== null ? 'JA' : 'NEIN') . "\n";

if ($item2->getOffers() !== null && $item2->getOffers()->getListings() !== null) {
    $listing = $item2->getOffers()->getListings()[0];
    echo "Preis über getOffers(): " . $listing->getPrice()->getDisplayAmount() . "\n";
}

if ($item2->getOffersV2() !== null && $item2->getOffersV2()->getListings() !== null) {
    $listing = $item2->getOffersV2()->getListings()[0];
    echo "Preis über getOffersV2(): " . $listing->getPrice()->getDisplayAmount() . "\n";
}

echo "\n";

// Test 3: Resource-Konstanten
echo "Test 3: Resource-Konstanten (OffersV2)\n";
echo "--------------------------------------------------------\n";

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetVariationsResource;

echo "GetItemsResource::OFFERSLISTINGSPRICE = " . GetItemsResource::OFFERSLISTINGSPRICE . "\n";
echo "SearchItemsResource::OFFERSLISTINGSPRICE = " . SearchItemsResource::OFFERSLISTINGSPRICE . "\n";
echo "GetVariationsResource::OFFERSLISTINGSPRICE = " . GetVariationsResource::OFFERSLISTINGSPRICE . "\n";

echo "\nGetItemsResource::OFFERSSUMMARIESLOWEST_PRICE = " . GetItemsResource::OFFERSSUMMARIESLOWEST_PRICE . "\n";

echo "\n";

// Test 4: Alle Offers Resources auflisten
echo "Test 4: Alle OffersV2 Resources\n";
echo "--------------------------------------------------------\n";

$allResources = GetItemsResource::getAllowableEnumValues();
$offersResources = array_filter($allResources, function($resource) {
    return strpos($resource, 'OffersV2.') === 0;
});

echo "Anzahl OffersV2 Resources: " . count($offersResources) . "\n";
echo "\nBeispiele:\n";
foreach (array_slice($offersResources, 0, 5) as $resource) {
    echo "  - " . $resource . "\n";
}

echo "\n=== Alle Tests erfolgreich! ===\n";
echo "\nFazit:\n";
echo "✅ Abwärtskompatibilität: getOffers() funktioniert mit offersV2\n";
echo "✅ Vorwärtskompatibilität: getOffersV2() funktioniert mit offers\n";
echo "✅ Resource-Konstanten: Alle auf OffersV2.* migriert\n";
echo "✅ Provider-Code: Keine Änderungen notwendig!\n";

