# Release Notes — Angebotskalkulator

**Version 1.1 · 20. April 2026**
Funkwerk Mobility Solutions — Branch `claude/quote-calculator-tool-Js6g2`

---

## Highlights dieses Releases

- **GAEB-LV Import (Phase 1a)** — X83-Dateien direkt einlesen und als Angebotsgerüst übernehmen.
- **Zwischensummen** zwischen Überschriften mit vollständiger Aggregation je Kostenart.
- **CEO-Ansicht** um **Excel- und Word-Export** erweitert; erweiterte CEO-Ansicht als einseitiges A4-PDF.
- **Produktkatalog** überarbeitet: neue Kategorien, Materialnummer, einklappbar.
- **Self-Service Passwortänderung** für alle Benutzer.
- Erstes **Benutzerhandbuch** für Vertriebsmitarbeiter (`BENUTZERHANDBUCH.md`).

---

## Neue Funktionen

### Zwischensummen in der Kalkulation

- Neuer Button **„Zwischensumme einfügen"** unterhalb der Tabelle.
- Eine Zwischensumme aggregiert **alle Überschriften seit der vorherigen Zwischensumme** (bzw. seit Kalkulationsbeginn).
- Summiert je Kostenart (Eigenmaterial, Fremdmaterial, Fremdleistung, Sonderkosten, Stunden) sowie HK Gesamt, VP Gesamt und Deckung %.
- Per ↑ / ↓ zwischen Gruppen verschiebbar, per Papierkorb löschbar.
- Wird im Angebots-JSON mitgespeichert (`zwischensummen`-Array), abwärtskompatibel.

### CEO-Ansicht: Excel- und Word-Export

- Zwei neue Buttons unten in der CEO-Ansicht: **Excel-Export** (`.xls`) und **Word-Export** (`.doc`).
- Umsetzung als HTML-Blob ohne externe Abhängigkeiten / CDN.
- Exportiert nur **sichtbare Spalten** (Toolbar-Auswahl) und **ausgeklappte Gruppen**.
- Summenzeile wird aus den exportierten Werten neu berechnet.
- Excel: `mso-number-format` für Zahlen-Spalten, damit Zellen als numerisch erkannt werden.

### Erweiterte CEO-Ansicht — 1-Seiten-PDF auf A4

- Print-CSS komplett überarbeitet: `@page size: A4 portrait; margin: 10mm`.
- Kompaktere Paddings, Tabellenschrift 8,5 pt, Sektionen 7,5 pt.
- Farbige Sektionen (Umsatz, HK, DB, GK, Gewinn) mit `print-color-adjust: exact`.
- `page-break-inside: avoid` pro Tabellenzeile — kein Zerreißen.
- Prozent-Inputs im Druckmodus randlos/transparent — sehen wie Textwerte aus.

### GAEB-LV Import (X83) — Phase 1a

- Neuer Button **„GAEB-LV einlesen"** auf der Startseite.
- Client-seitiger XML-Parser (`DOMParser`, namespace-agnostisch).
- Extrahiert: Projektname (`NamePrj`), Kategorie/Titel (`BoQCtgy`), Ordnungszahl (`RNoPart`-Hierarchie), Kurztext (`OutlineText` / `ShortText`), Menge (`Qty`), Einheit (`QU`).
- Vorschau-Tabelle mit:
  - Checkbox pro Zeile + „Alle auswählen/abwählen"
  - ↑ / ↓ zum Umsortieren
  - Projekt- und Positionszähler
- **Mapping in die Kalkulation** (aktualisiert in diesem Release):
  - Jede GAEB-Position wird eine eigene **Überschrift**
    - `hpos` = OZ, Titel = Kurztext `[Einheit]`, Gruppen-Stück = Menge
  - Darunter automatisch **eine Default-Unterposition mit Kostenstruktur „Material"**
- Legt grundsätzlich ein **neues Angebot** an (Meta-Daten werden vorbelegt).
- **Noch nicht enthalten**: `.D83` (Flat-File) und D.84-Rückexport — geplant für Phase 2.

### Eigenes Passwort ändern (Self-Service)

- Neuer Backend-Endpoint **`auth.php?action=changeOwnPassword`** (prüft altes Passwort, min. 6 Zeichen).
- Link **„Passwort ändern"** in allen Topbars (Startseite, Admin-Portal, Kalkulator, GAEB-View).
- Modal mit drei Feldern + clientseitiger Validierung (Übereinstimmung, Mindestlänge, Verschiedenheit).
- Bei Erfolg Toast „Passwort erfolgreich geändert".
- Die Admin-Funktion (Passwort für andere setzen) bleibt unverändert erhalten.

### Produktkatalog überarbeitet

- **Datenmodell** (DB, `catalog`-Tabelle): neue Spalte `materialnr` (idempotente Migration in `install.php`).
- **Kategorien**:
  - „Hardware" → **„Displays"** (Kostenstruktur „Material") — Migration per `install.php`.
  - Neu: **„Beschallung"** (Kostenstruktur „Material").
  - „SW-Lizenzen" (frei wählbar) und „Sonstiges" unverändert.
