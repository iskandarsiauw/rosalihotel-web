<?php
require_once '../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$counts = ['rooms' => 0, 'messages' => 0, 'gallery' => 0, 'events' => 0];
try {
    $counts['rooms']    = (int)$pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
    $counts['messages'] = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    $counts['gallery']  = (int)$pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn();
    $counts['events']   = (int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
} catch (PDOException) {}

echo json_encode($counts);
