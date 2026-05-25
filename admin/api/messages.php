<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

/* ── GET: list / export ─────────────────────────────────────── */
if ($method === 'GET') {
    $channel = $_GET['channel'] ?? '';
    $type    = $_GET['type']    ?? '';
    $read    = $_GET['read']    ?? '';
    $q       = trim($_GET['q']  ?? '');
    $from    = $_GET['from']    ?? '';
    $to      = $_GET['to']      ?? '';

    $where  = [];
    $params = [];
    if (in_array($channel, ['email','whatsapp'], true)) {
        $where[] = 'channel = ?'; $params[] = $channel;
    }
    if (in_array($type, ['stay','event','cafe'], true)) {
        $where[] = 'inquiry_type = ?'; $params[] = $type;
    }
    if ($read === '0' || $read === '1') {
        $where[] = 'is_read = ?'; $params[] = (int)$read;
    }
    if ($q !== '') {
        $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)';
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like, $like);
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $where[] = 'created_at >= ?'; $params[] = $from . ' 00:00:00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $where[] = 'created_at <= ?'; $params[] = $to   . ' 23:59:59';
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    if ($action === 'export') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="rosali_contact_log_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        // UTF-8 BOM so Excel opens it correctly
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID','Date','Channel','Inquiry Type','Name','Email','Phone','Message','IP','Read']);
        try {
            $stmt = $pdo->prepare("SELECT * FROM messages $whereSql ORDER BY created_at DESC");
            $stmt->execute($params);
            while ($r = $stmt->fetch()) {
                fputcsv($out, [
                    $r['id'], $r['created_at'], $r['channel'], $r['inquiry_type'],
                    $r['name'], $r['email'], $r['phone'], $r['message'],
                    $r['ip_address'] ?? '', $r['is_read'] ? '1' : '0',
                ]);
            }
        } catch (PDOException) {}
        fclose($out);
        exit;
    }

    header('Content-Type: application/json');
    try {
        $limit  = min(500, max(1, (int)($_GET['limit']  ?? 100)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM messages $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $listStmt = $pdo->prepare("SELECT id, channel, inquiry_type, name, email, phone, message, ip_address, is_read, created_at
                                   FROM messages $whereSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $listStmt->execute($params);
        $rows = $listStmt->fetchAll();

        // Aggregate stats over the same filter (helpful tiles)
        $statsStmt = $pdo->prepare("SELECT channel, COUNT(*) AS n, SUM(is_read = 0) AS unread
                                    FROM messages $whereSql GROUP BY channel");
        $statsStmt->execute($params);
        $stats = ['email'=>['n'=>0,'unread'=>0], 'whatsapp'=>['n'=>0,'unread'=>0]];
        foreach ($statsStmt->fetchAll() as $r) {
            $stats[$r['channel']] = ['n'=>(int)$r['n'], 'unread'=>(int)$r['unread']];
        }

        echo json_encode(['rows'=>$rows, 'total'=>$total, 'stats'=>$stats]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error'=>'db error']);
    }
    exit;
}

/* ── POST: mark read/unread, delete ─────────────────────────── */
if ($method === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $op   = $body['op']  ?? '';
    $id   = (int)($body['id'] ?? 0);
    $ids  = array_filter(array_map('intval', $body['ids'] ?? []), fn($v) => $v > 0);

    try {
        if ($op === 'mark_read' && $id > 0) {
            $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        } elseif ($op === 'mark_unread' && $id > 0) {
            $pdo->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
        } elseif ($op === 'delete' && $id > 0) {
            $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        } elseif ($op === 'delete_many' && $ids) {
            $place = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM messages WHERE id IN ($place)")->execute(array_values($ids));
        } elseif ($op === 'mark_all_read') {
            $pdo->query('UPDATE messages SET is_read = 1 WHERE is_read = 0');
        } else {
            http_response_code(400);
            echo json_encode(['error'=>'invalid op']);
            exit;
        }
        echo json_encode(['ok'=>true]);
    } catch (PDOException) {
        http_response_code(500);
        echo json_encode(['error'=>'db error']);
    }
    exit;
}

http_response_code(405);
header('Content-Type: application/json');
echo json_encode(['error' => 'method not allowed']);
