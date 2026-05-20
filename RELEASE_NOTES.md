# Release Notes — Funkwerk Kalkulationsplattform

**Version 1.0 · Launch 01.06.2026**
Funkwerk Mobility Solutions GmbH

---

## Was ist neu in V1.0

Diese Release Notes fassen alle Funktionen zusammen, die bis zum offiziellen Launch am 01.06.2026 in den Funkwerk Kalkulationsplattform integriert wurden.

### Update vom 19.05.2026 — Programm Management Modul + Sonderkosten-Formeln

#### Produktkalkulationen (neues Modul)

Programm Management kann eigene Produktkalkulationen direkt im Tool durchführen — ohne E-Mail-Pingpong mit dem Vertrieb. Sobald ein Produkt freigegeben ist, landet es automatisch im Vertriebs-Produktkatalog mit aktuellem HK-Preis.

- **Neuer Startseiten-Button „Produktkalkulationen"** (braun, sichtbar für Admin und freigegebene Benutzer).
- **Liste** mit Produkten je Status (In Bearbeitung / Freigegeben / Archiviert), Materialnummer, HK, VK.
- **Editor** mit fünf Sektionen analog zur Excel-Vorlage:
  - **Kopfdaten** — Produkt-Nr., Bezeichnung, Kategorie, Materialnr., Stückzahl Los, Status
  - **Komponenten / Stückliste** — Tabelle mit Kategorie, OZ, Artikelnr., Bauteilname, Lieferant, Menge, Einheit, Einzelpreis; Live-Summe inkl. MGK
  - **Aufwände** — Personalstunden je Aufwandstyp (Montage 65 €, Entwicklung/SI/PM/Test 100 €, Customer Service 80 €). „einmalig"-Flag teilt die Entwicklungskosten automatisch durch die Stückzahl Los; Pro-Stück-Aufwände gehen direkt ein. Fertigungsstunden werden mit FGK belastet.
  - **Transport** — Kosten gesamt mit automatischer Umlage auf die Stückzahl
  - **Konfiguration** — MGK 4 %, FGK 6 %, Risikopuffer 2 %, VK-Faktor 1,30 sowie alle Stundensätze als Default; jeder Wert lokal pro Produkt überschreibbar
- **Live-Zusammenfassung** zeigt alle Teilbeträge und das Endergebnis (HK pro Anzeiger + empfohlener VK).
- **Excel-/CSV-Import** für die Komponentenliste: Spalten werden per Header-Erkennung gemappt (Kategorie, OZ, Artikelnr., Bauteilname, Lieferant, Menge, Einheit, Einzelpreis). Bestehende Zeilen wahlweise anhängen oder ersetzen. Platzhalterzeilen werden automatisch herausgefiltert.
- **„Im Katalog freigeben"** legt einen Eintrag im Vertriebs-Produktkatalog an oder aktualisiert den bestehenden — über `catalog.product_id` referenziert. Der Vertrieb sieht den neuen HK-Preis sofort beim nächsten „Aus Katalog".

#### Berechtigungs-Flag pro Benutzer

Statt einer neuen Rolle gibt es das Flag **`can_calc_products`** in der Users-Tabelle. Setzt der Admin den Haken, sieht der Benutzer den Button „Produktkalkulationen". Admins haben grundsätzlich Zugriff. Der Status wird beim Login und beim Session-Check übertragen.

#### Sonderkosten-Formeln im Vertriebs-Kalkulator

Das Feld **Sonderkosten** akzeptiert jetzt Formeln im Excel-Stil mit `=`-Präfix. Variablen je Position: `stk`/`menge` (Stück), `std` (Stunden), `em` (Eigenmaterial), `fm` (Fremdmaterial), `fl` (Fremdleistung), `gstk` (Gruppen-Stück).

Beispiele: `=stk*5,50` · `=menge/12` · `=(em+fm)*0,05`. Formel-Zellen werden orange hinterlegt; Tooltip zeigt das aufgelöste Ergebnis in €. Sicherer Parser per Whitelist — kein Code-Injection-Risiko.

#### Dashboard auf eigene Seite

Das Admin-Dashboard wurde aus dem Inline-Widget auf der Startseite in eine eigene View ausgegliedert — zugänglich über einen pinken „Dashboard"-Button auf der Startseite. Neue Visualisierungen: **Donut-Chart** für die Status-Verteilung (Anzahl) und **horizontaler Bar-Chart** für das VP-Volumen je Status. Listen für Top-Kunden, Top-Bearbeiter und letzte Aktivitäten bleiben.

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
| `catalog.product_id` | Verknüpfung zu Programm-Management-Produktkalkulationen |
| `products` | Produktkalkulationen (Programm Management) |
| `users.can_calc_products` | Berechtigungs-Flag für Produktkalkulation |
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
- **Sonderkosten-Formeln** mit `=`-Präfix (z.B. `=stk*5,50`)
- **JSON-Export/-Import** für Backups
- **Produktkalkulationen** (Programm Management) — eigene Modul mit Komponenten-, Aufwände-, Transportkostenerfassung, Excel-/CSV-Import, Freigabe in den Katalog
- **Admin-Dashboard** auf eigener Seite mit Donut- und Bar-Charts, KPIs, Top-Kunden, Top-Bearbeiter, Aktivitäten
- **Admin-Portal** mit Benutzerverwaltung, Vorlagenpflege, Produktkatalog-Wartung

---

## Bekannte Einschränkungen (Phase 2)

- **GAEB-Flat-File-Format (`.D83`)** und **D.84-Rückexport** sind noch nicht enthalten.
- **CSV-Import im Produktkatalog** ergänzt nur, ein Update über Materialnummer ist noch nicht implementiert.
- **CEO-Excel-Export** erzeugt eine `.xls`-Datei (HTML-Blob); echte `.xlsx` mit Formeln bräuchte SheetJS.

---

_Hinweise oder Wünsche bitte an das Projektteam._
