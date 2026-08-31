# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.1.5

## Zweck dieser Alpha

Diese Version ist absichtlich **nur für Plenty Artikel-ID 260** freigeschaltet. Der Konfigurator ist standardmäßig ausgeblendet und wird per JavaScript nur eingeblendet, wenn die aktuelle Artikel-URL die Artikel-ID `260` enthält und `260` zusätzlich in der Plugin-Konfiguration freigegeben ist.

Testartikel:
- Artikel-ID: `260`
- Ausgangs-Varianten-ID: `14665`
- bekannte Variantennummer: `ERD0204305_1000`

## Warum 0.1.5?

In 0.1.4 wurde der Konfigurator im HTML zunächst sichtbar gerendert und anschließend per JavaScript ausgeblendet. 0.1.5 arbeitet umgekehrt (fail-closed): standardmäßig `display:none`; nur Artikel 260 wird sichtbar. Auf allen anderen Artikeln wird das Element aus dem DOM entfernt.

## Container

Nur erforderlich:

`Ceres::SingleItem.BeforeAddToBasket` → `Blech + Profil Wunschlängen-Konfigurator`

## Test

Auf Artikel 260 sollte unten im Kasten stehen:

`Alpha 0.1.5 · Artikel-ID 260 · Varianten-ID 14665`

Auf jedem anderen Artikel darf der Kasten überhaupt nicht erscheinen.

Anschließend kann mit `1268 mm` die Auswahl der passenden Lagerlänge getestet werden.
