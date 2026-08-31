# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.2.0

Diese Alpha schaltet den Konfigurator über eine Plenty-Auswahl-Eigenschaft frei.

## Testkonfiguration

- Eigenschaft `Zuschnitt möglich`: ID **48**
- Auswahl `Ja`: ID **71**
- Auswahl `Nein`: ID **72**
- Variante 14665 soll für den Test die Auswahl **71 = Ja** besitzen.

Im Plugin eintragen:

- Eigenschafts-ID: `48`
- Ja-Auswahl-ID: `71`
- Minimale Wunschlänge: `50`
- Maximale Wunschlänge: `6000`
- Sägeschnitt: `4`

## Änderung in 0.2.0

Die Freigabe wird nicht mehr dadurch geprüft, dass rekursiv in `ItemService::getVariation()` nach Property-Daten gesucht wird. Stattdessen nutzt das Plugin die Plenty PIM VariationDataInterface mit zwei Filtern:

1. konkrete Varianten-ID
2. Property-Selection-ID 71

Zusätzlich wird geprüft, ob Auswahl 71 tatsächlich zur Eigenschaft 48 gehört.

## Diagnose

Normalerweise bleibt die Diagnose unsichtbar. Für den Test kann an die Artikel-URL angehängt werden:

`?bpdebug=1`

Dann zeigt das Plugin auch dann einen Diagnosekasten, wenn der Konfigurator nicht freigegeben wird. Dort stehen Varianten-ID, Variantennummer, Eigenschaft, Auswahl und die genaue Servermeldung.

## Sichtbarkeit der Eigenschaft

Für die interne Freigabelogik muss die Eigenschaft nicht als sichtbare Kundeninformation auf der Artikelseite dargestellt werden. Sie dient nur als Steuermerkmal an der Variante.
