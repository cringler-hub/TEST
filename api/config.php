<?php
// ============================================================
// DB-Konfiguration — hier deine Zugangsdaten eintragen
// ============================================================

define('DB_HOST', 'db5020268215.hosting-data.io');
define('DB_NAME', 'dbs15580207');
define('DB_USER', 'dbu1913064');
define('DB_PASS', 'Funkwerk123!');
define('DB_CHARSET', 'utf8mb4');

// Session-Einstellungen (nur setzen wenn Session noch nicht gestartet)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth(): array {
    session_start();
    if (empty($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Nicht angemeldet'], 401);
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role'],
    ];
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        jsonResponse(['error' => 'Nur für Administratoren'], 403);
    }
    return $user;
}
