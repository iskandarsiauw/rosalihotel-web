<?php
/*
 * SEO helper — emits <title>, meta description, canonical, Open Graph,
 * Twitter Card, robots, and JSON-LD schema for the Hotel.
 *
 * Defaults are hardcoded per page id. Admins can override title /
 * description / og_image per page via the SEO tab, stored in the
 * settings table under keys like 'seo_home_title'.
 *
 * Usage in a front-end page (inside <head>):
 *   <?php seoMeta('home'); ?>
 */

require_once __DIR__ . '/functions.php';

function seoPageDefaults(): array {
    return [
        'home' => [
            'title_en'       => 'Rosali Hotel & Restaurant — Boutique Garden Hotel in Situbondo, East Java',
            'title_id'       => 'Rosali Hotel & Restoran — Hotel Butik di Situbondo, Jawa Timur',
            'description_en' => 'Boutique garden hotel in the heart of Situbondo, East Java. Modern traditional villas, lush tropical gardens, specialty café, and warm Indonesian hospitality.',
            'description_id' => 'Hotel butik di pusat Situbondo, Jawa Timur. Villa modern tradisional, taman tropis yang rimbun, café specialty, dan keramahan Indonesia yang tulus.',
            'path'           => 'index.php',
            'og_slot'        => 'hero___hotel_garden_entrance___aerial_view_at_golden_hour',
        ],
        'rooms' => [
            'title_en'       => 'Rooms & Villas — Rosali Hotel Situbondo',
            'title_id'       => 'Kamar & Villa — Rosali Hotel Situbondo',
            'description_en' => 'Five unique room categories — Wooden, Oriental, VIP, Superior, and Standard — set across garden clusters. Book a stay at Rosali Hotel Situbondo.',
            'description_id' => 'Lima kategori kamar unik — Wooden, Oriental, VIP, Superior, dan Standard — tersebar di kluster taman. Pesan kamar di Rosali Hotel Situbondo.',
            'path'           => 'rooms.php',
            'og_slot'        => 'rooms_hero___garden_villa_exterior___bungalow_cluster_aerial',
        ],
        'events' => [
            'title_en'       => 'Weddings, Meetings & Events — Rosali Hotel Situbondo',
            'title_id'       => 'Pernikahan, Rapat & Acara — Rosali Hotel Situbondo',
            'description_en' => 'Garden weddings, meeting rooms, and banquet spaces in Situbondo. Jasmine, Tulip, and Lavender halls, plus the Dream Garden ceremony space.',
            'description_id' => 'Pernikahan taman, ruang rapat, dan banquet di Situbondo. Aula Jasmine, Tulip, Lavender, serta Dream Garden untuk upacara.',
            'path'           => 'events.php',
            'og_slot'        => 'events_hero___dream_garden_wedding___meeting_setup_aerial',
        ],
        'cafe' => [
            'title_en'       => 'Rosa De 5 Café — Specialty Coffee & Garden Dining',
            'title_id'       => 'Rosa De 5 Café — Kopi Specialty & Santap di Taman',
            'description_en' => 'Specialty coffee, slow-bar pour-overs, and garden dining inside Rosali Hotel Situbondo. Open late, every day.',
            'description_id' => 'Kopi specialty, slow bar, dan tempat makan di taman dalam Rosali Hotel Situbondo. Buka sampai malam setiap hari.',
            'path'           => 'cafe.php',
            'og_slot'        => 'cafe_hero___rosa_de_5_interior___barista___specialty_coffee_setup',
        ],
        'gallery' => [
            'title_en'       => 'Gallery — Rosali Hotel Situbondo',
            'title_id'       => 'Galeri — Rosali Hotel Situbondo',
            'description_en' => 'Photos of Rosali Hotel: rooms, gardens, Rosa De 5 Café, and events in Situbondo, East Java.',
            'description_id' => 'Foto-foto Rosali Hotel: kamar, taman, Rosa De 5 Café, dan acara di Situbondo, Jawa Timur.',
            'path'           => 'gallery.php',
            'og_slot'        => null,
        ],
        'tourism' => [
            'title_en'       => 'Things to Do in Situbondo — Beaches, Ijen Crater & Baluran',
            'title_id'       => 'Wisata di Situbondo — Pantai, Kawah Ijen & Baluran',
            'description_en' => 'Pasir Putih Beach, Ijen Crater, Baluran National Park, and more — explore Situbondo, East Java, from Rosali Hotel.',
            'description_id' => 'Pantai Pasir Putih, Kawah Ijen, Taman Nasional Baluran, dan banyak lagi — jelajahi Situbondo dari Rosali Hotel.',
            'path'           => 'tourism.php',
            'og_slot'        => null,
        ],
        'contact' => [
            'title_en'       => 'Contact Rosali Hotel — Situbondo Reservations & Map',
            'title_id'       => 'Kontak Rosali Hotel — Reservasi & Peta Situbondo',
            'description_en' => 'Reservations, WhatsApp, phone, email, and directions to Rosali Hotel — Jl. PB Sudirman 52, Situbondo, East Java.',
            'description_id' => 'Reservasi, WhatsApp, telepon, email, dan petunjuk arah ke Rosali Hotel — Jl. PB Sudirman 52, Situbondo, Jawa Timur.',
            'path'           => 'contact.php',
            'og_slot'        => 'hotel___front_exterior___signage_at_road',
        ],
    ];
}

