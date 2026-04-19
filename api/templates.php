<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$user   = requireAuth();
$db     = getDB();

switch ($action) {

    case 'list':
        $rows = $db->query('SELECT id, name, items FROM templates ORDER BY id')->fetchAll();
        foreach ($rows as &$r) {
            $r['items'] = json_decode($r['items'], true) ?: [];
        }
        jsonResponse(['templates' => $rows]);
        break;

    case 'save':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $id    = (int)($input['id'] ?? 0);
        $name  = trim($input['name'] ?? '');
        $items = json_encode($input['items'] ?? [], JSON_UNESCAPED_UNICODE);

        if (!$name) jsonResponse(['error' => 'Name erforderlich'], 400);

        if ($id > 0) {
            $db->prepare('UPDATE templates SET name = ?, items = ? WHERE id = ?')->execute([$name, $items, $id]);
            jsonResponse(['ok' => true, 'id' => $id]);
        } else {
            $db->prepare('INSERT INTO templates (name, items) VALUES (?, ?)')->execute([$name, $items]);
            jsonResponse(['ok' => true, 'id' => (int)$db->lastInsertId()]);
        }
        break;

    case 'delete':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $db->prepare('DELETE FROM templates WHERE id = ?')->execute([$id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
