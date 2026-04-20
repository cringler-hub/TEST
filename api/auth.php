<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'login':
        session_start();
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            jsonResponse(['error' => 'Benutzername und Passwort erforderlich'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonResponse(['error' => 'Ungültige Anmeldedaten'], 401);
        }

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        jsonResponse([
            'ok'       => true,
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);
        break;

    case 'logout':
        session_start();
        session_destroy();
        jsonResponse(['ok' => true]);
        break;

    case 'me':
        session_start();
        if (empty($_SESSION['user_id'])) {
            jsonResponse(['loggedIn' => false]);
        }
        jsonResponse([
            'loggedIn' => true,
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['role'],
        ]);
        break;

    case 'changeOwnPassword':
        $user = requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $oldPass = $input['oldPassword'] ?? '';
        $newPass = $input['newPassword'] ?? '';

        if (!$oldPass || !$newPass) {
            jsonResponse(['error' => 'Altes und neues Passwort erforderlich'], 400);
        }
        if (strlen($newPass) < 6) {
            jsonResponse(['error' => 'Neues Passwort muss mindestens 6 Zeichen lang sein'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($oldPass, $row['password_hash'])) {
            jsonResponse(['error' => 'Aktuelles Passwort ist falsch'], 401);
        }

        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $user['id']]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