function seoSiteBase(): string {
    /* Prefer the admin-provided canonical site URL, then derive from current request. */
    $explicit = getSetting('seo_site_url', '');
    if ($explicit !== '') return rtrim($explicit, '/');

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'rosalihotel.id';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir    = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return $scheme . '://' . $host . $dir;
}

function seoFor(string $pageId, ?string $lang = null): array {
    $defs = seoPageDefaults();
    if (!isset($defs[$pageId])) $pageId = 'home';
    $def = $defs[$pageId];
    $lang = $lang ?: getActiveLang();
    $langKey = ($lang === 'en') ? 'en' : 'id';

    $title       = getSetting("seo_{$pageId}_title_{$langKey}", $def["title_{$langKey}"]);
    $description = getSetting("seo_{$pageId}_description_{$langKey}", $def["description_{$langKey}"]);
    $keywords    = getSetting("seo_{$pageId}_keywords", '');
    $noindex     = getSetting("seo_{$pageId}_noindex", '0') === '1';
    $ogImageUrl  = getSetting("seo_{$pageId}_og_image", '');

    if ($ogImageUrl === '' && !empty($def['og_slot'])) {
        $slotUrl = mediaForSlot($def['og_slot']);
        if ($slotUrl && isset($slotUrl['url'])) $ogImageUrl = $slotUrl['url'];
    }

    $base = seoSiteBase();
    $canonical = $base . '/' . $def['path'];
    if ($ogImageUrl !== '' && !preg_match('#^https?://#', $ogImageUrl)) {
        $ogImageUrl = $base . '/' . ltrim($ogImageUrl, '/');
    }

    return [
        'page_id'     => $pageId,
        'lang'        => $lang,
        'title'       => $title,
        'description' => $description,
        'keywords'    => $keywords,
        'noindex'     => $noindex,
        'canonical'   => $canonical,
        'og_image'    => $ogImageUrl,
        'site_base'   => $base,
    ];
}

/* Site-wide noindex flag — when ON, every page on this install forces a
   noindex/nofollow meta regardless of per-page setting. Intended for staging
   environments like testing.rosalihotel.id. */
function seoNoindexSite(): bool {
    return getSetting('seo_noindex_site', '0') === '1';
}

