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

function dirSize(string $dir): array {
    $count = 0; $bytes = 0;
    if (!is_dir($dir)) return [0, 0];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile() && strtolower($f->getFilename()) !== 'index.php') {
            $count++;
            $bytes += $f->getSize();
        }
    }
    return [$count, $bytes];
}

$base = realpath(__DIR__ . '/../..');
[$imgN, $imgB]    = dirSize($base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'images');
[$vidN, $vidB]    = dirSize($base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'videos');
[$splN, $splB]    = dirSize($base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'splats');

try {
    $mediaCount   = (int)$pdo->query("SELECT COUNT(*) FROM media")->fetchColumn();
    $contentCount = (int)$pdo->query("SELECT COUNT(*) FROM settings WHERE `key` LIKE 'rc_%' AND `value` IS NOT NULL AND `value` != ''")->fetchColumn();
    $colorRaw     = getSetting('rosali_color_overrides', '{}');
    $colorCount   = 0;
    $arr = json_decode($colorRaw, true);
    if (is_array($arr)) foreach ($arr as $t => $vars) if (is_array($vars) && $vars) $colorCount++;
} catch (PDOException) {
    $mediaCount = 0; $contentCount = 0; $colorCount = 0;
}

echo json_encode([
    'media'   => $mediaCount,
    'content' => $contentCount,
    'colors'  => $colorCount,
    'disk'    => [
        'images' => ['files' => $imgN, 'bytes' => $imgB],
        'videos' => ['files' => $vidN, 'bytes' => $vidB],
        'splats' => ['files' => $splN, 'bytes' => $splB],
    ],
]);
