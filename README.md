# BlechProfilLengthConfigurator – Alpha 0.1.1

Erste Testversion eines Wunschlängen-Konfigurators für plentyShop LTS / Ceres 5.x.

> **Wichtig beim Wechsel von der ersten SFX-Testversion:** Das GitHub-Repository am besten auf `BlechProfilLengthConfigurator` umbenennen oder ein neues Repository mit diesem Namen verwenden. In PlentyONE die alte Git-Plugin-Verknüpfung `SfxLengthConfigurator` nicht parallel im selben Test-Pluginset installiert lassen.

## Ziel der Alpha

Beispiel-Variantennummern:

- `ERD0204305_1000`
- `ERD0204305_1500`
- `ERD0204305_2000`
- `ERD0204305_4000`

Der gemeinsame Stamm ist `ERD0204305`, der Teil hinter dem letzten Unterstrich wird als Lagerlänge in mm interpretiert.

Gibt der Kunde z. B. `1268 mm` ein und ist ein Sägeschnitt von `4 mm` konfiguriert, benötigt das Plugin mindestens `1272 mm`. Es sucht unter den Varianten desselben Plenty-Artikels nach der kleinsten aktiven Lagerlänge mit ausreichendem **Nettobestand**. In diesem Beispiel wird `ERD0204305_1500` gewählt.

## Was funktioniert

- Anzeige auf der Einzelartikelansicht über `Ceres::SingleItem.BeforeAddToBasket`
- Freischaltung über konfigurierbare Variantennummer-Stämme
- Eingabe Wunschlänge + Menge
- serverseitige Validierung
- automatische Suche nach Geschwistervarianten des gleichen Plenty-Artikels
- Auswertung des Nettobestands über die Plenty VariationStock-Schnittstelle
- Auswahl der kleinsten passenden Lagerlänge
- erneute serverseitige Prüfung beim Warenkorb-Vorgang
- Hinzufügen der tatsächlich ausgewählten Lager-Variation zum Warenkorb
- Übergabe der Wunschlänge über `inputLength`

## Bewusste Einschränkungen der Alpha

1. **Preis:** Der Kunde bezahlt aktuell den normalen Verkaufspreis der ausgewählten Lager-Variation. Eine anteilige Wunschlängen-Preisberechnung ist noch nicht eingebaut.
2. **Mehrfachzuschnitte:** Bei Menge > 1 wird konservativ je Zuschnitt eine Rohmaterial-Variation verwendet. Eine Optimierung wie `1890 + 1900 -> 1 x 4000` ist für Alpha 0.2 vorgesehen.
3. **Reststücke:** Es wird noch kein Reststückbestand erzeugt oder verwaltet.
4. **Routing:** Die Browser-Endpunkte sind für den Default-Shop-Pfad ausgelegt. Bei Sprachpräfixen/Subshops muss das Routing ggf. angepasst werden.
5. **Nummernlogik:** Erwartet wird `STAMM_LAENGE`, wobei `LAENGE` numerisch ist.

## Installation über GitHub / Plenty

1. Den Inhalt dieses Ordners in ein neues GitHub-Repository hochladen. `plugin.json` muss im Repository-Stamm liegen.
2. In PlentyONE unter **Plugins > Git** das Repository hinzufügen.
3. Plugin in das Shop-Pluginset installieren.
4. Plugin-Konfiguration öffnen und zunächst `ERD0204305` als aktiven Nummernstamm belassen.
5. Min-/Max-Länge und Sägeschnitt kontrollieren.
6. In den **Container-Verknüpfungen** prüfen, ob der Provider **Blech + Profil Wunschlängen-Konfigurator** mit `Ceres::SingleItem.BeforeAddToBasket` verknüpft ist. Falls nicht, manuell verknüpfen.
7. Pluginset bereitstellen/deployen.
8. Einen Artikel öffnen, dessen aktuelle Variation z. B. `ERD0204305_1500` lautet.

## Erster sinnvoller Test

Voraussetzung: Varianten desselben Plenty-Artikels mit demselben Stamm und positivem Nettobestand, z. B.:

| Variantennummer | Nettobestand |
|---|---:|
| ERD0204305_1000 | 5 |
| ERD0204305_1500 | 5 |
| ERD0204305_2000 | 5 |
| ERD0204305_4000 | 2 |

Konfiguration: Sägeschnitt 4 mm.

- Wunschlänge `900` -> erwartet `ERD0204305_1000`
- Wunschlänge `1268` -> erwartet `ERD0204305_1500`
- Wunschlänge `1890` -> erwartet `ERD0204305_2000`
- Wunschlänge `1998` -> 2000 reicht wegen 4 mm Sägeschnitt nicht, erwartet nächstgrößere verfügbare Variante

Nach dem Hinzufügen prüfen:

- welche Plenty-Variation im Warenkorb liegt
- ob deren Bestand/Reservierung entsprechend eurer Plenty-Einstellung reagiert
- ob `inputLength` später am Auftrag bzw. in den Auftragspositionsdaten sichtbar/auswertbar ist

## Wichtiger Hinweis

Dies ist absichtlich eine Alpha. Vor produktiver Freischaltung müssen insbesondere Preisberechnung, Auftragsdarstellung und die gewünschte Bestands-/Reservierungswirkung im konkreten Plenty-System geprüft werden.

## Änderungen in Alpha 0.1.1

- Plugin/Namespace auf `BlechProfilLengthConfigurator` umbenannt.
- Alle bisherigen Shop-Bezeichner aus Routen, Twig-Markup und JavaScript entfernt.
- Von Plenty nicht erlaubte PHP-Funktion `method_exists()` vollständig entfernt.
- Variantenmodelle und Bestandsmodelle werden direkt über ihre dokumentierten Properties gelesen.
- IO-Aufruf von `getVariationList()` auf das dokumentierte `getVariationIds()` korrigiert.
