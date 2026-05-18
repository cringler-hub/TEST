# Release Notes — Angebotskalkulator

**Version 1.0 · Launch 01.06.2026**
Funkwerk Mobility Solutions GmbH

---

## Was ist neu in V1.0

Diese Release Notes fassen alle Funktionen zusammen, die bis zum offiziellen Launch am 01.06.2026 in den Angebotskalkulator integriert wurden.

### Update vom 18.05.2026 — finale Vorbereitung für den Launch

Eine größere Aktualisierungswelle mit über 15 Verbesserungen direkt vor dem Launch:

#### Versionierung von Angeboten
- **Version einfrieren** — Snapshot des aktuellen Stands mit Kommentar und Bearbeiter. Fortlaufende Versionsnummer V1, V2, V3 … je Angebot.
- **Versionsliste** zum Einsehen aller eingefrorenen Stände. Klick auf „Anzeigen" öffnet eine alte Version im Read-Only-Modus mit gelbem Banner; „Zurücksetzen" übernimmt eine Version als neuen Entwurf (mit Sicherheitsabfrage).
- Im Kalkulator zeigt ein Info-Banner immer den aktuellen Status und die letzte eingefrorene Version mit Datum und Bearbeiter.

#### Status pro Angebot
- Neues Feld **Status** im Angebotskopf: Entwurf · Eingereicht · Beauftragt · Verloren · Archiviert.
- Direkt auch in der Angebotsliste auf der Startseite änderbar.
- Liste wird automatisch nach Status gruppiert; Sektionen einklappbar mit Zähler.

#### Angebote für andere Benutzer freigeben (Lesen)
- Neuer Button **Freigeben** im Kalkulator. Modal mit Benutzer-Dropdown.
- Empfänger sehen das Angebot in ihrer Liste mit 📥-Badge.
- Ersteller sieht 👥+Anzahl bei eigenen freigegebenen Angeboten.
- Empfänger öffnen das Angebot **read-only**: Eingaben gesperrt; Drucken, Export, CEO-Ansicht, Versionen einsehen erlaubt.

#### Langtext pro Position
- Neuer Symbol-Button 📝 in der Aktionsleiste jeder Unterposition.
- Klick öffnet eine Textarea unterhalb der Zeile für ausführliche Details.
- Icon färbt sich lila, sobald Text drinsteht — Übersicht auch im eingeklappten Zustand.
- Langtext wandert beim Verschieben, Kopieren und Löschen automatisch mit.
- **Im Druck/PDF** werden alle Langtext-Zeilen mit Inhalt automatisch sichtbar (zurückhaltend formatiert).
- **CEO-Ansicht (Standard)** zeigt Langtext als graue Detail-Zeile unter der Position.
- **GAEB-Import** liest `DetailTxt` aus X83 (mit Erhaltung der Absatzstruktur) und füllt ihn in die Default-Unterposition.

#### VP Einzel manuell anpassbar
- Verkaufspreis pro Position ist jetzt **direkt editierbar**.
- Bei manueller Änderung wird die **Deckung automatisch neu berechnet** (`Deckung = (VP − HK) / VP × 100`, geclampt 0–99 %).
- Übrige Eingaben (Material, Stunden, Stück, Deckung) aktualisieren VP wie gewohnt.
- Für SW-Lizenzen wirkt die VP-Eingabe direkt auf das Kaufpreis-Feld zurück.

#### Schutz vor Datenverlust
- **Speichern-Erinnerung** beim Verlassen des Kalkulators (Startseite/Abmelden): Modal mit drei Optionen — Abbrechen, Ohne Speichern verlassen, Speichern und verlassen.
- **Browser-Warnung** beim Schließen, Reload oder URL-Wechsel mit ungespeicherten Änderungen.
- Echtes Dirty-Flag pro Kalkulator-Eingabe — Warnungen kommen nur bei tatsächlichen Änderungen.

#### Bearbeiter wird automatisch gesetzt
- Bei Neuanlage und beim Öffnen eines Angebots wird das **Bearbeiter-Feld automatisch mit dem angemeldeten Benutzer** befüllt.
- Manuelles Überschreiben bleibt möglich.

#### CEO-Ansichten erweitert
- **Datum und Bearbeiter** in der Header-Zeile beider CEO-Ansichten ergänzt (deutsches Datumsformat DD.MM.YYYY).
- Erweiterte CEO-Ansicht zeigt Datum + Bearbeiter zusätzlich als eigene Zeilen im Projekt-Block.

#### Kleine Verbesserungen
- **Kopieren einer Position** übernimmt jetzt auch das Stunden-Feld (Bug-Fix).
- **Branding** im Header auf „Funkwerk Mobility Solutions GmbH" geändert; Versions- und Launch-Datum-Zeile **V1.0 · 01.06.2026** überall ergänzt.

