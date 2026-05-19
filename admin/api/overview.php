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

$counts = ['rooms' => 0, 'messages' => 0, 'gallery' => 0, 'events' => 0];
try {
    $counts['rooms']    = (int)$pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
    $counts['messages'] = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    /* Gallery count = published images in media table (new system) */
    $counts['gallery']  = (int)$pdo->query("SELECT COUNT(*) FROM media WHERE file_type = 'image' AND is_published = 1")->fetchColumn();
    $counts['events']   = (int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
} catch (PDOException) {}

echo json_encode($counts);
