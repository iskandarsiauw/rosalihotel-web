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

$type   = $_GET['type'] ?? 'overview';
$period = isset($_GET['period']) ? (int)$_GET['period'] : 7;
if (!in_array($period, [7, 30, 90], true)) $period = 7;

$PAGE_LABELS = [
    'index.php'   => 'Home',
    'rooms.php'   => 'Rooms',
    'gallery.php' => 'Gallery',
    'events.php'  => 'Events',
    'cafe.php'    => 'Café',
    'tourism.php' => 'Tourism',
    'contact.php' => 'Contact',
];

try {
    switch ($type) {
        case 'overview':   echo json_encode(handleOverview($pdo, $PAGE_LABELS));     break;
        case 'pages':      echo json_encode(handlePages($pdo, $period, $PAGE_LABELS)); break;
        case 'countries':  echo json_encode(handleCountries($pdo, $period));         break;
        case 'devices':    echo json_encode(handleDevices($pdo, $period));           break;
        case 'browsers':   echo json_encode(handleBrowsers($pdo, $period));          break;
        case 'daily':      echo json_encode(handleDaily($pdo, max($period, 30)));    break;
        case 'referrers':  echo json_encode(handleReferrers($pdo, $period));         break;
        case 'recent':     echo json_encode(handleRecent($pdo, $PAGE_LABELS));       break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'unknown type']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'query failed']);
}

/* ---------- handlers ---------- */

