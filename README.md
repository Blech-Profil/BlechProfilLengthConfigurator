# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.1.6

## Zweck dieser Alpha

Diese Version ist absichtlich **nur für Plenty Artikel-ID 260** freigeschaltet. Der Konfigurator ist standardmäßig ausgeblendet und wird per JavaScript nur eingeblendet, wenn die aktuelle Artikel-URL die Artikel-ID `260` enthält und `260` zusätzlich in der Plugin-Konfiguration freigegeben ist.

Testartikel:
- Artikel-ID: `260`
- Ausgangs-Varianten-ID: `14665`
- bekannte Variantennummer: `ERD0204305_1000`

## Warum 0.1.6?

In 0.1.4 wurde der Konfigurator im HTML zunächst sichtbar gerendert und anschließend per JavaScript ausgeblendet. 0.1.6 arbeitet umgekehrt (fail-closed): standardmäßig `display:none`; nur Artikel 260 wird sichtbar. Auf allen anderen Artikeln wird das Element aus dem DOM entfernt.

## Container

Nur erforderlich:

`Ceres::SingleItem.BeforeAddToBasket` → `Blech + Profil Wunschlängen-Konfigurator`

## Test

Auf Artikel 260 sollte unten im Kasten stehen:

`Alpha 0.1.6 · Artikel-ID 260 · Varianten-ID 14665`

Auf jedem anderen Artikel darf der Kasten überhaupt nicht erscheinen.

Anschließend kann mit `1268 mm` die Auswahl der passenden Lagerlänge getestet werden.


## Alpha 0.1.6
Der Container rendert jetzt serverseitig nur für freigeschaltete Plenty-Artikel-IDs. Die aktuelle Artikel-/Varianten-ID wird primär aus der Request-URI (z. B. `_260_14665`) gelesen; das Container-Objekt dient nur als Fallback.
