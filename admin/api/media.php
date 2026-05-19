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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type     = $_GET['type']     ?? null;
    $category = $_GET['category'] ?? null;

    $sql    = "SELECT id, filename, original_name, file_type, mime_type, file_size_bytes,
                      category, assigned_to, is_published, created_at
               FROM media WHERE 1=1";
    $params = [];
    if ($type && in_array($type, ['image','video','splat'], true)) {
        $sql .= " AND file_type = ?";
        $params[] = $type;
    }
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = preg_replace('/[^a-z0-9_-]/i', '', (string)$category);
    }
    $sql .= " ORDER BY id DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['url'] = '../' . mediaUrl($r['file_type'], $r['filename']);
        }
        echo json_encode(['items' => $rows]);
    } catch (PDOException) {
        echo json_encode(['items' => []]);
    }
    exit;
}

if ($method === 'POST') {
    /* Update a media record: toggle publish, change category, change assignment */
    requireCsrf();
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $id   = (int)($body['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'error' => 'no id']); exit; }

    $fields = [];
    $vals   = [];
    if (isset($body['category'])) {
        $fields[] = '`category` = ?';
        $vals[]   = preg_replace('/[^a-z0-9_-]/i', '', (string)$body['category']) ?: 'general';
    }
    if (isset($body['assigned_to'])) {
        $fields[] = '`assigned_to` = ?';
        $vals[]   = substr((string)$body['assigned_to'], 0, 150);
    }
    if (isset($body['is_published'])) {
        $fields[] = '`is_published` = ?';
        $vals[]   = (int)(bool)$body['is_published'];
    }
    if (!$fields) { echo json_encode(['success' => false, 'error' => 'nothing to update']); exit; }

    $vals[] = $id;
    try {
        $stmt = $pdo->prepare("UPDATE media SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($vals);
        echo json_encode(['success' => true]);
    } catch (PDOException) {
        echo json_encode(['success' => false, 'error' => 'update failed']);
    }
    exit;
}

if ($method === 'DELETE') {
    requireCsrf();
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $id   = (int)($body['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'error' => 'no id']); exit; }

    try {
        $stmt = $pdo->prepare("SELECT filename, file_type FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { echo json_encode(['success' => false, 'error' => 'not found']); exit; }

        $baseDir  = realpath(__DIR__ . '/../..');
        $diskPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, mediaUrl($row['file_type'], $row['filename']));
        if (is_file($diskPath)) @unlink($diskPath);

        $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException) {
        echo json_encode(['success' => false, 'error' => 'delete failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method not allowed']);
