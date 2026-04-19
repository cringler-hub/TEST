<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$user   = requireAuth();

$db = getDB();

switch ($action) {

    case 'list':
        if ($user['role'] === 'admin') {
            $rows = $db->query('SELECT id, titel, kunde, ersteller, angebot_nr, created_at, updated_at FROM quotes ORDER BY updated_at DESC')->fetchAll();
        } else {
            $stmt = $db->prepare('SELECT id, titel, kunde, ersteller, angebot_nr, created_at, updated_at FROM quotes WHERE ersteller = ? ORDER BY updated_at DESC');
            $stmt->execute([$user['username']]);
            $rows = $stmt->fetchAll();
        }
        jsonResponse(['quotes' => $rows]);
        break;

    case 'load':
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $stmt = $db->prepare('SELECT * FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $quote = $stmt->fetch();

        if (!$quote) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $quote['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $quote['json_data'] = json_decode($quote['json_data'], true);
        jsonResponse(['quote' => $quote]);
        break;

    case 'save':
        $input = json_decode(file_get_contents('php://input'), true);
        $id        = (int)($input['id'] ?? 0);
        $titel     = trim($input['titel'] ?? 'Ohne Titel');
        $kunde     = trim($input['kunde'] ?? '');
        $angebotNr = trim($input['angebot_nr'] ?? '');
        $jsonData  = $input['json_data'] ?? null;

        if (!$jsonData) jsonResponse(['error' => 'Keine Angebotsdaten'], 400);

        $jsonStr = json_encode($jsonData, JSON_UNESCAPED_UNICODE);

        if ($id > 0) {
            $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
            $stmt->execute([$id]);
            $existing = $stmt->fetch();

            if (!$existing) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
            if ($user['role'] !== 'admin' && $existing['ersteller'] !== $user['username']) {
                jsonResponse(['error' => 'Kein Zugriff'], 403);
            }

            $db->prepare('UPDATE quotes SET titel = ?, kunde = ?, angebot_nr = ?, json_data = ?, updated_at = NOW() WHERE id = ?')
               ->execute([$titel, $kunde, $angebotNr, $jsonStr, $id]);

            jsonResponse(['ok' => true, 'id' => $id]);
        } else {
            $db->prepare('INSERT INTO quotes (titel, kunde, ersteller, angebot_nr, json_data) VALUES (?, ?, ?, ?, ?)')
               ->execute([$titel, $kunde, $user['username'], $angebotNr, $jsonStr]);

            jsonResponse(['ok' => true, 'id' => (int)$db->lastInsertId()]);
        }
        break;

    case 'delete':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $existing['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $db->prepare('DELETE FROM quotes WHERE id = ?')->execute([$id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
