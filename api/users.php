<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'list':
        requireAdmin();
        $db = getDB();
        $rows = $db->query('SELECT id, username, role, created_at FROM users ORDER BY id')->fetchAll();
        jsonResponse(['users' => $rows]);
        break;

    case 'create':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $role     = ($input['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

        if (!$username || !$password) {
            jsonResponse(['error' => 'Benutzername und Passwort erforderlich'], 400);
        }

        $db = getDB();
        $exists = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $exists->execute([$username]);
        if ($exists->fetchColumn() > 0) {
            jsonResponse(['error' => 'Benutzername existiert bereits'], 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, $hash, $role]);

        jsonResponse(['ok' => true, 'id' => (int)$db->lastInsertId()]);
        break;

    case 'delete':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);

        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $db = getDB();
        $user = $db->prepare('SELECT username FROM users WHERE id = ?');
        $user->execute([$id]);
        $u = $user->fetch();
        if (!$u) jsonResponse(['error' => 'Benutzer nicht gefunden'], 404);
        if ($u['username'] === 'admin') jsonResponse(['error' => 'Admin-Konto kann nicht gelöscht werden'], 403);

        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        jsonResponse(['ok' => true]);
        break;

    case 'changePassword':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        $newPass = $input['password'] ?? '';

        if ($id < 1 || !$newPass) jsonResponse(['error' => 'ID und Passwort erforderlich'], 400);

        $db = getDB();
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
