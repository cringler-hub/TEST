<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

// Debug: PHP-Fehler als JSON-Antwort statt 500-Crash zurückliefern
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
set_exception_handler(function ($e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'PHP-Fehler: ' . $e->getMessage(),
        'file'  => basename($e->getFile()),
        'line'  => $e->getLine()
    ]);
    exit;
});

$action = $_GET['action'] ?? '';
$user   = requireAuth();

$db = getDB();

switch ($action) {

    case 'list':
        $cols = 'id, titel, kunde, ersteller, status, current_version, angebot_nr, created_at, updated_at';
        if ($user['role'] === 'admin') {
            $rows = $db->query("SELECT $cols FROM quotes ORDER BY updated_at DESC")->fetchAll();
        } else {
            $stmt = $db->prepare("SELECT $cols FROM quotes WHERE ersteller = ? ORDER BY updated_at DESC");
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

        // Letzte Revision für Anzeige im Info-Banner mitliefern
        $revStmt = $db->prepare('SELECT version, comment, committed_by, committed_at FROM quote_revisions WHERE quote_id = ? ORDER BY version DESC LIMIT 1');
        $revStmt->execute([$id]);
        $quote['latest_revision'] = $revStmt->fetch() ?: null;

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

    // --- Revisionen / Snapshots ---

    case 'commit':
        $input = json_decode(file_get_contents('php://input'), true);
        $id      = (int)($input['id'] ?? 0);
        $comment = trim($input['comment'] ?? '');
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $stmt = $db->prepare('SELECT ersteller, json_data, current_version FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $q = $stmt->fetch();
        if (!$q) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $q['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $nextVersion = ((int)$q['current_version']) + 1;
        $db->prepare('INSERT INTO quote_revisions (quote_id, version, json_data, comment, committed_by) VALUES (?, ?, ?, ?, ?)')
           ->execute([$id, $nextVersion, $q['json_data'], $comment, $user['username']]);
        $db->prepare('UPDATE quotes SET current_version = ?, updated_at = NOW() WHERE id = ?')
           ->execute([$nextVersion, $id]);

        jsonResponse(['ok' => true, 'version' => $nextVersion]);
        break;

    case 'listRevisions':
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $existing['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $stmt = $db->prepare('SELECT id, version, comment, committed_by, committed_at FROM quote_revisions WHERE quote_id = ? ORDER BY version DESC');
        $stmt->execute([$id]);
        jsonResponse(['revisions' => $stmt->fetchAll()]);
        break;

    case 'loadRevision':
        $revId = (int)($_GET['revision_id'] ?? 0);
        if ($revId < 1) jsonResponse(['error' => 'Ungültige Revisions-ID'], 400);

        $stmt = $db->prepare('SELECT r.*, q.ersteller, q.titel, q.kunde, q.angebot_nr FROM quote_revisions r JOIN quotes q ON r.quote_id = q.id WHERE r.id = ?');
        $stmt->execute([$revId]);
        $rev = $stmt->fetch();
        if (!$rev) jsonResponse(['error' => 'Revision nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $rev['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $rev['json_data'] = json_decode($rev['json_data'], true);
        jsonResponse(['revision' => $rev]);
        break;

    case 'setStatus':
        $input = json_decode(file_get_contents('php://input'), true);
        $id     = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $allowed = ['Entwurf', 'Eingereicht', 'Beauftragt', 'Verloren', 'Archiviert'];
        if ($id < 1 || !in_array($status, $allowed, true)) {
            jsonResponse(['error' => 'Ungültige Eingabe'], 400);
        }

        $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
        if ($user['role'] !== 'admin' && $existing['ersteller'] !== $user['username']) {
            jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $db->prepare('UPDATE quotes SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}
