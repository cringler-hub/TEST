<?php
/**
 * Einmal-Setup für den Angebotskalkulator.
 * Legt Tabellen an und erstellt den Admin-Benutzer.
 *
 * NACH DER INSTALLATION DIESE DATEI LÖSCHEN!
 */

require_once __DIR__ . '/api/config.php';

$db = getDB();

$sql = <<<'SQL'

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS templates (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(255) NOT NULL,
    items JSON NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS catalog (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    beschreibung VARCHAR(500) NOT NULL,
    hk_preis     DECIMAL(12,2) NOT NULL DEFAULT 0,
    vk_preis     DECIMAL(12,2) NOT NULL DEFAULT 0,
    kategorie    VARCHAR(100) NOT NULL DEFAULT 'Sonstiges'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SQL;

// Jedes Statement einzeln ausführen
foreach (explode(';', $sql) as $stmt) {
    $stmt = trim($stmt);
    if ($stmt) $db->exec($stmt);
}

// Admin-Benutzer anlegen (falls noch nicht vorhanden)
$check = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
$check->execute(['admin']);
if ($check->fetchColumn() == 0) {
    $hash = password_hash('admin', PASSWORD_BCRYPT);
    $db->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)')
       ->execute(['admin', $hash, 'admin']);
    echo "✓ Admin-Benutzer angelegt (user: admin / pass: admin)\n";
} else {
    echo "✓ Admin-Benutzer existiert bereits\n";
}

// Standard-Vorlage einfügen
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
    echo "✓ Standard-Vorlage 'Anzeiger' angelegt\n";
}

echo "\n✓ Installation abgeschlossen!\n";
echo "WICHTIG: Lösche diese Datei (install.php) jetzt vom Server!\n";
echo "\nÖffne: " . dirname($_SERVER['REQUEST_URI'] ?: '') . "/index.html\n";
