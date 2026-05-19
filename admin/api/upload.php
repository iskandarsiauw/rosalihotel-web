<?php
require_once '../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']); exit;
}

$slotKey = $_POST['key'] ?? null;
if (!$slotKey || !isset($_FILES['file'])) {
    echo json_encode(['error' => 'missing key or file']); exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'upload error ' . $file['error']]); exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'video/mp4'];
if (!in_array($mime, $allowed, true)) {
    echo json_encode(['error' => 'file type not allowed']); exit;
}
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['error' => 'max 10 MB']); exit;
}

$slotKey = preg_replace('/[^a-z0-9_\-]/', '', strtolower($slotKey));

// Delete previous file for this slot
$oldPath = getSetting('media_' . $slotKey);
if ($oldPath && file_exists('../../' . $oldPath)) {
    unlink('../../' . $oldPath);
}

$ext = match($mime) {
    'image/jpeg' => 'jpg', 'image/png' => 'png',
    'image/webp' => 'webp', 'image/gif' => 'gif', 'video/mp4' => 'mp4',
    default      => 'bin',
};
$filename  = bin2hex(random_bytes(12)) . '.' . $ext;
$uploadDir = '../../uploads/media/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    echo json_encode(['error' => 'could not save file']); exit;
}

$relPath = 'uploads/media/' . $filename;
setSetting('media_' . $slotKey, $relPath);

echo json_encode(['ok' => true, 'url' => '../' . $relPath]);
