<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method not allowed']);
    exit;
}

// Honeypot — silently accept and discard if bots filled the hidden field
if (!empty($_POST['website'] ?? '')) {
    echo json_encode(['ok' => true]);
    exit;
}

$first = trim((string)($_POST['first']   ?? ''));
$last  = trim((string)($_POST['last']    ?? ''));
$phone = trim((string)($_POST['phone']   ?? ''));
$email = trim((string)($_POST['email']   ?? ''));
$msg   = trim((string)($_POST['message'] ?? ''));

if ($first === '' || $email === '' || $msg === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please provide your name, a valid email, and a message.']);
    exit;
}

// Length caps to keep mail bodies sane
$first = mb_substr($first, 0, 80);
$last  = mb_substr($last,  0, 80);
$phone = mb_substr($phone, 0, 40);
$msg   = mb_substr($msg,   0, 4000);

$to = trim(getSetting('contact_email_to', 'rosalihotel@gmail.com'));
$cc = trim(getSetting('contact_email_cc', ''));

if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    http_response_code(500);
    echo json_encode(['error' => 'Email destination is not configured.']);
    exit;
}

$name    = trim($first . ' ' . $last);
$subject = '[Rosali Website] Contact from ' . $name;
$body =
    "Name:    {$name}\r\n"
  . "Email:   {$email}\r\n"
  . "Phone:   {$phone}\r\n"
  . "\r\n"
  . "Message:\r\n{$msg}\r\n";

$host = $_SERVER['SERVER_NAME'] ?? 'rosalihotel.id';
$fromDomain = preg_replace('/^www\./', '', $host);

$headers   = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: Rosali Hotel Website <no-reply@' . $fromDomain . '>';
$headers[] = 'Reply-To: ' . $email;

if ($cc !== '') {
    $ccList = array_filter(
        array_map('trim', explode(',', $cc)),
        fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
    );
    if ($ccList) $headers[] = 'Cc: ' . implode(', ', $ccList);
}

$mailOk = @mail($to, $subject, $body, implode("\r\n", $headers));

// Persist to messages table so it's never lost even if mail() fails (XAMPP, etc.)
try {
    global $pdo;
    $stmt = $pdo->prepare(
        'INSERT INTO messages (name, email, phone, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $phone, $msg]);
} catch (PDOException) {}

echo json_encode(['ok' => true, 'sent' => (bool)$mailOk]);
