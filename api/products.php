<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * Produktkalkulationen (Programm Management).
 * Zugriff: Admin oder Benutzer mit Flag users.can_calc_products = 1.
 */

function canCalcProducts(array $user, PDO $db): bool {
    if ($user['role'] === 'admin') return true;
    $stmt = $db->prepare('SELECT can_calc_products FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    return (bool)$stmt->fetchColumn();
}

$action = $_GET['action'] ?? '';
$user   = requireAuth();
$db     = getDB();

if (!canCalcProducts($user, $db)) {
    jsonResponse(['error' => 'Kein Zugriff auf Produktkalkulationen'], 403);
}

switch ($action) {

    case 'list':
        $cols = 'id, produkt_nr, bezeichnung, kategorie, materialnr, status, ersteller, hk_preis, vk_preis_empf, catalog_id, created_at, updated_at';
        if ($user['role'] === 'admin') {
            $rows = $db->query("SELECT $cols FROM products ORDER BY updated_at DESC")->fetchAll();
        } else {
            $stmt = $db->prepare("SELECT $cols FROM products WHERE ersteller = ? ORDER BY updated_at DESC");
            $stmt->execute([$user['username']]);
            $rows = $stmt->fetchAll();
        }
        jsonResponse(['products' => $rows]);
        break;

    case 'load':
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if (!$p) jsonResponse(['error' => 'Produkt nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $p['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }
        $p['json_data'] = json_decode($p['json_data'], true);
        jsonResponse(['product' => $p]);
        break;

    case 'save':
        $input         = json_decode(file_get_contents('php://input'), true);
        $id            = (int)($input['id'] ?? 0);
        $produktNr     = trim($input['produkt_nr']    ?? '');
        $bezeichnung   = trim($input['bezeichnung']   ?? 'Ohne Bezeichnung');
        $kategorie     = trim($input['kategorie']     ?? 'Displays');
        $materialnr    = trim($input['materialnr']    ?? '');
        $jsonData      = $input['json_data'] ?? null;
        $hkPreis       = (float)($input['hk_preis']      ?? 0);
        $vkPreis       = (float)($input['vk_preis_empf'] ?? 0);

        if (!$jsonData) jsonResponse(['error' => 'Keine Kalkulationsdaten'], 400);
        $jsonStr = json_encode($jsonData, JSON_UNESCAPED_UNICODE);

        if ($id > 0) {
            $stmt = $db->prepare('SELECT ersteller FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $ex = $stmt->fetch();
            if (!$ex) jsonResponse(['error' => 'Produkt nicht gefunden'], 404);
            if ($user['role'] !== 'admin' && $ex['ersteller'] !== $user['username']) {
                jsonResponse(['error' => 'Kein Zugriff'], 403);
            }
            $db->prepare('UPDATE products SET produkt_nr = ?, bezeichnung = ?, kategorie = ?, materialnr = ?, json_data = ?, hk_preis = ?, vk_preis_empf = ?, updated_at = NOW() WHERE id = ?')
               ->execute([$produktNr, $bezeichnung, $kategorie, $materialnr, $jsonStr, $hkPreis, $vkPreis, $id]);
            jsonResponse(['ok' => true, 'id' => $id]);
        } else {
            $db->prepare('INSERT INTO products (produkt_nr, bezeichnung, kategorie, materialnr, ersteller, json_data, hk_preis, vk_preis_empf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
               ->execute([$produktNr, $bezeichnung, $kategorie, $materialnr, $user['username'], $jsonStr, $hkPreis, $vkPreis]);
            jsonResponse(['ok' => true, 'id' => (int)$db->lastInsertId()]);
        }
        break;

    case 'setStatus':
        $input  = json_decode(file_get_contents('php://input'), true);
        $id     = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $allowed = ['In Bearbeitung', 'Freigegeben', 'Archiviert'];
        if ($id < 1 || !in_array($status, $allowed, true)) {
            jsonResponse(['error' => 'Ungültige Eingabe'], 400);
        }
        $stmt = $db->prepare('SELECT ersteller FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $ex = $stmt->fetch();
        if (!$ex) jsonResponse(['error' => 'Produkt nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $ex['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }
        $db->prepare('UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
        jsonResponse(['ok' => true]);
        break;

    case 'release':
        // Produktkalkulation in den Katalog veröffentlichen — Insert oder Update bei vorhandenem catalog_id.
        $input = json_decode(file_get_contents('php://input'), true);
        $id    = (int)($input['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if (!$p) jsonResponse(['error' => 'Produkt nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $p['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $matnr       = $p['materialnr'];
        $bezeichnung = $p['bezeichnung'];
        $kategorie   = $p['kategorie'] ?: 'Displays';
        $hk          = (float)$p['hk_preis'];
        $vk          = (float)$p['vk_preis_empf'];

        if ($p['catalog_id']) {
            // Update bestehender Katalog-Eintrag
            $db->prepare('UPDATE catalog SET materialnr = ?, beschreibung = ?, hk_preis = ?, vk_preis = ?, kategorie = ? WHERE id = ?')
               ->execute([$matnr, $bezeichnung, $hk, $vk, $kategorie, (int)$p['catalog_id']]);
            $catalogId = (int)$p['catalog_id'];
        } else {
            $db->prepare('INSERT INTO catalog (materialnr, beschreibung, hk_preis, vk_preis, kategorie, product_id) VALUES (?, ?, ?, ?, ?, ?)')
               ->execute([$matnr, $bezeichnung, $hk, $vk, $kategorie, $id]);
            $catalogId = (int)$db->lastInsertId();
            $db->prepare('UPDATE products SET catalog_id = ? WHERE id = ?')->execute([$catalogId, $id]);
        }
        $db->prepare('UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?')->execute(['Freigegeben', $id]);
        jsonResponse(['ok' => true, 'catalog_id' => $catalogId]);
        break;

    case 'delete':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);
        $stmt = $db->prepare('SELECT ersteller FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $ex = $stmt->fetch();
        if (!$ex) jsonResponse(['error' => 'Produkt nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $ex['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }
        $db->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
