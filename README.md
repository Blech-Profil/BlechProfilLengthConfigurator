# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.1.7

Alpha-Test für plentyShop LTS.

## Sichtbarkeitslogik

Der Konfigurator wird nur gerendert, wenn **beides** passt:

1. Die Plenty Artikel-ID steht unter `Aktive Artikel-IDs`.
2. Die aktuelle Plenty-Variantennummer besitzt exakt einen freigegebenen Stamm vor dem letzten Unterstrich.

Beispiel:

- `ERD0204305_1000` → Stamm `ERD0204305` → sichtbar, wenn `ERD0204305` freigegeben ist.
- `ERD0184305_1000` → Stamm `ERD0184305` → nicht sichtbar.

Damit kann ein Plenty-Artikel wie Artikel 260 viele Durchmesser/Längen enthalten, ohne dass der Konfigurator automatisch bei allen Varianten angezeigt wird.

## Testkonfiguration

- Aktive Artikel-IDs: `260`
- Aktive Variantenstämme: `ERD0204305`
- Minimale Wunschlänge: `50`
- Maximale Wunschlänge: `6000`
- Sägeschnitt: `4`

## Container

`Ceres::SingleItem.BeforeAddToBasket`
