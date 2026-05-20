<?php
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: text/plain; charset=utf-8');

$base = seoSiteBase();

if (seoNoindexSite()) {
    // Staging mode — tell every crawler to stay out.
    echo "User-agent: *\n";
    echo "Disallow: /\n";
    return;
}

/* Production robots.txt */
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /admin\n";
echo "Disallow: /includes/\n";
echo "Disallow: /uploads/splats/\n";
echo "Disallow: /*.sql\$\n";
echo "\n";
echo "Sitemap: {$base}/sitemap.xml\n";