function handleOverview(PDO $pdo, array $labels): array {
    $today    = (int)scalar($pdo,
        "SELECT COALESCE(SUM(total_visits),0) FROM visitor_daily_summary WHERE visit_date = CURDATE()");
    $yesterday = (int)scalar($pdo,
        "SELECT COALESCE(SUM(total_visits),0) FROM visitor_daily_summary WHERE visit_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
    $week     = (int)scalar($pdo,
        "SELECT COALESCE(SUM(total_visits),0) FROM visitor_daily_summary WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
    $month    = (int)scalar($pdo,
        "SELECT COALESCE(SUM(total_visits),0) FROM visitor_daily_summary WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)");
    $allTime  = (int)scalar($pdo, "SELECT COUNT(*) FROM visitor_logs");

    $topPage = $pdo->query(
        "SELECT page, SUM(total_visits) AS visits
         FROM visitor_daily_summary
         WHERE visit_date = CURDATE()
         GROUP BY page ORDER BY visits DESC LIMIT 1"
    )->fetch();
    $topPageLabel = null;
    if ($topPage) {
        $p = $topPage['page'];
        $topPageLabel = $labels[$p] ?? $p;
    }

    $topCountry = $pdo->query(
        "SELECT country, COUNT(*) AS visits
         FROM visitor_logs
         WHERE visit_date = CURDATE() AND country IS NOT NULL AND country <> ''
         GROUP BY country ORDER BY visits DESC LIMIT 1"
    )->fetch();

    return [
        'today'             => $today,
        'yesterday'         => $yesterday,
        'week'              => $week,
        'month'             => $month,
        'all_time'          => $allTime,
        'top_page_today'    => $topPageLabel,
        'top_country_today' => $topCountry ? $topCountry['country'] : null,
    ];
}

function handlePages(PDO $pdo, int $period, array $labels): array {
    $stmt = $pdo->prepare(
        "SELECT page,
                SUM(total_visits)  AS visits,
                SUM(unique_visits) AS uniq
         FROM visitor_daily_summary
         WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY page ORDER BY visits DESC"
    );
    $stmt->execute([$period - 1]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[] = [
            'page'   => $r['page'],
            'label'  => $labels[$r['page']] ?? $r['page'],
            'visits' => (int)$r['visits'],
            'unique' => (int)$r['uniq'],
        ];
    }
    return $out;
}

function handleCountries(PDO $pdo, int $period): array {
    $stmt = $pdo->prepare(
        "SELECT country, COUNT(*) AS visits
         FROM visitor_logs
         WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
           AND country IS NOT NULL AND country <> ''
         GROUP BY country ORDER BY visits DESC LIMIT 10"
    );
    $stmt->execute([$period - 1]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[] = ['country' => $r['country'], 'visits' => (int)$r['visits']];
    }
    return $out;
}

function handleDevices(PDO $pdo, int $period): array {
    $stmt = $pdo->prepare(
        "SELECT device_type, COUNT(*) AS c
         FROM visitor_logs
         WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
           AND device_type IS NOT NULL
         GROUP BY device_type"
    );
    $stmt->execute([$period - 1]);
    $out = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0];
    foreach ($stmt->fetchAll() as $r) {
        $d = $r['device_type'];
        if (isset($out[$d])) $out[$d] = (int)$r['c'];
    }
    return $out;
}

function handleBrowsers(PDO $pdo, int $period): array {
    $stmt = $pdo->prepare(
        "SELECT browser, COUNT(*) AS c
         FROM visitor_logs
         WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
           AND browser IS NOT NULL
         GROUP BY browser"
    );
    $stmt->execute([$period - 1]);
    $out = ['Chrome' => 0, 'Safari' => 0, 'Firefox' => 0, 'Edge' => 0, 'Opera' => 0, 'Other' => 0];
    foreach ($stmt->fetchAll() as $r) {
        $b = $r['browser'];
        if (!isset($out[$b])) $b = 'Other';
        $out[$b] += (int)$r['c'];
    }
    return $out;
}

function handleDaily(PDO $pdo, int $period): array {
    // Build a zero-filled list of dates so the chart has no gaps.
    $rows = [];
    $stmt = $pdo->prepare(
        "SELECT visit_date, SUM(total_visits) AS visits
         FROM visitor_daily_summary
         WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY visit_date"
    );
    $stmt->execute([$period - 1]);
    $byDate = [];
    foreach ($stmt->fetchAll() as $r) $byDate[$r['visit_date']] = (int)$r['visits'];

    $out = [];
    for ($i = $period - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i day"));
        $out[] = ['date' => $d, 'visits' => $byDate[$d] ?? 0];
    }
    return $out;
}

function handleReferrers(PDO $pdo, int $period): array {
    // Normalize referrers to their host so they group sensibly.
    $stmt = $pdo->prepare(
        "SELECT referrer FROM visitor_logs
         WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)"
    );
    $stmt->execute([$period - 1]);
    $counts = [];
    foreach ($stmt->fetchAll() as $r) {
        $ref = $r['referrer'];
        if (!$ref) { $key = 'Direct'; }
        else {
            $host = parse_url($ref, PHP_URL_HOST);
            $key = $host ? preg_replace('/^www\./i', '', $host) : 'Direct';
        }
        if (!isset($counts[$key])) $counts[$key] = 0;
        $counts[$key]++;
    }
    arsort($counts);
    $out = [];
    foreach (array_slice($counts, 0, 10, true) as $k => $v) {
        $out[] = ['referrer' => $k, 'visits' => $v];
    }
    return $out;
}

function handleRecent(PDO $pdo, array $labels): array {
    $stmt = $pdo->query(
        "SELECT page, ip_address, country, city, device_type, browser, os, referrer,
                visit_date, visit_time
         FROM visitor_logs
         ORDER BY id DESC LIMIT 30"
    );
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $ref = $r['referrer'];
        if ($ref) {
            $h = parse_url($ref, PHP_URL_HOST);
            if ($h) $ref = preg_replace('/^www\./i', '', $h);
        }
        $out[] = [
            'page'    => $labels[$r['page']] ?? $r['page'],
            'ip'      => $r['ip_address'],
            'country' => $r['country'],
            'city'    => $r['city'],
            'device'  => $r['device_type'],
            'browser' => $r['browser'],
            'os'      => $r['os'],
            'referrer'=> $ref,
            'when'    => $r['visit_date'] . ' ' . $r['visit_time'],
        ];
    }
    return $out;
}

function scalar(PDO $pdo, string $sql) {
    $stmt = $pdo->query($sql);
    return $stmt ? $stmt->fetchColumn() : 0;
}
