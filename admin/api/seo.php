<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/seo.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$defs = seoPageDefaults();
$pages = [];
foreach ($defs as $id => $def) {
    $pages[$id] = [
        'id'             => $id,
        'path'           => $def['path'],
        'defaults'       => [
            'title_en'       => $def['title_en'],
            'title_id'       => $def['title_id'],
            'description_en' => $def['description_en'],
            'description_id' => $def['description_id'],
            'og_slot'        => $def['og_slot'] ?? null,
        ],
        'overrides' => [
            'title_en'       => getSetting("seo_{$id}_title_en", ''),
            'title_id'       => getSetting("seo_{$id}_title_id", ''),
            'description_en' => getSetting("seo_{$id}_description_en", ''),
            'description_id' => getSetting("seo_{$id}_description_id", ''),
            'keywords'       => getSetting("seo_{$id}_keywords", ''),
            'og_image'       => getSetting("seo_{$id}_og_image", ''),
            'noindex'        => getSetting("seo_{$id}_noindex", '0') === '1',
        ],
    ];
    if (!empty($def['og_slot'])) {
        $m = mediaForSlot($def['og_slot']);
        $pages[$id]['defaults']['og_slot_url'] = $m['url'] ?? null;
    }
}

echo json_encode([
    'site_url' => getSetting('seo_site_url', ''),
    'pages'    => $pages,
]);
