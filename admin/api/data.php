<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    /* ?batch=rc — return all rc_* settings as {key: value} map */
    if (isset($_GET['batch']) && $_GET['batch'] === 'rc') {
        global $pdo;
        $out = [];
        try {
            $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'rc_%'");
            foreach ($stmt->fetchAll() as $r) $out[substr($r['key'], 3)] = $r['value'];
        } catch (PDOException) {}
        echo json_encode($out);
        exit;
    }
    $key = $_GET['key'] ?? null;
    if (!$key) { echo json_encode(['error' => 'no key']); exit; }
    echo json_encode(['value' => getSetting($key)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $body  = json_decode(file_get_contents('php://input'), true) ?: [];
    $key   = $body['key']   ?? null;
    $value = $body['value'] ?? '';
    if (!$key) { echo json_encode(['error' => 'no key']); exit; }
    setSetting($key, is_string($value) ? $value : json_encode($value));
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method not allowed']);
