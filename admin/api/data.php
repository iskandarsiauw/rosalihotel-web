<?php
require_once '../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $key = $_GET['key'] ?? null;
    if (!$key) { echo json_encode(['error' => 'no key']); exit; }
    echo json_encode(['value' => getSetting($key)]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $key   = $body['key']   ?? null;
    $value = $body['value'] ?? '';
    if (!$key) { echo json_encode(['error' => 'no key']); exit; }
    setSetting($key, $value);
    echo json_encode(['ok' => true]);

} else {
    echo json_encode(['error' => 'method not allowed']);
}
