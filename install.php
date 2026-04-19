<?php
/**
 * Einmal-Setup für den Angebotskalkulator.
 * Legt Tabellen an und erstellt den Admin-Benutzer.
 *
 * NACH DER INSTALLATION DIESE DATEI LÖSCHEN!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Angebotskalkulator — Installation</h2>";

// Schritt 1: Config laden
echo "<p>1. Lade Konfiguration...</p>";
require_once __DIR__ . '/api/config.php';
echo "<p style='color:green'>✓ Config geladen</p>";
echo "<p>→ Host: <code>" . DB_HOST . "</code></p>";
echo "<p>→ Datenbank: <code>" . DB_NAME . "</code></p>";
echo "<p>→ Benutzer: <code>" . DB_USER . "</code></p>";

// Schritt 2: Verbindung testen
echo "<p>2. Teste DB-Verbindung...</p>";
try {
    $db = getDB();
    echo "<p style='color:green'>✓ Verbindung erfolgreich!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Verbindung fehlgeschlagen: <b>" . htmlspecialchars($e->getMessage()) . "</b></p>";
    echo "<p>Bitte prüfe die Zugangsdaten in <code>api/config.php</code>:</p>";
    echo "<ul>";
    echo "<li>Stimmt der <b>DB_NAME</b>? (So wie im Hosting-Panel angezeigt)</li>";
    echo "<li>Stimmt das <b>DB_PASS</b>?</li>";
    echo "<li>Stimmt der <b>DB_HOST</b>?</li>";
    echo "</ul>";
    exit;
}

// Schritt 3: Tabellen anlegen
echo "<p>3. Lege Tabellen an...</p>";
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role          ENUM('user','admin') NOT NULL DEFAULT 'user',
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green'>✓ Tabelle <code>users</code></p>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS quotes (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            titel       VARCHAR(255) NOT NULL DEFAULT '',
            kunde       VARCHAR(255) NOT NULL DEFAULT '',
            ersteller   VARCHAR(100) NOT NULL,
            angebot_nr  VARCHAR(100) NOT NULL DEFAULT '',
            json_data   LONGTEXT NOT NULL,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_ersteller (ersteller),
            INDEX idx_updated   (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green'>✓ Tabelle <code>quotes</code></p>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS templates (
            id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name  VARCHAR(255) NOT NULL,
            items JSON NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green'>✓ Tabelle <code>templates</code></p>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS catalog (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            beschreibung VARCHAR(500) NOT NULL,
            hk_preis     DECIMAL(12,2) NOT NULL DEFAULT 0,
            vk_preis     DECIMAL(12,2) NOT NULL DEFAULT 0,
            kategorie    VARCHAR(100) NOT NULL DEFAULT 'Sonstiges'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green'>✓ Tabelle <code>catalog</code></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>✗ Fehler bei Tabellenerstellung: <b>" . htmlspecialchars($e->getMessage()) . "</b></p>";
    exit;
}

// Schritt 4: Admin-Benutzer
echo "<p>4. Admin-Benutzer...</p>";
try {
    $check = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $check->execute(['admin']);
    if ($check->fetchColumn() == 0) {
        $hash = password_hash('admin', PASSWORD_BCRYPT);
        $db->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)')
           ->execute(['admin', $hash, 'admin']);
        echo "<p style='color:green'>✓ Admin-Benutzer angelegt (user: <b>admin</b> / pass: <b>admin</b>)</p>";
    } else {
        echo "<p style='color:green'>✓ Admin-Benutzer existiert bereits</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Schritt 5: Standard-Vorlage
echo "<p>5. Standard-Vorlage...</p>";
try {
    $check = $db->query('SELECT COUNT(*) FROM templates');
    if ($check->fetchColumn() == 0) {
        $items = json_encode([
            ['desc' => 'Material',          'kstr' => 'material'],
            ['desc' => 'Konstruktion',      'kstr' => 'konstr'],
            ['desc' => 'HW Design',         'kstr' => 'hwdesign'],
            ['desc' => 'Projektmanagement', 'kstr' => 'pm'],
            ['desc' => 'Versand',           'kstr' => 'sonder'],
            ['desc' => 'Sonstiges',         'kstr' => 'sonder'],
        ], JSON_UNESCAPED_UNICODE);
        $db->prepare('INSERT INTO templates (name, items) VALUES (?, ?)')->execute(['Anzeiger', $items]);
        echo "<p style='color:green'>✓ Vorlage 'Anzeiger' angelegt</p>";
    } else {
        echo "<p style='color:green'>✓ Vorlagen existieren bereits</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3 style='color:green'>✓ Installation abgeschlossen!</h3>";
echo "<p><b>WICHTIG:</b> Lösche diese Datei (<code>install.php</code>) jetzt vom Server!</p>";
echo "<p>→ <a href='index.html'>Zum Angebotskalkulator</a></p>";
