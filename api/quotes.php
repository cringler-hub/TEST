<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

function quoteAccessible(PDO $db, int $quoteId, array $user): bool {
    if ($user['role'] === 'admin') return true;
    $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
    $stmt->execute([$quoteId]);
    $q = $stmt->fetch();
    if (!$q) return false;
    if ($q['ersteller'] === $user['username']) return true;
    $stmt = $db->prepare('SELECT 1 FROM quote_shares WHERE quote_id = ? AND username = ?');
    $stmt->execute([$quoteId, $user['username']]);
    return (bool)$stmt->fetchColumn();
}

function quoteOwnedOrAdmin(PDO $db, int $quoteId, array $user): bool {
    if ($user['role'] === 'admin') return true;
    $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
    $stmt->execute([$quoteId]);
    $q = $stmt->fetch();
    return $q && $q['ersteller'] === $user['username'];
}

$action = $_GET['action'] ?? '';
$user   = requireAuth();

$db = getDB();

switch ($action) {

    case 'list':
        // SELECT eigene + freigegebene Angebote (für Admin: alles)
        $sql = "SELECT q.id, q.titel, q.kunde, q.ersteller, q.status, q.current_version,
                       q.angebot_nr, q.created_at, q.updated_at,
                       (SELECT COUNT(*) FROM quote_shares s WHERE s.quote_id = q.id) AS shared_count,
                       (CASE WHEN EXISTS (SELECT 1 FROM quote_shares s WHERE s.quote_id = q.id AND s.username = :me) THEN 1 ELSE 0 END) AS shared_to_me
                FROM quotes q ";
        if ($user['role'] === 'admin') {
            $sql .= 'ORDER BY q.updated_at DESC';
            $stmt = $db->prepare($sql);
            $stmt->execute([':me' => $user['username']]);
        } else {
            $sql .= 'WHERE q.ersteller = :me OR EXISTS (SELECT 1 FROM quote_shares s WHERE s.quote_id = q.id AND s.username = :me) ORDER BY q.updated_at DESC';
            $stmt = $db->prepare($sql);
            $stmt->execute([':me' => $user['username']]);
        }
        jsonResponse(['quotes' => $stmt->fetchAll()]);
        break;

    case 'load':
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);

        $stmt = $db->prepare('SELECT * FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $quote = $stmt->fetch();
        if (!$quote) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);

        $isOwner = $quote['ersteller'] === $user['username'];
        $isAdmin = $user['role'] === 'admin';
        $shareInfo = null;
        if (!$isOwner && !$isAdmin) {
            $s = $db->prepare('SELECT shared_by, shared_at FROM quote_shares WHERE quote_id = ? AND username = ?');
            $s->execute([$id, $user['username']]);
            $shareInfo = $s->fetch() ?: null;
            if (!$shareInfo) jsonResponse(['error' => 'Kein Zugriff'], 403);
        }

        $quote['json_data'] = json_decode($quote['json_data'], true);

        // Letzte Revision für Anzeige im Info-Banner mitliefern
        $revStmt = $db->prepare('SELECT version, comment, committed_by, committed_at FROM quote_revisions WHERE quote_id = ? ORDER BY version DESC LIMIT 1');
        $revStmt->execute([$id]);
        $quote['latest_revision'] = $revStmt->fetch() ?: null;

        $quote['read_only']  = !$isOwner && !$isAdmin;
        $quote['share_info'] = $shareInfo;

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
        if (!quoteAccessible($db, $id, $user)) jsonResponse(['error' => 'Kein Zugriff'], 403);

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
        if (!quoteAccessible($db, (int)$rev['quote_id'], $user)) jsonResponse(['error' => 'Kein Zugriff'], 403);

        $rev['json_data'] = json_decode($rev['json_data'], true);
        jsonResponse(['revision' => $rev]);
        break;

    case 'share':
        $input    = json_decode(file_get_contents('php://input'), true);
        $id       = (int)($input['id'] ?? 0);
        $shareUsr = trim($input['username'] ?? '');
        if ($id < 1 || !$shareUsr) jsonResponse(['error' => 'Eingabe unvollständig'], 400);
        if (!quoteOwnedOrAdmin($db, $id, $user)) jsonResponse(['error' => 'Kein Zugriff'], 403);

        $stmt = $db->prepare('SELECT ersteller FROM quotes WHERE id = ?');
        $stmt->execute([$id]);
        $q = $stmt->fetch();
        if (!$q) jsonResponse(['error' => 'Angebot nicht gefunden'], 404);
        if ($shareUsr === $q['ersteller']) jsonResponse(['error' => 'Ersteller braucht keine Freigabe'], 400);

        $exists = $db->prepare('SELECT 1 FROM users WHERE username = ?');
        $exists->execute([$shareUsr]);
        if (!$exists->fetchColumn()) jsonResponse(['error' => 'Benutzer existiert nicht'], 404);

        try {
            $db->prepare('INSERT INTO quote_shares (quote_id, username, shared_by) VALUES (?, ?, ?)')
               ->execute([$id, $shareUsr, $user['username']]);
        } catch (Exception $e) { /* Duplicate (bereits freigegeben) ist OK */ }
        jsonResponse(['ok' => true]);
        break;

    case 'unshare':
        $input    = json_decode(file_get_contents('php://input'), true);
        $id       = (int)($input['id'] ?? 0);
        $shareUsr = trim($input['username'] ?? '');
        if ($id < 1 || !$shareUsr) jsonResponse(['error' => 'Eingabe unvollständig'], 400);
        if (!quoteOwnedOrAdmin($db, $id, $user)) jsonResponse(['error' => 'Kein Zugriff'], 403);

        $db->prepare('DELETE FROM quote_shares WHERE quote_id = ? AND username = ?')
           ->execute([$id, $shareUsr]);
        jsonResponse(['ok' => true]);
        break;

    case 'listShares':
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) jsonResponse(['error' => 'Ungültige ID'], 400);
        if (!quoteOwnedOrAdmin($db, $id, $user)) jsonResponse(['error' => 'Kein Zugriff'], 403);

        $stmt = $db->prepare('SELECT username, shared_by, shared_at FROM quote_shares WHERE quote_id = ? ORDER BY shared_at DESC');
        $stmt->execute([$id]);
        jsonResponse(['shares' => $stmt->fetchAll()]);
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
