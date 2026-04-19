<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$user   = requireAuth();
$db     = getDB();

switch ($action) {

    case 'list':
        $rows = $db->query('SELECT id, beschreibung, hk_preis, vk_preis, kategorie FROM catalog ORDER BY kategorie, beschreibung')->fetchAll();
        jsonResponse(['products' => $rows]);
        break;

    case 'save':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $id          = (int)($input['id'] ?? 0);
        $beschreibung = trim($input['beschreibung'] ?? '');
        $hkPreis     = (float)($input['hk_preis'] ?? 0);
        $vkPreis     = (float)($input['vk_preis'] ?? 0);
        $kategorie   = trim($input['kategorie'] ?? 'Sonstiges');

        if (!$beschreibung) jsonResponse(['error' => 'Beschreibung erforderlich'], 400);

        if ($id > 0) {
            $db->prepare('UPDATE catalog SET beschreibung = ?, hk_preis = ?, vk_preis = ?, kategorie = ? WHERE id = ?')
               ->execute([$beschreibung, $hkPreis, $vkPreis, $kategorie, $id]);
            jsonResponse(['ok' => true, 'id' => $id]);
        } else {
            $db->prepare('INSERT INTO catalog (beschreibung, hk_preis, vk_preis, kategorie) VALUES (?, ?, ?, ?)')
               ->execute([$beschreibung, $hkPreis, $vkPreis, $kategorie]);
            jsonResponse(['ok' => true, 'id' => (int)$db->lastInsertId()]);
        }
        break;

    case 'bulkImport':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $products = $input['products'] ?? [];
        if (empty($products)) jsonResponse(['error' => 'Keine Produkte'], 400);

        $stmt = $db->prepare('INSERT INTO catalog (beschreibung, hk_preis, vk_preis, kategorie) VALUES (?, ?, ?, ?)');
        $count = 0;
        foreach ($products as $p) {
            $stmt->execute([
                trim($p['beschreibung'] ?? ''),
                (float)($p['hk_preis'] ?? 0),
                (float)($p['vk_preis'] ?? 0),
                trim($p['kategorie'] ?? 'Sonstiges'),
            ]);
            $count++;
        }
        jsonResponse(['ok' => true, 'imported' => $count]);
        break;

    case 'delete':
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $db->prepare('DELETE FROM catalog WHERE id = ?')->execute([$id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
