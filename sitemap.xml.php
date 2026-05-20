<?php
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=utf-8');

$base       = seoSiteBase();
$visibility = pageVisibility();
$defs       = seoPageDefaults();

/* Map page id → visibility key (most match 1:1). */
$visMap = [
    'home'=>'home', 'rooms'=>'rooms', 'events'=>'events', 'cafe'=>'cafe',
    'gallery'=>'gallery', 'tourism'=>'tourism', 'contact'=>'contact',
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$today = date('Y-m-d');
foreach ($defs as $pageId => $def) {
    $visKey = $visMap[$pageId] ?? $pageId;
    if (isset($visibility[$visKey]) && $visibility[$visKey] === false) continue;
    $noindex = getSetting("seo_{$pageId}_noindex", '0') === '1';
    if ($noindex) continue;

    $loc = htmlspecialchars($base . '/' . $def['path'], ENT_QUOTES, 'UTF-8');
    $prio = ($pageId === 'home') ? '1.0' : '0.8';

    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>{$prio}</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>' . "\n";
