# Blech + Profil Wunschlängen-Konfigurator – Alpha 0.1.9

Testversion für plentyShop LTS.

## Freigabe über Varianten-Eigenschaft

Der Konfigurator wird nur angezeigt, wenn die aktuell gewählte Variante folgende Kombination besitzt:

- Eigenschafts-ID: **48** (`Zuschnitt möglich`)
- Auswahl-ID: **71** (`Ja`)

Auswahl-ID **72** (`Nein`) oder eine fehlende Eigenschaft blendet den Konfigurator aus.

Die IDs sind in der Plugin-Konfiguration änderbar. Standardwerte in Alpha 0.1.9 sind bereits 48 und 71.

## Variantenwechsel

Alpha 0.1.9 liest die aktuelle Varianten-ID bevorzugt direkt aus dem plentyShop-LTS Vue/Vuex-Store und reagiert zusätzlich auf `onVariationChanged`. Damit soll die Freigabe ohne F5 direkt nach einem Variantenwechsel neu geprüft werden.

## Längenlogik

Die tatsächliche Rohmaterialauswahl bleibt über das Variantennummer-Muster `STAMM_LAENGE` aufgebaut, z. B. `ERD0204305_1000`.

Beispiel bei 4 mm Sägeschnitt:

- Wunschlänge 1268 mm
- Bedarf 1272 mm
- kleinste passende verkaufbare Lagerlänge z. B. `ERD0204305_1500`

Alpha-Status: Die exakte Nettobestands-/Mehrfachzuschnittoptimierung folgt nach erfolgreichem Sichtbarkeits- und Variantentest.