---

### Update vom 20.04.2026

- **GAEB-LV Import (Phase 1a)** — X83-Dateien direkt einlesen und als Angebotsgerüst übernehmen. Jede GAEB-Position wird zu einer eigenen Überschrift, darunter eine Default-Unterposition mit Kostenstruktur „Material".
- **Zwischensummen** zwischen Überschriften mit vollständiger Aggregation je Kostenart (Eigenmaterial, Fremdmaterial, Fremdleistung, Sonderkosten, Stunden, HK Gesamt, VP Gesamt, Deckung %).
- **CEO-Ansicht** um **Excel- und Word-Export** erweitert (HTML-Blob ohne Abhängigkeiten). Exportiert nur sichtbare Spalten und ausgeklappte Gruppen.
- Erweiterte CEO-Ansicht als **einseitiges A4-PDF** optimiert.
- **Produktkatalog** überarbeitet: neue Kategorien Displays / Beschallung / SW-Lizenzen / Sonstiges, Materialnummer-Spalte, einklappbar, CSV-Import ergänzt Bestand.
- **Self-Service Passwortänderung** für alle Benutzer.
- Tabellen-Header umbrechen bei schmalen Spalten — keine überlappenden Spaltentitel mehr.
- Startseite: Button „Angebote anzeigen" entfernt — Liste war auch ohne diesen Button sichtbar.

---

## Datenbank-Migrationen

Damit alle Funktionen in einer bestehenden Installation greifen, muss einmalig **`install.php`** im Browser aufgerufen werden. Das Script ist idempotent — mehrfaches Ausführen ist unkritisch.

Im Rahmen von V1.0 ergänzte Migrationen:

| Tabelle / Spalte | Zweck |
|---|---|
| `quotes.status` | Lifecycle-Status pro Angebot |
| `quotes.current_version` | Verweis auf die letzte eingefrorene Version |
| `quote_revisions` | Komplette Snapshots aller eingefrorenen Versionen |
| `quote_shares` | Lese-Freigaben an einzelne Benutzer |
| `catalog.materialnr` | Materialnummer-Spalte im Produktkatalog |
| Kategorie-Umbenennung | `Hardware` → `Displays` im Produktkatalog |

> **Wichtig:** `install.php` nach erfolgreicher Migration wieder vom Server entfernen.

---

## Funktionsumfang im Überblick

Mit V1.0 stehen Vertriebsmitarbeiter:innen folgende Bereiche zur Verfügung:

- **Login & Self-Service Passwortverwaltung**
- **Startseite** mit Kachelnavigation, gruppierter Angebotsliste nach Status, Admin-Filter „Benutzer"
- **Neues Angebot** anlegen — Meta-Daten, Konfigurations-Parameter, Überschriften, Unterpositionen, Kostenstrukturen, Stunden- und Materialerfassung
- **Manuell anpassbarer Verkaufspreis pro Position** (Deckung wird automatisch nachgezogen)
- **Zwischensummen** zwischen Gruppen
- **Langtext pro Position** für ausführliche Details
- **Produktkatalog** mit Kategorien, Materialnummer, CSV-Import (Admin)
- **GAEB-LV-Import (X83)** mit Vorschau, Auswahl, Übernahme; Langtext aus DetailTxt
- **Versionsverwaltung** — Versionen einfrieren, einsehen, zurücksetzen
- **Status-Lifecycle** pro Angebot (Entwurf bis Archiviert)
- **Freigaben** lesend für andere Benutzer
- **CEO-Ansicht** (tabellarisch) mit Spaltenwahl, Ein-/Ausklappen, Excel-/Word-Export
- **Erweiterte CEO-Ansicht** als Deckungsbeitrag-PDF auf einer A4-Seite
- **Datenrettung**: Speichern-Erinnerung, Browser-Warnung bei ungespeicherten Änderungen
- **Drucken / PDF** des kompletten Angebots inkl. Langtexte
- **JSON-Export/-Import** für Backups
- **Admin-Portal** mit Benutzerverwaltung, Vorlagenpflege, Produktkatalog-Wartung

---

## Bekannte Einschränkungen (Phase 2)

- **GAEB-Flat-File-Format (`.D83`)** und **D.84-Rückexport** sind noch nicht enthalten.
- **CSV-Import im Produktkatalog** ergänzt nur, ein Update über Materialnummer ist noch nicht implementiert.
- **CEO-Excel-Export** erzeugt eine `.xls`-Datei (HTML-Blob); echte `.xlsx` mit Formeln bräuchte SheetJS.

---

_Hinweise oder Wünsche bitte an das Projektteam._
