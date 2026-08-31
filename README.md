# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.1.4

Diagnose-/Funktionsalpha für plentyShop LTS.

## Wichtigste Änderung gegenüber 0.1.3

Die JavaScript-Logik wird **direkt zusammen mit dem sichtbaren Konfigurator** in `Ceres::SingleItem.BeforeAddToBasket` geladen. Eine zweite Verknüpfung mit `Ceres::SingleItem.AfterScriptsLoaded` ist nicht mehr nötig.

Der Testartikel wird primär über die URL-IDs erkannt:

- Artikel-ID: `260`
- Beispiel-Varianten-ID: `14665`
- Beispiel-Variantennummer: `ERD0204305_1000`

## Test

Auf dem Artikel 260 sollte vor dem Warenkorb-Button ein Kasten erscheinen. Unten steht zur Kontrolle z. B.:

`Alpha 0.1.4 · Artikel-ID 260 · Varianten-ID 14665`

Bei 1268 mm und 4 mm Sägeschnitt soll die kleinste passende verkaufbare Variante gewählt werden, z. B. `ERD0204305_1500`.

## Hinweis zum Bestand

Alpha 0.1.4 verwendet zunächst `IO\Services\ItemService::getVariationIsSalable()` statt einer exakten Lagerbestandsabfrage. So testen wir zuerst zuverlässig Container, ID-Erkennung, Routing und Variantenauswahl. Die exakte Nettobestandsmenge und spätere Zuschnittoptimierung werden danach ergänzt.

## Plugin-Reihenfolge

Wenn der Container trotz aktiver Verknüpfung nicht erscheint, im Plugin-Set die Reihenfolge prüfen. Für Erweiterungs-/Theme-Plugins empfiehlt Plenty eine Position zwischen IO und plentyShop LTS/Ceres, z. B. IO 999, dieses Plugin 998, Ceres 997.
