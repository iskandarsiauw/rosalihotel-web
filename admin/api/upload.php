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

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['file']['error'] ?? -1;
    $msg  = match($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large (PHP limit)',
        UPLOAD_ERR_PARTIAL    => 'File upload incomplete',
        UPLOAD_ERR_NO_FILE    => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Server tmp dir missing',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
        default               => 'Unknown upload error',
    };
    echo json_encode(['success' => false, 'error' => $msg]); exit;
}

$file        = $_FILES['file'];
$originalName = $file['name'];
$tmp         = $file['tmp_name'];
$size        = (int)$file['size'];

$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

/* Detect file type. Splats are identified by extension; images/video by MIME. */
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($tmp) ?: 'application/octet-stream';

$fileType   = null;
$destSubdir = null;
$maxBytes   = 0;
$newExt     = $ext;

if (in_array($ext, ['splat', 'ksplat'], true)) {
    $fileType   = 'splat';
    $destSubdir = 'uploads/splats/';
    $maxBytes   = 500 * 1024 * 1024;
    $newExt     = $ext;
    /* Splat upload requires the feature flag */
    if (!isSplatEnabled()) {
        echo json_encode(['success' => false, 'error' => '3D tour uploads disabled — enable in Settings']);
        exit;
    }
} elseif (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    $fileType   = 'image';
    $destSubdir = 'uploads/media/images/';
    $maxBytes   = 10 * 1024 * 1024;
    $newExt     = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    };
} elseif ($mime === 'video/mp4') {
    $fileType   = 'video';
    $destSubdir = 'uploads/media/videos/';
    $maxBytes   = 200 * 1024 * 1024;
    $newExt     = 'mp4';
} else {
    echo json_encode(['success' => false, 'error' => "File type not allowed (mime: $mime, ext: $ext)"]);
    exit;
}

if ($size > $maxBytes) {
    echo json_encode(['success' => false, 'error' => 'File exceeds max size of ' . round($maxBytes / 1048576) . ' MB']);
    exit;
}

$baseDir = realpath(__DIR__ . '/../..');
$destDir = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $destSubdir);
if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
    echo json_encode(['success' => false, 'error' => 'Could not create upload directory']);
    exit;
}

/* Drop an empty index.php into the destination dir to block listing if missing. */
$idxFile = $destDir . DIRECTORY_SEPARATOR . 'index.php';
if (!file_exists($idxFile)) @file_put_contents($idxFile, "<?php // no directory listing\n");

$filename = bin2hex(random_bytes(16)) . '.' . $newExt;
$destPath = $destDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($tmp, $destPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
    exit;
}

$category    = isset($_POST['category']) ? preg_replace('/[^a-z0-9_-]/i', '', (string)$_POST['category']) : 'general';
$assignedTo  = isset($_POST['assigned_to']) ? substr((string)$_POST['assigned_to'], 0, 150) : '';
$isPublished = isset($_POST['is_published']) ? (int)(bool)$_POST['is_published'] : 1;

/* Legacy "key" param means slot-based assignment for RosaliImg slots */
if (!$assignedTo && isset($_POST['key'])) {
    $slotKey   = preg_replace('/[^a-z0-9_\-]/i', '', strtolower((string)$_POST['key']));
    $assignedTo = 'slot:' . $slotKey;

    /* Replace any prior file in this slot — delete old assets so disk doesn't bloat. */
    try {
        $stmt = $pdo->prepare("SELECT id, filename, file_type FROM media WHERE assigned_to = ?");
        $stmt->execute([$assignedTo]);
        foreach ($stmt->fetchAll() as $old) {
            $oldPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, mediaUrl($old['file_type'], $old['filename']));
            if (is_file($oldPath)) @unlink($oldPath);
            $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$old['id']]);
        }
    } catch (PDOException) {}
}

if ($fileType === 'splat') $category = 'room_tour';

try {
    $stmt = $pdo->prepare(
        "INSERT INTO media (filename, original_name, file_type, mime_type, file_size_bytes, category, assigned_to, is_published, uploaded_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $filename, $originalName, $fileType, $mime, $size,
        $category ?: 'general', $assignedTo, $isPublished, (int)($_SESSION['admin_id'] ?? 0)
    ]);
    $id = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    @unlink($destPath);
    echo json_encode(['success' => false, 'error' => 'DB insert failed']);
    exit;
}

$url = mediaUrl($fileType, $filename);
echo json_encode([
    'success'   => true,
    'id'        => $id,
    'filename'  => $filename,
    'url'       => '../' . $url,
    'file_type' => $fileType,
]);
