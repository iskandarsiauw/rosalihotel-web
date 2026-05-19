<?php
require_once '../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']); exit;
}

try {
    // Delete media files from disk
    $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` LIKE 'media_%' AND `value` != ''");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $p) {
        if (file_exists('../../' . $p)) unlink('../../' . $p);
    }
    // Clear overrideable settings
    $pdo->exec("DELETE FROM settings WHERE `key` LIKE 'media_%'
                   OR `key` LIKE 'rc_%'
                   OR `key` LIKE 'layout_%'
                   OR `key` IN ('rosali_color_overrides','admin_pages')");
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
