# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.1.3

Test-Plugin für plentyShop LTS.

## Alpha 0.1.3

- Primärschlüssel im Frontend: Plenty Artikel-ID + Varianten-ID.
- Standard-Testartikel: Artikel-ID `260`.
- Die Variantennummer wird serverseitig anhand der Varianten-ID geladen.
- Nummernstamm `ERD0204305` wird weiterhin zur Gruppierung kompatibler Lagerlängen verwendet.
- Beispiel: Variante `14665` -> `ERD0204305_1000`.
- Wunsch 1268 mm + 4 mm Sägeschnitt -> kleinste passende aktive Lagerlänge mit Nettobestand, z. B. `ERD0204305_1500`.
- Script ist separat an `Ceres::SingleItem.AfterScriptsLoaded` angebunden.

## Konfiguration

- Aktive Artikel-IDs: `260`
- Aktive Nummernstämme: `ERD0204305`
- Min.: `50 mm`
- Max.: `6000 mm`
- Sägeschnitt: `4 mm`

## Noch nicht enthalten

- individuelle Preisberechnung nach Wunschlänge
- Mehrschnittoptimierung wie 1890 + 1900 -> 1x 4000
- Reststückverwaltung
