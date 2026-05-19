<?php
require_once '../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

try {
    $media   = (int)$pdo->query("SELECT COUNT(*) FROM settings WHERE `key` LIKE 'media_%' AND `value` != ''")->fetchColumn();
    $content = (int)$pdo->query("SELECT COUNT(*) FROM settings WHERE `key` LIKE 'rc_%' AND `value` != ''")->fetchColumn();
    $raw     = getSetting('rosali_color_overrides', '{}');
    $colors  = count(json_decode($raw, true) ?: []);
    echo json_encode(['media' => $media, 'content' => $content, 'colors' => $colors]);
} catch (PDOException) {
    echo json_encode(['media' => 0, 'content' => 0, 'colors' => 0]);
}
