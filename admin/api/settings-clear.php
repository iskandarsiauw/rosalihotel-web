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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']); exit;
}

requireCsrf();

try {
    $base = realpath(__DIR__ . '/../..');

    /* Delete all media files (new + legacy slot-based) from disk. */
    $stmt = $pdo->query("SELECT filename, file_type FROM media");
    foreach ($stmt->fetchAll() as $row) {
        $p = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, mediaUrl($row['file_type'], $row['filename']));
        if (is_file($p)) @unlink($p);
    }
    $pdo->exec("DELETE FROM media");

    /* Clear setting overrides but preserve active_theme/lang/splat_enabled. */
    $pdo->exec(
        "DELETE FROM settings WHERE `key` LIKE 'rc_%'
            OR `key` LIKE 'layout_%'
            OR `key` LIKE 'media_%'
            OR `key` IN ('rosali_color_overrides','admin_pages','page_visibility')"
    );

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'reset failed']);
}
