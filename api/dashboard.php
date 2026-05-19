<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * Liefert aggregierte Kennzahlen rund um die gespeicherten Angebote.
 *
 * Phase 1: Nur Admins haben Zugriff. Der Helper canAccessDashboard ist so
 * gebaut, dass sich später per Tabelle/Flag eine Freigabe-Logik nachziehen
 * lässt, ohne Endpoint-Signatur zu ändern.
 */

function canAccessDashboard(array $user, ?PDO $db = null): bool {
    if ($user['role'] === 'admin') return true;
    // Phase 2: hier könnte ein Lookup auf eine künftige Freigabe-Tabelle stehen,
    // z.B. SELECT 1 FROM dashboard_access WHERE username = ?
    return false;
}

$action = $_GET['action'] ?? 'stats';
$user   = requireAuth();
$db     = getDB();

if (!canAccessDashboard($user, $db)) {
    jsonResponse(['error' => 'Kein Zugriff auf das Dashboard'], 403);
}

switch ($action) {
    case 'stats':
        // Scope:
        //   'all'  → alle Angebote (Default für Admin)
        //   'me'   → nur eigene
        //   '<u>'  → eines Benutzers (nur Admin)
        $scope = $_GET['scope'] ?? 'all';
        $where = '';
        $params = [];
        if ($scope === 'me') {
            $where = 'WHERE ersteller = :u';
            $params[':u'] = $user['username'];
        } elseif ($scope !== 'all') {
            // Bestimmter Benutzer
            $where = 'WHERE ersteller = :u';
            $params[':u'] = $scope;
        }

        $sql = "SELECT id, titel, kunde, ersteller, status, current_version, angebot_nr,
                       json_data, created_at, updated_at
                FROM quotes $where";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $quotes = $stmt->fetchAll();

        // Aggregationen
        $statusOrder = ['Entwurf', 'Eingereicht', 'Beauftragt', 'Verloren', 'Archiviert'];
        $countByStatus = array_fill_keys($statusOrder, 0);
        $vpByStatus    = array_fill_keys($statusOrder, 0.0);
        $hkByStatus    = array_fill_keys($statusOrder, 0.0);
        $countByUser   = [];
        $vpByUser      = [];
        $vpByCustomer  = [];
        $totalVP = 0.0;
        $totalHK = 0.0;
        $totalCount = 0;
        $recent  = [];

        foreach ($quotes as $q) {
            $totalCount++;
            $status = $q['status'] ?: 'Entwurf';
            if (!isset($countByStatus[$status])) $countByStatus[$status] = 0;
            $countByStatus[$status]++;

            $totals = computeQuoteTotals($q['json_data']);
            $vp = $totals['vp'];
            $hk = $totals['hk'];

            $totalVP += $vp;
            $totalHK += $hk;
            if (!isset($vpByStatus[$status])) $vpByStatus[$status] = 0.0;
            if (!isset($hkByStatus[$status])) $hkByStatus[$status] = 0.0;
            $vpByStatus[$status] += $vp;
            $hkByStatus[$status] += $hk;

            $u = $q['ersteller'] ?: '–';
            $countByUser[$u] = ($countByUser[$u] ?? 0) + 1;
            $vpByUser[$u]    = ($vpByUser[$u]    ?? 0) + $vp;

            $kunde = trim($q['kunde'] ?? '');
            if ($kunde !== '') {
                $vpByCustomer[$kunde] = ($vpByCustomer[$kunde] ?? 0) + $vp;
            }

            $recent[] = [
                'id'         => (int)$q['id'],
                'titel'      => $q['titel'] ?: 'Ohne Titel',
                'kunde'      => $q['kunde'] ?: '–',
                'angebot_nr' => $q['angebot_nr'] ?: '',
                'ersteller'  => $u,
                'status'     => $status,
                'vp'         => $vp,
                'updated_at' => $q['updated_at'],
            ];
        }

        // Sort + Top-N
        usort($recent, fn($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));
        $recent = array_slice($recent, 0, 10);

        arsort($vpByCustomer);
        $topCustomers = [];
        $i = 0;
        foreach ($vpByCustomer as $name => $value) {
            if ($i++ >= 5) break;
            $topCustomers[] = ['kunde' => $name, 'vp' => $value, 'count' => 0];
        }
        // Anzahl pro Top-Kunde nachträglich auffüllen
        foreach ($topCustomers as &$tc) {
            $tc['count'] = countQuotesForCustomer($quotes, $tc['kunde']);
        }
        unset($tc);

        // Top-Bearbeiter
        $topUsers = [];
        $users = array_keys($countByUser);
        usort($users, fn($a, $b) => ($vpByUser[$b] ?? 0) <=> ($vpByUser[$a] ?? 0));
        foreach (array_slice($users, 0, 5) as $u) {
            $topUsers[] = [
                'ersteller' => $u,
                'count'     => $countByUser[$u],
                'vp'        => $vpByUser[$u],
            ];
        }

        // Hit-Rate (Beauftragt vs. Beauftragt+Verloren) — Anzahl
        $won  = $countByStatus['Beauftragt'] ?? 0;
        $lost = $countByStatus['Verloren']   ?? 0;
        $hitRate = ($won + $lost) > 0 ? ($won / ($won + $lost)) * 100 : null;

        $pipeline = ($vpByStatus['Entwurf'] ?? 0) + ($vpByStatus['Eingereicht'] ?? 0);
        $orderIntake = $vpByStatus['Beauftragt'] ?? 0;
        $lostValue   = $vpByStatus['Verloren']   ?? 0;

        jsonResponse([
            'scope'           => $scope,
            'total_count'     => $totalCount,
            'total_vp'        => $totalVP,
            'total_hk'        => $totalHK,
            'count_by_status' => $countByStatus,
            'vp_by_status'    => $vpByStatus,
            'pipeline'        => $pipeline,
            'order_intake'    => $orderIntake,
            'lost_value'      => $lostValue,
            'hit_rate'        => $hitRate,
            'top_customers'   => $topCustomers,
            'top_users'       => $topUsers,
            'recent'          => $recent,
        ]);
        break;

    default:
        jsonResponse(['error' => 'Unbekannte Aktion'], 400);
}