- **Admin-Katalog**:
  - Ganzer Bereich **einklappbar** (`<details>`) für bessere Übersicht.
  - Spalte **„#"** entfernt.
  - Neue Spalte **„Materialnr."** (freier Text, auch leer erlaubt).
  - Admin-Bereich auf 1180 px verbreitert.
- **CSV-Import**:
  - Neues Format: `Materialnr;Beschreibung;HK;VK;Kategorie` — Materialnr und Kategorie optional.
  - Auto-Erkennung, ob die CSV eine Materialnr-Spalte enthält.
  - Header mit „Material"/„Beschreibung"/... wird erkannt.
  - **Bestehende Einträge werden ergänzt, nicht überschrieben.**
- **Katalog-Modal in der Kalkulation** zeigt die Materialnr jetzt mit an.

### Benutzerhandbuch (erste Fassung)

- Neue Datei **`BENUTZERHANDBUCH.md`** — 10 Kapitel für Vertriebsmitarbeiter.
- Themen: Anmeldung, Passwortänderung, Angebotsanlage, Kostenstrukturen, Zwischensummen, Produktkatalog, GAEB-Import, CEO-Ansichten + Export, Drucken, Admin-Bereich, FAQ.
- Platzhalter für Screenshots (nachträglich ergänzbar).

---

## Verbesserungen & Bugfixes

### UI

- **Tabellen-Header** der Kalkulation: `white-space: nowrap` → `normal` mit `line-height: 1.2` und `overflow-wrap`. Spaltentitel wie „Eigenmaterial €" laufen nicht mehr in Nachbarspalten über. `&nbsp;` durch reguläres Leerzeichen ersetzt, damit natürlicher Umbruch möglich ist.
- **Startseite**: Button **„Angebote anzeigen"** entfernt (die Liste der gespeicherten Angebote bleibt weiterhin unterhalb der Buttons sichtbar).

---

## Datenbank-Migration

Damit die neuen Funktionen in bestehenden Installationen funktionieren, muss einmalig **`install.php`** im Browser aufgerufen werden. Das Script ist idempotent — mehrfaches Ausführen ist unkritisch.

Migrationen in diesem Release:

| Tabelle | Änderung |
|---|---|
| `catalog` | Neue Spalte `materialnr VARCHAR(100) NOT NULL DEFAULT ''` |
| `catalog` | `UPDATE … SET kategorie = 'Displays' WHERE kategorie = 'Hardware'` |

> **Wichtig:** `install.php` nach der Migration wieder vom Server entfernen.

---

## Geänderte Dateien

| Datei | Änderung |
|---|---|
| `index.html` | Feature-Arbeit (Zwischensummen, CEO-Export, GAEB-View, Passwortänderung, Katalog-UI, Header-Fix) |
| `api/auth.php` | Neuer Endpoint `changeOwnPassword` |
| `api/catalog.php` | `materialnr` in list/save/bulkImport |
| `install.php` | Schema-Migration für `materialnr` und Kategorie-Umbenennung |
| `BENUTZERHANDBUCH.md` | **Neu**: Anleitung für Vertriebsmitarbeiter |
| `RELEASE_NOTES.md` | **Neu**: diese Datei |

---

## Bekannte Einschränkungen

- **GAEB-Flatfile (`.D83`) und D.84-Rückexport** sind noch nicht enthalten (Phase 2 geplant).
- **CSV-Import im Produktkatalog** ergänzt nur – ein Update bestehender Einträge per Materialnummer ist noch nicht implementiert.
- **CEO-Excel-Export** erzeugt eine `.xls`-Datei (HTML-Blob); beim Öffnen in neuen Excel-Versionen erscheint evtl. ein Sicherheitshinweis. Für echte `.xlsx` wäre SheetJS nötig.
- **GAEB-Import** liest keinen Langtext aus (`DetailTxt`); nur der Kurztext wird übernommen.

---

## Commits in diesem Release (chronologisch)

| Commit | Kurzbeschreibung |
|---|---|
| `aec7d70` | Zwischensummen-Feature |
| `5dc2d35` | Tabellen-Header: Umbruch korrigiert |
| `d39f995` | Self-Service Passwortänderung |
| `8f66384` | CEO-Ansicht: Excel-/Word-Export |
| `8e1f8ce` | Erweiterte CEO-Ansicht: 1-Seiten-A4-PDF |
| `7823165` | GAEB-LV X83-Import (Phase 1a) |
| `864a5e8` | Button „Angebote anzeigen" entfernt |
| `1fb7f09` | GAEB-Import Mapping: Positionen werden Überschriften |
| `730020c` | Produktkatalog überarbeitet (Materialnr, Displays, Beschallung, einklappbar) |
| `fed4ef2` | Benutzerhandbuch |

---

_Feedback und Änderungswünsche bitte an das Projektteam._
