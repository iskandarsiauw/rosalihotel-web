<?php
/* Logs the *intent* to contact via WhatsApp.
   Fired by contact.php before opening wa.me — we cannot confirm whether
   the user actually pressed Send in WhatsApp, only that they clicked. */

require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method not allowed']);
    exit;
}

// sendBeacon delivers a Blob; parse JSON body if Content-Type is JSON.
$ct = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($ct, 'application/json') !== false) {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $body = $_POST;
}

$name  = trim((string)($body['name']         ?? ''));
$phone = trim((string)($body['phone']        ?? ''));
$msg   = trim((string)($body['message']      ?? ''));
$type  = trim((string)($body['inquiry_type'] ?? ''));
if (!in_array($type, ['stay','event','cafe',''], true)) $type = '';

// Silent accept if nothing useful — don't pollute the log
if ($name === '' && $phone === '' && $msg === '') {
    http_response_code(204);
    exit;
}

// Length caps
$name  = mb_substr($name,  0, 160);
$phone = mb_substr($phone, 0, 40);
$msg   = mb_substr($msg,   0, 4000);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO messages (channel, inquiry_type, name, email, phone, message, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute(['whatsapp', $type, $name, '', $phone, $msg, $_SERVER['REMOTE_ADDR'] ?? '']);
} catch (PDOException) {
    http_response_code(500);
    echo json_encode(['error' => 'log failed']);
    exit;
}

echo json_encode(['ok' => true]);