function seoMeta(string $pageId): void {
    $s = seoFor($pageId);
    $hotelName = getSetting('rc_hotel_name', 'Rosali Hotel');
    $h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $noindex = $s['noindex'] || seoNoindexSite();

    echo "<title>" . $h($s['title']) . "</title>\n";
    echo '<meta name="description" content="' . $h($s['description']) . '"/>' . "\n";
    if ($s['keywords'] !== '') {
        echo '<meta name="keywords" content="' . $h($s['keywords']) . '"/>' . "\n";
    }
    if ($noindex) {
        echo '<meta name="robots" content="noindex, nofollow"/>' . "\n";
    } else {
        echo '<meta name="robots" content="index, follow"/>' . "\n";
    }
    echo '<link rel="canonical" href="' . $h($s['canonical']) . '"/>' . "\n";

    // Open Graph
    echo '<meta property="og:type" content="website"/>' . "\n";
    echo '<meta property="og:site_name" content="' . $h($hotelName) . '"/>' . "\n";
    echo '<meta property="og:title" content="' . $h($s['title']) . '"/>' . "\n";
    echo '<meta property="og:description" content="' . $h($s['description']) . '"/>' . "\n";
    echo '<meta property="og:url" content="' . $h($s['canonical']) . '"/>' . "\n";
    echo '<meta property="og:locale" content="' . ($s['lang'] === 'en' ? 'en_US' : 'id_ID') . '"/>' . "\n";
    if ($s['og_image'] !== '') {
        echo '<meta property="og:image" content="' . $h($s['og_image']) . '"/>' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image"/>' . "\n";
    echo '<meta name="twitter:title" content="' . $h($s['title']) . '"/>' . "\n";
    echo '<meta name="twitter:description" content="' . $h($s['description']) . '"/>' . "\n";
    if ($s['og_image'] !== '') {
        echo '<meta name="twitter:image" content="' . $h($s['og_image']) . '"/>' . "\n";
    }

    // JSON-LD: emit Hotel/LodgingBusiness once (on home); LocalBusiness Org link on others.
    if ($pageId === 'home') {
        echo seoJsonLdHotel($s['site_base']);
    }
}

function seoJsonLdHotel(string $base): string {
    $name      = getSetting('rc_hotel_name', 'Rosali Hotel');
    $phone     = getSetting('rc_phone', '');
    $email     = getSetting('rc_email', '');
    $wa        = getSetting('rc_wa_number', '');
    $address   = getSetting('rc_address', 'Jl. PB Sudirman 52, 68312 Situbondo, Jawa Timur');
    $instagram = getSetting('rc_instagram', '');
    $facebook  = getSetting('rc_facebook_url', '');
    $description = getSetting('seo_home_description_id', 'Hotel butik di Situbondo dengan taman tropis, kamar modern, dan café specialty.');

    /* Parse first line as street, second line as locality if multi-line. */
    $addrLines = preg_split('/\r?\n/', trim($address));
    $street    = $addrLines[0] ?? '';
    $locality  = $addrLines[1] ?? 'Situbondo';

    $sameAs = [];
    if ($instagram !== '') $sameAs[] = 'https://instagram.com/' . ltrim($instagram, '@');
    if ($facebook  !== '') $sameAs[] = $facebook;

    $data = [
        '@context'   => 'https://schema.org',
        '@type'      => ['Hotel', 'LodgingBusiness'],
        'name'       => $name,
        'url'        => $base . '/',
        'description'=> $description,
        'telephone'  => $phone,
        'email'      => $email,
        'address'    => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $street,
            'addressLocality' => 'Situbondo',
            'addressRegion'   => 'Jawa Timur',
            'postalCode'      => '68312',
            'addressCountry'  => 'ID',
        ],
        'image'      => $base . '/logo.png',
    ];
    if ($wa !== '')      $data['contactPoint'] = [
        '@type'       => 'ContactPoint',
        'telephone'   => '+' . preg_replace('/[^0-9]/', '', $wa),
        'contactType' => 'reservations',
    ];
    if ($sameAs)         $data['sameAs'] = $sameAs;

    return '<script type="application/ld+json">' . json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) . '</script>' . "\n";
}
