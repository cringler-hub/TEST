# Anleitung: GitHub → IONOS Webspace per SFTP automatisch deployen

Schritt-für-Schritt-Anleitung, um Pushes auf GitHub automatisch auf Euren IONOS-Webspace zu übertragen — ohne FTP-Programm, ohne manuelles Hochladen.

---

## Was Du am Ende hast

- Push auf einen festgelegten Branch (z. B. `main`) → GitHub Actions deployed die Dateien automatisch per SFTP auf IONOS.
- Sensible Dateien wie `install.php`, `RELEASE_NOTES.md`, `BENUTZERHANDBUCH.md` werden vom Upload ausgeschlossen.
- Zugangsdaten liegen verschlüsselt in GitHub Secrets — nichts davon im Repo sichtbar.

---

## Voraussetzungen

- Repository auf GitHub mit Schreibzugriff
- IONOS-Webhosting-Paket mit (S)FTP-Zugang
- 10 Minuten Zeit

---

## Schritt 1 — SFTP-Zugangsdaten in IONOS finden

1. Im Browser einloggen auf **[ionos.de](https://www.ionos.de)** → **„Login"** oben rechts.
2. Im Customer Portal: **Hosting** → Dein Webhosting-Vertrag → **„Verwalten"**.
3. Linke Seitenleiste: **„SFTP & SSH"** (alternativ unter **„Sicherheit"** oder **„Zugangsdaten"** je nach Paket).
4. Notiere Dir:
   - **Server / Host** — z. B. `home123456789.1and1-data.host` oder `access-XXXXXXX.webspace-data.io`
   - **Benutzername** — meist `u12345678` oder ähnlich
   - **Port** — `22` für SFTP
   - **Passwort** — wenn unbekannt: **„Passwort ändern"** klicken und ein neues setzen (notieren!)

> **Tipp:** Verwende **SFTP** (Port 22), nicht reines FTP. SFTP ist verschlüsselt; IONOS unterstützt es standardmäßig.

---

## Schritt 2 — Zielordner auf dem Webspace prüfen

Damit GitHub weiß, wohin die Dateien sollen:

1. Mit einem SFTP-Client (z. B. **FileZilla** oder **WinSCP**) einmalig manuell verbinden:
   - Host: aus Schritt 1
   - User / Passwort: aus Schritt 1
   - Protokoll: SFTP, Port 22
2. Schau Dir die Ordnerstruktur an. Typische Pfade bei IONOS:
   - Normales Webhosting: oft direkt **`/`** oder **`/htdocs/`**
   - ClickAndBuilds: **`/clickandbuilds/<projektname>/`**
   - Eigene Domain auf Subordner: z. B. **`/kalkulator/`**
3. Merke Dir den Pfad, in dem `index.html` liegen soll, z. B. **`/kalkulator/`**.

---

## Schritt 3 — Zugangsdaten als GitHub Secrets hinterlegen

1. Im Browser zum GitHub-Repository: **`cringler-hub/TEST`**.
2. Oben: **Settings** → linke Seitenleiste: **Secrets and variables** → **Actions** → **„New repository secret"**.
3. Lege nacheinander **vier Secrets** an (Name exakt so):

| Secret-Name | Wert |
|---|---|
| `FTP_HOST` | Server-Hostname aus Schritt 1 (ohne `sftp://`) |
| `FTP_USER` | Benutzername aus Schritt 1 |
| `FTP_PASSWORD` | Passwort aus Schritt 1 |
| `FTP_SERVER_DIR` | Zielpfad aus Schritt 2, z. B. `/kalkulator/` (mit führendem und abschließendem `/`) |

> Secrets sind verschlüsselt und werden in Logs maskiert. Auch Du selbst kannst sie nach dem Speichern nicht mehr im Klartext sehen.

---

## Schritt 4 — Deploy-Workflow im Repository anlegen

1. Im Repo: neuer Ordner und neue Datei: **`.github/workflows/deploy.yml`**
2. Inhalt einfügen:

```yaml
name: Deploy to IONOS

on:
  push:
    branches:
      - main          # Anpassen, falls Du einen anderen Produktions-Branch verwendest

jobs:
  sftp-deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: SFTP-Deploy zu IONOS
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_HOST }}
          username: ${{ secrets.FTP_USER }}
          password: ${{ secrets.FTP_PASSWORD }}
          server-dir: ${{ secrets.FTP_SERVER_DIR }}
          protocol: ftps              # IONOS unterstützt FTPS; für reines SFTP siehe Hinweis unten
          port: 21
          security: strict
          exclude: |
            **/.git*
            **/.git*/**
            **/.github/**
            **/node_modules/**
            install.php
            BENUTZERHANDBUCH.md
            RELEASE_NOTES.md
            DEPLOY_IONOS.md
            *.log
            .env*
```

3. Committen und auf den im Workflow genannten Branch (`main`) pushen.

> **Hinweis zu SFTP statt FTPS:**
> Die Action `SamKirkland/FTP-Deploy-Action` unterstützt **FTP/FTPS**, kein klassisches SFTP. IONOS bietet beides an. Wenn Du **echtes SFTP (Port 22)** willst, nimm die Alternative **`wlixcc/SFTP-Deploy-Action`**:
>
> ```yaml
>       - name: SFTP-Deploy
>         uses: wlixcc/SFTP-Deploy-Action@v1.2.4
>         with:
>           server: ${{ secrets.FTP_HOST }}
>           username: ${{ secrets.FTP_USER }}
>           password: ${{ secrets.FTP_PASSWORD }}
>           port: 22
>           local_path: './*'
>           remote_path: ${{ secrets.FTP_SERVER_DIR }}
>           sftp_only: true
> ```
>
> Diese Action lädt allerdings immer **alles** hoch, nicht nur die geänderten Dateien — bei großen Repos langsamer.

---

## Schritt 5 — Ersten Lauf prüfen

1. Im Repo oben: **„Actions"**-Tab.
2. Du siehst einen Workflow-Run **„Deploy to IONOS"**. Klick darauf.
3. Live-Log:
   - **grünes ✓** → erfolgreich; Dateien sind auf dem Webspace.
   - **rotes ✗** → Log anschauen, häufige Ursachen siehe unten.
4. Im Browser die URL Eurer Anwendung aufrufen — die neue Version sollte ausgeliefert werden (ggf. **Strg + F5** für Cache-Reload).

---

## Schritt 6 — Branch-Strategie überlegen

Empfehlung für saubere Arbeit:

- **Entwicklungs-Branch** (z. B. `develop` oder `claude/quote-calculator-tool-Js6g2`) → wird **nicht automatisch** deployed.
- **Produktions-Branch** `main` → wird automatisch deployed.
- Wenn ein Feature fertig getestet ist: per Pull Request oder `git merge` in `main` ziehen → Deployment startet automatisch.

So bleibt Production stabil, während Du parallel weiterentwickelst.

---

## Sicherheitshinweise

- **`install.php`** ist im `exclude` ausgenommen — die Datei lädst Du manuell hoch, wenn Du eine DB-Migration brauchst, und löschst sie danach wieder.
- **`api/config.php`** enthält DB-Zugangsdaten. Sie **wird** automatisch hochgeladen — überprüfe vor dem ersten Deploy, dass der Inhalt korrekt ist und keine Test-Zugangsdaten enthält. Bei Bedarf in den `exclude`-Block aufnehmen und manuell pflegen.
- **`.env`-Dateien** sind ausgeschlossen. Falls Du welche nutzt, behalten.
- **GitHub-Logs**: Secrets werden in Logs automatisch maskiert. Trotzdem nicht aus Versehen `echo $FTP_PASSWORD` in einen Workflow-Schritt schreiben.

---

## Häufige Fehler & Lösungen

**„530 Login authentication failed"**
> Benutzername oder Passwort falsch. Setze das SFTP-Passwort im IONOS-Panel neu und aktualisiere das Secret `FTP_PASSWORD` in GitHub.

**„Connection timed out" / „getaddrinfo ENOTFOUND"**
> Der Hostname stimmt nicht. Prüfe, ob `FTP_HOST` ohne `sftp://`-Präfix gespeichert ist.

**„Could not change to directory"**
> Pfad in `FTP_SERVER_DIR` existiert nicht. Mit FileZilla manuell verbinden, den richtigen Pfad finden, Secret aktualisieren.

**Workflow läuft nicht**
> Prüfe, dass die Datei wirklich unter `.github/workflows/deploy.yml` liegt und in den Branch gepusht wurde, der im `branches`-Trigger steht.

**Änderungen nicht sichtbar im Browser**
> Browser-Cache mit Strg + F5 leeren. Wenn IONOS ein Caching-Layer hat, ggf. dort invalidieren.

---

## Optional: Manueller Deploy-Trigger

Falls Du nicht jeden Push automatisch deployen willst, sondern nur auf Knopfdruck:

Im `deploy.yml` ergänzen:

```yaml
on:
  workflow_dispatch:        # → manuell startbar über die GitHub-Oberfläche
  push:
    branches: [ main ]
```

Im **Actions**-Tab erscheint dann ein **„Run workflow"**-Button.

---

## Optional: Deployment-Status als Badge

Im README einbinden:

```markdown
![Deploy](https://github.com/cringler-hub/TEST/actions/workflows/deploy.yml/badge.svg)
```

So siehst Du sofort, ob das letzte Deployment erfolgreich war.

---

_Bei Problemen bitte mit Log-Ausschnitt des Workflow-Runs melden — daraus erkennt man die Ursache meist sofort._
