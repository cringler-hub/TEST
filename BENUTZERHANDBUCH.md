# Benutzerhandbuch — Angebotskalkulator

> Anleitung für Vertriebsmitarbeiter · Funkwerk Mobility Solutions
> Stand: Version 1 — _Platzhalter für Screenshots, werden später ergänzt_

---

## Inhalt

1. [Überblick](#1-überblick)
2. [Erste Schritte](#2-erste-schritte)
3. [Angebot anlegen & kalkulieren](#3-angebot-anlegen--kalkulieren)
4. [Produktkatalog nutzen](#4-produktkatalog-nutzen)
5. [GAEB-LV einlesen (X83-Import)](#5-gaeb-lv-einlesen-x83-import)
6. [Speichern, Laden, Importieren, Exportieren](#6-speichern-laden-importieren-exportieren)
7. [CEO-Ansichten & Export](#7-ceo-ansichten--export)
8. [Drucken / PDF](#8-drucken--pdf)
9. [Admin-Bereich](#9-admin-bereich)
10. [Tipps & häufige Fragen](#10-tipps--häufige-fragen)

---

## 1. Überblick

Der Angebotskalkulator hilft Dir, Angebote nach Funkwerk-Standard zu erstellen. Du trägst Material-, Fremd- und Personalkosten pro Position ein und erhältst automatisch Herstellkosten (HK), Verkaufspreise (VP) und Deckungsbeiträge. Angebote können gespeichert, als JSON importiert/exportiert, gedruckt oder als Excel/Word heruntergeladen werden. GAEB-LVs (X83) lassen sich direkt als Grundlage einlesen.

---

## 2. Erste Schritte

### 2.1 Anmelden

Öffne die URL des Tools, gib Benutzername und Passwort ein und klicke auf **Anmelden**. Bei Problemen wende Dich an Deinen Admin.

### 2.2 Passwort ändern

In jeder Ansicht findest Du oben rechts den Link **„Passwort ändern"**.
Darüber kannst Du Dein Passwort jederzeit selbst ändern:

- Aktuelles Passwort eingeben
- Neues Passwort zweimal eingeben (mindestens 6 Zeichen)
- **Speichern**

### 2.3 Die Startseite

Nach dem Anmelden siehst Du drei Kacheln:

- **Neues Angebot** – öffnet eine leere Kalkulation.
- **GAEB-LV einlesen** – importiert eine X83-Datei und übernimmt sie als Angebotsgerüst.
- **Admin Portal** – nur für Admins sichtbar (Benutzer, Vorlagen, Produktkatalog).

Unterhalb der Kacheln findest Du die Liste der **gespeicherten Angebote** — klicke auf „Öffnen", um ein Angebot weiterzubearbeiten.

---

## 3. Angebot anlegen & kalkulieren

Klicke auf der Startseite auf **Neues Angebot**. Du landest direkt in der Kalkulationsansicht.

### 3.1 Angaben zum Angebot (Kopfbereich)

Im oberen Block trägst Du ein:

- **Angebots-Titel** – Projektname
- **Kunde**
- **Angebots-Nr.** – wird automatisch vorbelegt, kann überschrieben werden
- **Datum** und **Gültig bis** – vorbelegt mit heute bzw. heute + 30 Tage

### 3.2 Konfiguration aufklappen

Über **„Konfiguration (Stundensätze, MGK, Deckung)"** klappt der Kalkulations-Parameterblock auf:

- **MGK** (Materialgemeinkosten-Faktor) und **FGK** (Fertigungsgemeinkosten-Faktor)
- **Deckungs-Default** in %
- **Preisnachlass** in % (wird am Ende auf den Gesamtpreis angewendet)
- **Stundensätze** pro Abteilung (z. B. Konstruktion, HW-Design, Fertigung …)
- GK-Zuschläge (GK1 Material/Personal, GK2 Verwaltung/Vertrieb/Entwicklung), Garantie, Sicherheit
- **Bearbeiter** und **Freigabe**

Diese Werte fließen in alle Berechnungen ein.

### 3.3 Überschriften (Hauptpositionen) anlegen

Eine Kalkulation gliedert sich in **Überschriften** (Hauptpositionen). Jede Überschrift kann mehrere **Unterpositionen** enthalten.

- **Überschrift hinzufügen** – legt eine neue Hauptposition an, inklusive einer leeren Unterposition.
- **Vorlage einfügen** – fügt eine Hauptposition mit vorgegebenen Unterpositionen aus einer Vorlage ein (z. B. „Anzeiger").
- Pro Überschrift gibt es eine **Stückzahl (Stk.)**, mit der alle Unterpositionen multipliziert werden (z. B. Anzahl identischer Baugruppen).

**Aktionen pro Überschrift** (rechts in der Zeile):

- ↑ / ↓ — verschieben
- Kopieren / Einfügen — Überschrift duplizieren
- 🗑 — löschen (mit Rückfrage, wenn Unterpositionen vorhanden sind)
- Der kleine Pfeil am Anfang der Zeile klappt die Unterpositionen ein/aus.

### 3.4 Unterpositionen

Jede Überschrift enthält eine Tabelle mit Unterpositionen. Pro Zeile trägst Du ein:

| Feld | Bedeutung |
|---|---|
| **Pos.** | Wird automatisch gesetzt (1.1, 1.2, …); kannst Du bei Bedarf manuell überschreiben. |
| **Bezeichnung** | Leistung / Produkt |
| **Kostenstruktur** | Welcher Abteilung / welchem Kostentyp ordnest Du das zu (siehe 3.5) |
| **Eigenmaterial €** | Eigenfertigungs-Material pro Stück |
| **Fremdmaterial €** | Zugekauftes Material (mit MGK belegt) |
| **Fremdleistung €** | Dienstleistungen extern (mit MGK belegt) |
| **Sonderkosten €** | Werden 1:1 übernommen |
| **Std./Menge** | Stunden (bei Personal-Kostenstrukturen) oder Menge (bei km) |
| **Stück** | Anzahl dieser Unterposition innerhalb der Gruppe |
| **HK Einzel** | Automatisch berechnet |
| **Deckung %** | Frei einstellbar, Default aus Konfiguration |
| **VP Einzel** | Ergibt sich aus HK und Deckung |
| **HK Gesamt / VP Gesamt** | × Stück × Gruppen-Stück |

**Aktionen pro Unterposition**: ↑ / ↓, Kopieren, Einfügen, Löschen.

### 3.5 Kostenstrukturen verstehen

Die **Kostenstruktur** bestimmt, wie die Kosten berechnet werden:

- **Material**, **Konstruktion**, **HW-Design**, **SW-Entwicklung**, **Test**, **Fertigung**, **Projektmanagement**, **Service**, … → Stundenbasiert (Std. × Stundensatz, ggf. FGK-Aufschlag bei Fertigung)
- **Sonderkosten** → werden ohne Aufschlag übernommen
- **Km (Reisekosten)** → Menge × Km-Satz
- **SW-Lizenzen** → Sonderfall: Kein HK-Aufschlag, VP = Kaufpreis; Deckung per Definition 100 %

### 3.6 Zwischensummen einfügen

Für lange Angebote kannst Du **Zwischensummen** zwischen Überschriften einfügen:

1. Unterhalb der Tabelle: **„Zwischensumme einfügen"**
2. Es wird eine neue Zeile am Ende angelegt.
3. Titel der Zwischensumme (z. B. „Zwischensumme Mechanik") eingeben.
4. Mit ↑ / ↓ an die richtige Stelle verschieben.

Die Zwischensumme summiert **alle Überschriften seit der vorherigen Zwischensumme** — je Kostenart (Eigenmaterial, Fremdmaterial, Fremdleistung, Sonderkosten, Stunden) sowie HK Gesamt, VP Gesamt und Deckung %.

### 3.7 Gesamtsummen & Endpreis

Ganz unten findest Du die fett hervorgehobene Totals-Leiste:

- **Gesamt Herstellkosten**
- **Gesamt Verkaufspreis**
- **Deckung (bei VP)**
- **Endpreis (nach Nachlass)**
- **Deckung (nach Nachlass)**

Der **Preisnachlass** (Kopfkonfiguration) wirkt nur auf den Endpreis, nicht auf die einzelnen Positionen.

### 3.8 Kopieren & Einfügen

Jede Überschrift und jede Unterposition hat einen **Kopieren**-Button. Danach kannst Du den **Einfügen**-Button an einer anderen Stelle klicken, um das kopierte Element dort einzufügen.

---

## 4. Produktkatalog nutzen

Der Produktkatalog enthält häufig verwendete Produkte (z. B. Displays, SW-Lizenzen, Beschallungskomponenten) mit hinterlegten HK- und VK-Preisen. Er wird von Admins gepflegt.

**So fügst Du ein Produkt aus dem Katalog ein:**

1. In einer Überschrift unten auf **„Aus Katalog"** klicken.
2. Es öffnet sich ein Auswahl-Fenster mit Suchfeld und Kategorien-Reiter (SW-Lizenzen, Displays, Beschallung, Sonstiges).
3. Produkt anklicken — es wird automatisch als Unterposition eingefügt, mit der richtigen Kostenstruktur und den Preisen:
   - **Displays / Beschallung** → Kostenstruktur „Material", HK-Preis ins Feld Fremdmaterial, Deckung aus HK/VK.
   - **SW-Lizenzen** → Kostenstruktur „SW-Lizenzen", VP = Kaufpreis.
4. Du kannst danach Stückzahl und weitere Felder anpassen.

---

## 5. GAEB-LV einlesen (X83-Import)

Wenn ein Kunde ein Leistungsverzeichnis als GAEB-XML (`.X83`) schickt, kannst Du es als Angebotsgerüst importieren.

1. Startseite → **„GAEB-LV einlesen"** klicken.
2. **„X83-Datei auswählen"** — Datei hochladen.
3. Es öffnet sich eine Vorschau-Tabelle mit allen erkannten Positionen:
   - OZ (Ordnungszahl), Titel/Gruppe, Kurztext, Menge, Einheit.
4. **Auswahl steuern:**
   - ☐ pro Zeile zum Aus-/Einschließen.
   - **Alle auswählen** / **Alle abwählen** für Bulk-Aktionen.
   - ↑ / ↓ zum Umsortieren einzelner Zeilen.
5. **„In Kalkulation übernehmen"** — erstellt ein neues Angebot:
   - **Jede GAEB-Position wird eine Überschrift** (Titel = Kurztext [Einheit], OZ als Pos.-Nr., Menge als Gruppen-Stück).
   - Darunter wird automatisch **eine Default-Unterposition mit Kostenstruktur „Material"** angelegt — hier kannst Du Unterprojekte oder weitere Positionen ergänzen.

> **Hinweis:** Aktuell nur `.X83` (GAEB XML). Das alte Flat-File-Format `.D83` sowie der D.84-Rückexport sind geplant (Phase 2).

---

## 6. Speichern, Laden, Importieren, Exportieren

### 6.1 Speichern auf dem Server

In der Kalkulation unten: **„Angebot speichern"**. Das Angebot erscheint danach in der Angebotsliste auf der Startseite.

### 6.2 Angebot laden

Auf der Startseite unter „Gespeicherte Angebote" auf **Öffnen** klicken.

### 6.3 Angebot löschen

In der Angebotsliste auf das 🗑 -Symbol. Bei Admins zusätzlich sichtbar: der Ersteller.

### 6.4 JSON-Export / -Import

- **„Als JSON exportieren"** — lädt das komplette Angebot als `.json` herunter (Backup, Weitergabe).
- **„JSON importieren"** — liest eine zuvor exportierte JSON-Datei ein.

### 6.5 Zurücksetzen

**„Zurücksetzen"** löscht die aktuelle Kalkulation und beginnt frisch.

---

## 7. CEO-Ansichten & Export

Für die Präsentation an Entscheider oder zur weiteren Analyse gibt es zwei kompakte Sichten.

### 7.1 CEO-Ansicht (tabellarisch)

Öffnet ein separates Fenster mit Positionsliste und Gesamtsummen.

**Steuerung:**

- **Spalten-Toolbar** oben: Einzelne Spalten (Stk., HK Einzel, VP Einzel, HK Gesamt, Deckung, VP Gesamt) per Checkbox ein-/ausblenden.
- **Alle einklappen / Alle ausklappen** blendet die Unterpositionen ein/aus — so siehst Du nur die Überschriften mit ihren Summen.
- Ein Klick auf den Pfeil einer einzelnen Überschrift klappt nur diese Gruppe.

**Exporte unten:**

- **Drucken / PDF** — Druckdialog des Browsers.
- **Excel-Export** — lädt eine `.xls`-Datei herunter. **Es werden nur sichtbare Spalten und ausgeklappte Gruppen exportiert.**
- **Word-Export** — analog als `.doc`-Datei.

Änderst Du in der Kalkulation Werte, aktualisiert sich die CEO-Ansicht automatisch.

### 7.2 Erweiterte CEO-Ansicht (Deckungsbeitrag)

Öffnet eine Kalkulationsübersicht im Deckungsbeitrag-Format mit Umsatz → HK → Deckungsbeitrag → Gemeinkosten → Selbstkosten → Garantie/Sicherheit → Gewinn.

- GK-Prozentsätze sind **direkt im Popup editierbar** — Änderungen werden in die Kalkulation zurückgespielt.
- Das Layout ist auf **eine A4-Seite (hoch)** optimiert: **„Drucken / PDF"** erzeugt ein fertig verwendbares PDF.

---

## 8. Drucken / PDF

### 8.1 Ganzes Angebot

In der Kalkulation unten: **„Drucken / als PDF"**. Nutzt den Browser-Druckdialog; Ziel „Als PDF speichern" wählen.

### 8.2 CEO-Ansichten

In der CEO-Ansicht auf **„Drucken / PDF"** (siehe 7.1 / 7.2). Die erweiterte CEO-Ansicht ist auf 1 Seite A4 optimiert.

---

## 9. Admin-Bereich

> Nur sichtbar für Benutzer mit Rolle **Admin**.

Auf der Startseite **„Admin Portal"** → es öffnen sich drei einklappbare Bereiche.

### 9.1 Benutzerverwaltung

- Liste aller Benutzer mit Rolle.
- **Neuen Benutzer anlegen**: Benutzername, Passwort, Rolle (user/admin).
- Ein Benutzer kann sein Passwort jederzeit selbst über „Passwort ändern" tauschen. Ein Admin kann hier ein Passwort zurücksetzen.
- Der Admin-Account kann nicht gelöscht werden.

### 9.2 Vorlagen verwalten

Vorlagen sind vordefinierte Listen von Unterpositionen (z. B. „Anzeiger"), die im Kalkulator unter „Vorlage einfügen" angeboten werden. Hier legst Du sie an, ergänzt Zeilen und editierst Kostenstrukturen.

### 9.3 Produktkatalog (einklappbar)

Tabelle aller Produkte mit Spalten:

- **Materialnr.** (optional, freier Text)
- **Beschreibung**
- **HK Preis** / **VK Preis**
- **Kategorie** (SW-Lizenzen, Displays, Beschallung, Sonstiges)

**Produkt anlegen**: Formular unterhalb der Tabelle — Materialnr, Beschreibung, Preise, Kategorie → **Hinzufügen**.

**CSV-Import** (Bulk):

- Datei per Drag & Drop oder Klick in die Dropzone.
- Format: `Materialnr;Beschreibung;HK;VK;Kategorie` (Semikolon-getrennt, Materialnr und Kategorie optional).
- Ist ein Header vorhanden (enthält „Material", „Beschreibung", …), wird er automatisch erkannt.
- **Der Import ergänzt bestehende Einträge — bereits vorhandene Produkte bleiben erhalten**.

---

## 10. Tipps & häufige Fragen

**Wie wird der Verkaufspreis berechnet?**
> VP = HK ÷ (1 − Deckung). Beispiel: HK = 1.000 €, Deckung = 25 % → VP = 1.333,33 €.

**Warum zeigt eine Unterposition HK = 0?**
> Bei Kostenstruktur „SW-Lizenzen" ist das normal: HK = 0, VP = eingetragener Kaufpreis (Feld Fremdmaterial), Deckung 100 %.

**Wie multipliziert sich die Stückzahl einer Überschrift?**
> Jede Unterposition in der Gruppe × Gruppen-Stück. Beispiel: Unterposition mit Stück = 2 in einer Überschrift mit Gruppen-Stück = 5 → tatsächliche Menge = 10.

**Was passiert, wenn ich eine Überschrift lösche, die Unterpositionen enthält?**
> Du wirst vorher gefragt. Nach Bestätigung werden Überschrift und alle Unterpositionen gelöscht.

**Kann ich die Pos.-Nr. anpassen?**
> Ja. Sobald Du sie einmal überschreibst, bleibt sie erhalten — die automatische Nummerierung übergeht manuell geänderte Pos.-Nummern.

**Ich habe ein GAEB-LV importiert, aber die Default-Unterposition ist leer — warum?**
> Gewollt. Jede GAEB-Position wird eine Überschrift, darunter eine leere Material-Unterposition als Starthilfe. Fülle sie mit den tatsächlichen Kostenbestandteilen oder ersetze sie durch mehrere eigene Unterpositionen.

**Kann ich im Excel-/Word-Export auch Details mitnehmen, die in der CEO-Ansicht ausgeblendet sind?**
> Nein — der Export bildet 1:1 die aktuelle Sicht ab (sichtbare Spalten + ausgeklappte Gruppen). Sichtbarkeit vorher entsprechend einstellen.

**Was, wenn ich mein Passwort vergessen habe?**
> Ein Admin muss es für Dich zurücksetzen (Admin-Bereich → Benutzerverwaltung).

---

_Änderungswünsche oder Fehler im Handbuch bitte an den Admin oder das Projektteam melden._
