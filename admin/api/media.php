<?php
require_once '../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'media_%' AND `value` != ''");
        $stmt->execute();
        $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $slotKey = substr($row['key'], 6); // strip 'media_' prefix
            $result[$slotKey] = '../' . $row['value'];
        }
        echo json_encode($result);
    } catch (PDOException) {
        echo json_encode([]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $key  = $body['key'] ?? null;
    if (!$key) { echo json_encode(['error' => 'no key']); exit; }

    $path = getSetting('media_' . $key);
    if ($path && file_exists('../../' . $path)) unlink('../../' . $path);
    setSetting('media_' . $key, '');
    echo json_encode(['ok' => true]);

} else {
    echo json_encode(['error' => 'method not allowed']);
}
