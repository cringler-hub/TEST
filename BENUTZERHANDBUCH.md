# Benutzerhandbuch — Funkwerk Kalkulationsplattform

> Anleitung für Vertriebsmitarbeiter · Funkwerk Mobility Solutions GmbH
> Stand: Version 1.0 (Launch 01.06.2026)

---

## Inhalt

1. [Überblick](#1-überblick)
2. [Erste Schritte](#2-erste-schritte)
3. [Angebot anlegen & kalkulieren](#3-angebot-anlegen--kalkulieren)
4. [Produktkatalog nutzen](#4-produktkatalog-nutzen)
5. [GAEB-LV einlesen (X83-Import)](#5-gaeb-lv-einlesen-x83-import)
6. [Versionen einfrieren & einsehen](#6-versionen-einfrieren--einsehen)
7. [Status & Ordner-/Filteransicht](#7-status--ordner-filteransicht)
8. [Angebote freigeben](#8-angebote-freigeben)
9. [Speichern, Laden, Importieren, Exportieren](#9-speichern-laden-importieren-exportieren)
10. [CEO-Ansichten & Export](#10-ceo-ansichten--export)
11. [Drucken / PDF](#11-drucken--pdf)
12. [Produktkalkulationen (Programm Management)](#12-produktkalkulationen-programm-management)
13. [Admin-Bereich](#13-admin-bereich)
14. [Tipps & häufige Fragen](#14-tipps--häufige-fragen)

---

## 1. Überblick

Der Funkwerk Kalkulationsplattform hilft Dir, Angebote nach Funkwerk-Standard zu erstellen. Du trägst Material-, Fremd- und Personalkosten pro Position ein und erhältst automatisch Herstellkosten (HK), Verkaufspreise (VP) und Deckungsbeiträge. Angebote können gespeichert, versioniert, an Kollegen freigegeben, ausgedruckt oder als Excel/Word/JSON exportiert werden. GAEB-LVs (X83) lassen sich direkt als Grundlage einlesen.

---

## 2. Erste Schritte

### 2.1 Anmelden

Öffne die URL des Tools, gib Benutzername und Passwort ein und klicke auf **Anmelden**. Bei Problemen wende Dich an Deinen Admin.

### 2.2 Passwort ändern

In jeder Ansicht findest Du oben rechts den Link **„Passwort ändern"**. Aktuelles Passwort eingeben, neues Passwort zweimal eingeben (mindestens 6 Zeichen), Speichern.

### 2.3 Die Startseite

Nach dem Anmelden siehst Du drei Kacheln:

- **Neues Angebot** – öffnet eine leere Kalkulation.
- **GAEB-LV einlesen** – importiert eine X83-Datei und übernimmt sie als Angebotsgerüst.
- **Admin Portal** – nur für Admins sichtbar.

Unterhalb der Kacheln findest Du die Liste der **gespeicherten Angebote**, gruppiert nach Status.

---

## 3. Angebot anlegen & kalkulieren

Klicke auf der Startseite auf **Neues Angebot**. Du landest in der Kalkulationsansicht.

### 3.1 Angaben zum Angebot (Kopfbereich)

- **Angebots-Titel** – Projektname
- **Kunde**
- **Angebots-Nr.** – wird automatisch vorbelegt
- **Datum** und **Gültig bis** – vorbelegt mit heute bzw. heute + 30 Tage
- **Status** – Entwurf (Default) / Eingereicht / Beauftragt / Verloren / Archiviert

### 3.2 Konfiguration

Über **„Konfiguration (Stundensätze, MGK, Deckung)"** klappt der Parameterblock auf:

- **MGK** und **FGK**, **Deckungs-Default**, **Preisnachlass**
- **Stundensätze** pro Abteilung
- GK-Zuschläge, Garantie, Sicherheit
- **Bearbeiter** und **Freigabe**

> Der **Bearbeiter** wird beim Anlegen und beim Öffnen eines Angebots automatisch mit Deinem Login-Namen vorbelegt — überschreibbar.

### 3.3 Überschriften (Hauptpositionen)

- **Überschrift hinzufügen** – legt eine neue Hauptposition an, inklusive einer leeren Unterposition.
- **Vorlage einfügen** – fügt eine Hauptposition mit vorgegebenen Unterpositionen ein.
- Pro Überschrift gibt es eine **Stückzahl (Stk.)**, die alle Unterpositionen multipliziert.

**Aktionen pro Überschrift**: ↑/↓, Kopieren, Einfügen, Löschen, Ein-/Ausklappen.

### 3.4 Unterpositionen

Pro Zeile trägst Du ein:

| Feld | Bedeutung |
|---|---|
| **Pos.** | Automatisch (1.1, 1.2, …); manuell überschreibbar |
| **Bezeichnung** | Leistung / Produkt |
| **Kostenstruktur** | Welche Abteilung / welcher Kostentyp |
| **Eigenmaterial €** | Eigenfertigungs-Material |
| **Fremdmaterial €** | Zugekauftes Material (mit MGK belegt) |
| **Fremdleistung €** | Externe Dienstleistung (mit MGK belegt) |
| **Sonderkosten €** | 1:1 übernommen — akzeptiert auch Formeln mit `=`-Präfix (siehe 3.x) |
| **Std./Menge** | Stunden oder km |
| **Stück** | Anzahl in der Gruppe |
| **HK Einzel** | Automatisch berechnet |
| **Deckung %** | Frei einstellbar, Default aus Konfiguration |
| **VP Einzel** | **NEU: manuell editierbar** — Deckung wird automatisch nachgezogen |
| **HK Gesamt / VP Gesamt** | × Stück × Gruppen-Stück |

**Aktionen pro Unterposition**: ↑/↓, Kopieren, Einfügen, **Langtext-Toggle** 📝, Löschen.

### 3.5 Formeln in Sonderkosten

Das Feld **Sonderkosten** akzeptiert nicht nur eine Zahl, sondern auch einfache Rechenformeln. Eine Formel beginnt immer mit `=`. Verfügbare Variablen pro Position:

| Variable | Bedeutung |
|---|---|
| `stk` / `menge` | Stück (im Item) |
| `std` | Stunden / Menge |
| `em` | Eigenmaterial € |
| `fm` | Fremdmaterial € |
| `fl` | Fremdleistung € |
| `gstk` | Stückzahl der Überschrift |

**Beispiele:**

- `=stk*5,50` → 5,50 € pro Stück
- `=menge/12` → durch 12 Monate teilen
- `=std*0,85` → 85 % vom Std.-Wert
- `=(em+fm)*0,05` → 5 % auf Materialkosten

Formel-Felder werden orange hinterlegt. Wert ohne `=` bleibt eine einfache Zahl wie bisher.

### 3.6 VP Einzel manuell anpassen

- Klick in das Feld **VP Einzel** und Wert ändern → die **Deckung wird automatisch neu berechnet** (Formel: `Deckung = (VP − HK) / VP × 100`).
- Tippst Du danach in ein Materialfeld, wird VP wieder aus HK und Deckung neu berechnet.
- Für SW-Lizenzen wirkt eine VP-Eingabe direkt auf den Kaufpreis (Feld „Fremdmaterial").

### 3.7 Langtext pro Position

Pro Unterposition kannst Du detaillierte Beschreibungen hinterlegen:

1. Klicke auf das **📝-Icon** in der Aktionsleiste der Position.
2. Eine Textarea klappt unterhalb auf — beliebig viel Text eintragen.
3. Erneuter Klick → klappt wieder ein. Das Icon leuchtet lila, sobald Text drinsteht.
4. Langtext wird **mit Kopieren, Verschieben, Speichern** mitgeführt.
5. Im **Druck/PDF**, in der **CEO-Ansicht** und beim **GAEB-Import** wird er sichtbar.

### 3.8 Zwischensummen

- Unterhalb der Tabelle: **„Zwischensumme einfügen"** legt eine neue Zwischensummen-Zeile an.
- Titel eingeben (z. B. „Zwischensumme Mechanik").
- Mit ↑/↓ verschieben.
- Summiert alle Überschriften seit der vorherigen Zwischensumme je Kostenart sowie HK Gesamt, VP Gesamt und Deckung %.

### 3.9 Gesamtsummen & Endpreis

Unten findest Du fett: Gesamt HK, Gesamt VP, Deckung, Endpreis (nach Nachlass), Deckung (nach Nachlass).

### 3.10 Status setzen

Im Angebotskopf gibt es das Dropdown **Status**:

- **Entwurf** – frühe Bearbeitungsphase
- **Eingereicht** – an Kunde gesendet
- **Beauftragt** – Auftrag erhalten
- **Verloren** – nicht beauftragt
- **Archiviert** – ältere/abgeschlossene Angebote

Der Status kann jederzeit gewechselt werden — auch direkt aus der Startseite-Liste.

---

## 4. Produktkatalog nutzen

Der Produktkatalog enthält häufig verwendete Produkte (Displays, SW-Lizenzen, Beschallung, Sonstiges) mit hinterlegten HK- und VK-Preisen plus Materialnummer.

**So fügst Du ein Produkt aus dem Katalog ein:**

1. In einer Überschrift unten auf **„Aus Katalog"** klicken.
2. Auswahl-Fenster mit Suchfeld und Kategorien-Reitern öffnet sich.
3. Produkt anklicken → wird mit Materialnr., Bezeichnung, Preisen und passender Kostenstruktur eingefügt.

---

## 5. GAEB-LV einlesen (X83-Import)

Wenn ein Kunde ein Leistungsverzeichnis als GAEB-XML (`.X83`) schickt, kannst Du es als Angebotsgerüst importieren.

1. Startseite → **„GAEB-LV einlesen"**.
2. **„X83-Datei auswählen"** und hochladen.
3. Vorschau-Tabelle erscheint mit OZ, Titel/Gruppe, Kurztext, Menge, Einheit.
4. **Auswahl** per Checkbox; **„Alle auswählen"/„Alle abwählen"**; **↑/↓** zum Umsortieren.
5. **„In Kalkulation übernehmen"** → erstellt ein neues Angebot. Jede GAEB-Position wird zu einer **Überschrift** (OZ = Pos.-Nr., Menge = Gruppen-Stück, Kurztext + Einheit als Titel). Darunter wird automatisch eine **Default-Unterposition mit Kostenstruktur „Material"** angelegt.
6. Wenn die GAEB-Datei einen `DetailTxt` enthält, wird er als **Langtext** automatisch in die Default-Unterposition übernommen — Du siehst den Originaltext direkt unter der Position.

> Aktuell nur `.X83` (GAEB XML 3.x). Das alte Flat-File `.D83` sowie der D.84-Rückexport sind in Phase 2 geplant.

---

## 6. Versionen einfrieren & einsehen

Damit Du den Bearbeitungsstand eines Angebots sauber dokumentieren kannst, gibt es ein Versions-System:

### 6.1 Version einfrieren

1. Im Kalkulator unten auf **„Version einfrieren"** (lila).
2. Optionalen **Kommentar** eintragen (z. B. „nach Verhandlung mit Kunde XYZ").
3. **„Einfrieren"** → Snapshot wird unter `V1`, `V2`, `V3` … angelegt.
4. Du arbeitest danach normal weiter; weitere Änderungen landen erst beim nächsten Einfrieren in einer neuen Version.

### 6.2 Versionen einsehen

- Button **„Versionen"** öffnet die Liste aller eingefrorenen Versionen.
- **Anzeigen** öffnet eine alte Version im **Read-Only-Modus** (gelbes Banner oben, alle Inputs gesperrt). Drucken, Export und CEO-Ansicht funktionieren weiter.
- **„Schließen"** kehrt zum lebenden Entwurf zurück.

### 6.3 Auf eine Version zurücksetzen

- In der Versions-Liste auf **„Zurücksetzen"** klicken.
- Sicherheitsabfrage bestätigt, dass der aktuelle Entwurf überschrieben wird.
- Der gewählte Snapshot wird zum neuen Entwurf, kann normal weiterbearbeitet werden.

### 6.4 Revisions-Banner

Im Kalkulator zeigt das Banner oben:

- **Aktueller Status** (Entwurf / Eingereicht / …)
- **Letzte eingefrorene Version** mit Datum, Bearbeiter und Kommentar (falls vorhanden)

---

## 7. Status & Ordner-/Filteransicht

Auf der Startseite werden Angebote nach Status gruppiert in **einklappbaren Sektionen** angezeigt:

- Standard offen: **Entwurf** und **Eingereicht**
- Standard eingeklappt: **Beauftragt**, **Verloren**, **Archiviert**

Jede Sektion zeigt einen **Zähler**. Direkt in der Zeile lässt sich der Status per Dropdown wechseln — keine Notwendigkeit, das Angebot zu öffnen.

**Admins** sehen zusätzlich oben rechts einen **Benutzer-Filter** zur Auswahl eines bestimmten Erstellers.

---

## 8. Angebote freigeben

Du kannst Angebote für andere Benutzer **zum Lesen** freigeben.

### 8.1 Freigeben

1. Angebot öffnen, **„Angebot speichern"** falls neu.
2. Button **„Freigeben"** klicken.
3. Im Dropdown den Benutzer auswählen, **„Hinzufügen"**.
4. Liste der aktuellen Empfänger erscheint darunter, jeder kann per **„Entfernen"** wieder rausgenommen werden.

### 8.2 Was sieht der Empfänger?

- Auf seiner Startseite erscheint das Angebot in der Liste mit dem Badge **📥 Freigegeben** — neben den eigenen Angeboten.
- Beim Öffnen ist es **read-only**: gelbes Banner „Freigegeben von … am …".
- Der Empfänger kann:
  - Drucken / PDF
  - CEO-Ansichten + Excel/Word-Export
  - Versionsliste einsehen und alte Versionen anzeigen
  - JSON exportieren
- Der Empfänger kann **nicht**:
  - Werte ändern
  - Speichern
  - Einfrieren
  - Status ändern
  - Löschen
  - weiter freigeben

### 8.3 Bei eigenen Angeboten

Beim Ersteller erscheint in der Liste das Badge **👥 N** mit der Anzahl der Empfänger.

---

## 9. Speichern, Laden, Importieren, Exportieren

### 9.1 Speichern auf dem Server

In der Kalkulation unten: **„Angebot speichern"**.

### 9.2 Speichern-Erinnerung

Beim Klick auf **„Startseite"** oder **„Abmelden"** im Kalkulator-Topbar erscheint ein Modal, wenn ungespeicherte Änderungen vorliegen:

- **Abbrechen** — bleibt im Kalkulator
- **Ohne Speichern verlassen** — geht ohne Speichern
- **Speichern und verlassen** — speichert dann navigiert

Beim Schließen des Browser-Tabs / Reload zeigt der Browser zusätzlich seine **eigene native Warnung**.

### 9.3 Angebot laden

Auf der Startseite unter „Gespeicherte Angebote" auf **„Öffnen"**.

### 9.4 JSON-Export / -Import

- **„Als JSON exportieren"** — Download des kompletten Angebots als `.json`.
- **„JSON importieren"** — liest eine zuvor exportierte JSON-Datei ein.

### 9.5 Zurücksetzen

**„Zurücksetzen"** löscht die aktuelle Kalkulation.

---

## 10. CEO-Ansichten & Export

### 10.1 CEO-Ansicht (tabellarisch)

Öffnet ein separates Fenster mit Positionsliste, Gesamtsummen und Header inklusive **Bearbeiter** und **Datum** (DD.MM.YYYY).

**Steuerung:**

- **Spalten-Toolbar** oben: Spalten per Checkbox ein-/ausblenden.
- **Alle einklappen / Alle ausklappen** zeigt nur Überschriften oder alle Positionen.
- Klick auf den Pfeil einer einzelnen Überschrift klappt nur diese Gruppe.

**Exporte:**

- **Drucken / PDF**
- **Excel-Export** (`.xls`, HTML-Blob) — nur sichtbare Spalten + ausgeklappte Gruppen
- **Word-Export** (`.doc`, HTML-Blob)

Langtexte erscheinen als graue Detailzeile direkt unter der Position.

### 10.2 Erweiterte CEO-Ansicht

Deckungsbeitrag-Format mit Umsatz → HK → DB → Gemeinkosten → Selbstkosten → Garantie/Sicherheit → Gewinn.

- GK-Prozentsätze direkt im Popup editierbar — wirken zurück in die Kalkulation.
- Layout auf **eine A4-Seite (hoch)** optimiert: **„Drucken / PDF"** erzeugt ein fertig verwendbares Dokument.
- Datum + Bearbeiter werden zusätzlich als eigene Zeilen im Projekt-Block angezeigt.

---

## 11. Drucken / PDF

In der Kalkulation: **„Drucken / als PDF"**. Beim Druck werden **alle Langtext-Zeilen mit Inhalt automatisch eingeblendet**, die Eingabefelder erscheinen als plain Text. Nach dem Druck wird der vorherige Klappzustand wiederhergestellt.

---

## 12. Produktkalkulationen (Programm Management)

> Sichtbar für **Admins** und Benutzer mit Berechtigungs-Haken „Produktkalkulation".

Statt HK-Preise eigenproduzierter Anzeiger per E-Mail an den Vertrieb zu schicken, kann Programm Management die Kalkulation direkt im Tool machen. Sobald sie freigegeben ist, landet das Produkt automatisch im Vertriebs-Katalog mit aktuellem HK-Preis.

### 12.1 Aufruf

Auf der Startseite den Button **„Produktkalkulationen"** klicken → Liste aller Produkte (gruppiert nach Status: In Bearbeitung / Freigegeben / Archiviert). Neues Produkt über **„Neues Produkt"** rechts oben.

### 12.2 Kopfdaten

- **Produkt-Nr.**, **Bezeichnung**, **Kategorie** (Displays / Beschallung / SW-Lizenzen / Sonstiges)
- **Materialnr.** — wird beim Freigeben in den Katalog übernommen
- **Stückzahl Los** — z.B. 15 Anzeiger. Wird als Umlage-Basis verwendet.
- **Status** — wird automatisch gesetzt (In Bearbeitung → Freigegeben → Archiviert).

### 12.3 Komponenten / Stückliste

Tabelle mit Spalten: Kategorie · OZ · Artikelnr. · Bauteilname · Lieferant · Menge · Einheit · Einzelpreis · Gesamt.

- **„+ Zeile hinzufügen"** für manuelle Einträge.
- **„Excel/CSV importieren"** zieht eine vorhandene Stückliste rein:
  - Unterstützt `.xlsx`, `.xls`, `.csv`.
  - Spalten werden per Header-Erkennung gemappt (Synonyme für Kategorie, OZ, Artikelnr., Bauteilname, Lieferant, Menge, Einheit, Einzelpreis).
  - Platzhalterzeilen wie „Stücklistenlangtext" und Dummy-Nummern werden automatisch herausgefiltert.
  - Auswahl beim Import: **Anhängen** oder bestehende Zeilen **Ersetzen**.

### 12.4 Aufwände

Personalstunden je Aufwandstyp:

| Typ | Default-Satz | Default „einmalig" |
|---|---|---|
| Produktionszeit (Montage) | 65 €/h + FGK | ❌ pro Stück |
| Entwicklung / Konstruktion | 100 €/h | ✅ wird umgelegt |
| Entwicklung Hardware | 100 €/h | ✅ wird umgelegt |
| SI / Embedded | 100 €/h | ✅ wird umgelegt |
| Programm Management | 100 €/h | ✅ wird umgelegt |
| Validierung / Tests | 100 €/h | ✅ wird umgelegt |
| Industrial Engineering | 80 €/h | ✅ wird umgelegt |
| Customer Service | 80 €/h | ❌ pro Stück |
| Sonstige | 65 €/h | ❌ pro Stück |

- **„einmalig"-Haken** teilt die Gesamtkosten durch die Stückzahl Los — typisch für Entwicklungs-Aufwände.
- **Ohne Haken** gilt der Wert direkt pro Anzeiger — typisch für Montagezeit.
- **Fertigungsstunden** (Aufwandstyp „Montage") werden zusätzlich mit dem FGK-Aufschlag belastet.

### 12.5 Transportkosten

Tabelle mit Transportart · Bemerkung · Stückzahl · Kosten gesamt · pro Anzeiger.
Die Gesamtkosten werden automatisch auf die Stückzahl Los umgelegt.

### 12.6 Konfiguration (einklappbar)

Default-Werte werden vom System gesetzt und können **pro Produkt** überschrieben werden:

- **MGK** (Material): 4 %
- **FGK** (Fertigung): 6 %
- **Risikopuffer**: 2 %
- **VK-Faktor** (Empfehlung): 1,30
- **Stundensätze** pro Aufwandstyp

### 12.7 Live-Zusammenfassung

Unterhalb des Editors zeigt die orange Leiste:

- Material gesamt (× MGK)
- Aufwände einmalig / pro Stk.
- Fertigung (× FGK)
- Transport pro Stk.
- Risikopuffer
- **HK pro Anzeiger** (hervorgehoben)
- **Empf. VK** (HK × VK-Faktor)

Die Werte aktualisieren sich live bei jeder Eingabe.

### 12.8 Speichern / Freigeben / Archivieren

- **„Speichern"** — speichert den aktuellen Stand. Status bleibt „In Bearbeitung".
- **„Im Katalog freigeben"** — speichert + setzt Status auf „Freigegeben" + legt einen Eintrag im Vertriebs-Produktkatalog an (oder aktualisiert den vorhandenen, falls schon einmal freigegeben). Der Vertrieb sieht den HK-Preis ab sofort beim „Aus Katalog".
- **„Archivieren"** — markiert das Produkt als nicht mehr aktiv. Der Katalog-Eintrag bleibt unverändert; bei erneuter Freigabe wird er aktualisiert.

### 12.9 Beispiel-Rechnung

> Stückzahl Los = 15 Anzeiger
> Material (Summe Komponenten) = 3.000 € · MGK 4 % → 3.120 €
> Entwicklung 30 h × 100 €/h = 3.000 € (einmalig) → 200 € / Anzeiger
> Montage 2 h × 65 €/h × (1 + 6 %) = 137,80 € / Anzeiger
> Transport gesamt 4.832 € / 15 = 322,13 € / Anzeiger
> **Zwischensumme: 3.779,93 €**
> Risikopuffer 2 % = 75,60 €
> **HK pro Anzeiger: 3.855,53 €**
> Empf. VK = HK × 1,30 = **5.012,19 €**

---

## 13. Admin-Bereich

Nur sichtbar für Benutzer mit Rolle **Admin**. Auf der Startseite **„Admin Portal"** öffnen.

### 13.1 Benutzerverwaltung

- Liste aller Benutzer mit Rolle.
- Neue Benutzer anlegen (Benutzername, Passwort, Rolle).
- Passwort für Andere zurücksetzen.
- **Berechtigung „Produktkalkulation"** pro Benutzer setzbar (Haken). Nutzer mit Haken sehen den Button „Produktkalkulationen" auf der Startseite (Kapitel 12).
- Admin-Account kann nicht gelöscht werden.

### 13.2 Vorlagen verwalten

Vordefinierte Positionslisten (z. B. „Anzeiger"), die im Kalkulator unter „Vorlage einfügen" angeboten werden.

### 13.3 Produktkatalog (einklappbar)

- Spalten: **Materialnr.**, Beschreibung, HK Preis, VK Preis, Kategorie
- Neue Produkte über Formular hinzufügen
- **CSV-Import**: Format `Materialnr;Beschreibung;HK;VK;Kategorie`. Bestehende Einträge bleiben erhalten — der Import **ergänzt** nur.
- Über Produktkalkulationen freigegebene Einträge (siehe Kapitel 12) werden automatisch aktualisiert, wenn das Produkt erneut freigegeben wird.

### 13.4 Dashboard

Über den pinken Button **„Dashboard"** auf der Startseite (eigene Seite). KPIs (Angebote gesamt, Pipeline, Auftragseingang, Hit-Rate, Verloren-Wert, Gesamt-VP), Donut-Chart für Status-Verteilung, horizontaler Bar-Chart für VP-Volumen, Top-Kunden, Top-Bearbeiter und letzte 10 Aktivitäten.

### 13.5 Release Notes & Handbuch

Über Buttons im Admin-Portal direkt aufrufbar. Aktuelle Stände, alle Versionsänderungen.

---

## 14. Tipps & häufige Fragen

**Wie wird der Verkaufspreis berechnet?**
> VP = HK ÷ (1 − Deckung). Beispiel: HK = 1.000 €, Deckung = 25 % → VP = 1.333,33 €.

**Was passiert, wenn ich den VP manuell ändere?**
> Die Deckung wird automatisch neu berechnet, sodass die Beziehung stimmt. Tippst Du danach in ein HK-Feld, gilt die alte Berechnung (VP folgt aus HK und Deckung) wieder.

**Warum zeigt eine Unterposition HK = 0?**
> Bei Kostenstruktur „SW-Lizenzen" ist das Standard: HK = 0, VP = Kaufpreis (Feld Fremdmaterial), Deckung 100 %.

**Wie multipliziert sich die Stückzahl einer Überschrift?**
> Jede Unterposition × Gruppen-Stück. Beispiel: Unterposition mit Stück 2 in einer Überschrift mit Gruppen-Stück 5 → tatsächliche Menge = 10.

**Kann ich eine eingefrorene Version weiterbearbeiten?**
> Direkt nein — eine Version ist immer read-only. Aber Du kannst sie über „Versionen → Zurücksetzen" als neuen Entwurf übernehmen und dann bearbeiten.

**Kann ich ein freigegebenes Angebot bearbeiten?**
> Als Empfänger nein, nur lesen. Frage den Ersteller, ob er die Änderung vornimmt. Direktes Bearbeiten ist nur dem Ersteller (und Admins) erlaubt.

**Was passiert, wenn ich beim Verlassen des Kalkulators „Ohne Speichern" wähle?**
> Alle Änderungen seit dem letzten Speichern gehen verloren. Bereits eingefrorene Versionen bleiben unverändert.

**Was, wenn ich mein Passwort vergessen habe?**
> Ein Admin muss es für Dich zurücksetzen (Admin-Bereich → Benutzerverwaltung).

**Wo finde ich den Anwender-Stand (Versionsnummer) des Tools?**
> Unten am Login-Bildschirm sowie in jeder Topbar als kleine Zeile unterhalb von „Funkwerk Mobility Solutions GmbH". Aktuell: **V1.0 · 01.06.2026**.

---

_Änderungswünsche oder Fehler im Handbuch bitte an den Admin oder das Projektteam melden._