// --- Helper -----------------------------------------------------------------

/**
 * Liest aus dem gespeicherten JSON des Angebots die Gesamt-HK und Gesamt-VP.
 * Vereinfachte Re-Implementierung der Frontend-Berechnung — robust gegen
 * fehlende Felder.
 */
function computeQuoteTotals(string $jsonData): array {
    $data = json_decode($jsonData, true);
    if (!is_array($data)) return ['hk' => 0.0, 'vp' => 0.0];

    $config = $data['config'] ?? [];
    $mgk = isset($config['mgk']) ? 1 + ((float)$config['mgk'] / 100) : 1.04;
    $fgk = isset($config['fgk']) ? 1 + ((float)$config['fgk'] / 100) : 1.06;
    $rates = $config['stundensaetze'] ?? [];

    $totalHK = 0.0;
    $totalVP = 0.0;

    foreach (($data['gruppen'] ?? []) as $g) {
        $grpStk = max(1, (int)($g['stueck'] ?? 1));
        foreach (($g['positionen'] ?? []) as $p) {
            $kstr     = $p['kstr'] ?? 'material';
            $hausteil = (float)($p['hausteil'] ?? 0);
            $kaufteil = (float)($p['kaufteil'] ?? 0);
            $fremd    = (float)($p['fremd']    ?? 0);
            $std      = (float)($p['std']      ?? 0);
            $stueck   = (float)($p['stueck']   ?? 1);
            $deckung  = (float)($p['deckung']  ?? 0);
            $rate     = (float)($rates[$kstr]  ?? 0);
            // sonder kann Zahl oder Formel-String sein (z.B. "=stk*5,5") — auflösen.
            $sonderRaw = $p['sonder'] ?? 0;
            $sonder    = evalSonderFormula($sonderRaw, [
                'stk' => $stueck, 'stueck' => $stueck, 'menge' => $stueck,
                'std' => $std, 'stunden' => $std,
                'em' => $hausteil, 'eigenmaterial' => $hausteil,
                'fm' => $kaufteil, 'fremdmaterial' => $kaufteil,
                'fl' => $fremd, 'fremdleistung' => $fremd,
                'gstk' => max(1, (int)($g['stueck'] ?? 1)), 'gruppenstueck' => max(1, (int)($g['stueck'] ?? 1)),
            ]);

            if ($kstr === 'swlizenz') {
                $vp = $kaufteil;
                $hk = 0;
                $totalHK += 0;
                $totalVP += $vp * $stueck * $grpStk;
                continue;
            }
            if ($kstr === 'km') {
                $hk = $stueck * $rate + $hausteil + $kaufteil * $mgk + $fremd * $mgk + $sonder;
                $d  = $deckung / 100;
                $vp = $d < 1 ? $hk / (1 - $d) : $hk;
                $totalHK += $hk * $grpStk;
                $totalVP += $vp * $grpStk;
                continue;
            }
            $stdKosten = $std * $rate;
            if ($kstr === 'fertigung') $stdKosten *= $fgk;
            $hk = $hausteil + $kaufteil * $mgk + $fremd * $mgk + $sonder + $stdKosten;
            $d  = $deckung / 100;
            $vp = $d < 1 ? $hk / (1 - $d) : $hk;
            $totalHK += $hk * $stueck * $grpStk;
            $totalVP += $vp * $stueck * $grpStk;
        }
    }

    return ['hk' => $totalHK, 'vp' => $totalVP];
}

/**
 * Werte ein Sonderkosten-Feld aus. Akzeptiert Zahl oder Formel mit "="-Präfix.
 * Variablen wie stk/std/em/fm/fl/gstk werden aus $vars eingesetzt. Strenges
 * Whitelisting verhindert Code-Injection.
 */
function evalSonderFormula($raw, array $vars): float {
    if (is_numeric($raw)) return max(0.0, (float)$raw);
    $s = trim((string)$raw);
    if ($s === '') return 0.0;
    if ($s[0] !== '=') {
        // DE-Format nur wenn ein Komma vorkommt — sonst Punkt als Dezimaltrenner.
        if (strpos($s, ',') !== false) {
            $n = (float)str_replace(',', '.', preg_replace('/\./', '', $s));
        } else {
            $n = (float)$s;
        }
        return max(0.0, $n);
    }
    $expr = substr($s, 1);
    $expr = preg_replace_callback('/\b([a-zA-Z_][a-zA-Z_0-9]*)\b/', function ($m) use ($vars) {
        $name = strtolower($m[1]);
        return isset($vars[$name]) ? '(' . (float)$vars[$name] . ')' : '0';
    }, $expr);
    $expr = str_replace(',', '.', $expr);
    if (preg_match('/[^0-9+\-*\/().\s]/', $expr) || trim($expr) === '') return 0.0;
    try {
        $result = null;
        // Sichere Auswertung über eval — Whitelist oben sichert ab.
        @eval('$result = ' . $expr . ';');
        return is_numeric($result) && is_finite((float)$result) ? max(0.0, (float)$result) : 0.0;
    } catch (Throwable $e) {
        return 0.0;
    }
}

function countQuotesForCustomer(array $quotes, string $kunde): int {
    $n = 0;
    foreach ($quotes as $q) {
        if (trim($q['kunde'] ?? '') === $kunde) $n++;
    }
    return $n;
}
