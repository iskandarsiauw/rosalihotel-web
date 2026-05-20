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

$PAGE_LABELS = [
    'index.php'   => 'Home',
    'rooms.php'   => 'Rooms',
    'gallery.php' => 'Gallery',
    'events.php'  => 'Events',
    'cafe.php'    => 'Café',
    'tourism.php' => 'Tourism',
    'contact.php' => 'Contact',
];

$type    = $_GET['type'] ?? 'overview';
[$from, $to] = resolveDateRange($_GET);
$limit   = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 30;

try {
    switch ($type) {
        case 'overview':   echo json_encode(handleOverview($pdo, $PAGE_LABELS));     break;
        case 'pages':      echo json_encode(handlePages($pdo, $from, $to, $PAGE_LABELS)); break;
        case 'countries':  echo json_encode(handleCountries($pdo, $from, $to));      break;
        case 'devices':    echo json_encode(handleDevices($pdo, $from, $to));        break;
        case 'browsers':   echo json_encode(handleBrowsers($pdo, $from, $to));       break;
        case 'daily':      echo json_encode(handleDaily($pdo, $from, $to));          break;
        case 'referrers':  echo json_encode(handleReferrers($pdo, $from, $to));      break;
        case 'recent':     echo json_encode(handleRecent($pdo, $from, $to, $limit, $PAGE_LABELS)); break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'unknown type']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'query failed']);
}

/* ---------- date range helpers ---------- */

function isValidDate(string $s): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
    [$y, $m, $d] = array_map('intval', explode('-', $s));
    return checkdate($m, $d, $y);
}

/**
 * Returns [from, to] as YYYY-MM-DD strings.
 *  - If both from & to are valid → use them.
 *  - Else if period in {7,30,90} → today - (period-1) ... today.
 *  - Else default 7 days.
 * Capped to 2 years (730 days) to bound query cost.
 */
function resolveDateRange(array $q): array {
    $from = $q['from'] ?? '';
    $to   = $q['to']   ?? '';

    if (isValidDate($from) && isValidDate($to)) {
        if ($from > $to) [$from, $to] = [$to, $from];
    } else {
        $period = isset($q['period']) ? (int)$q['period'] : 7;
        if (!in_array($period, [7, 30, 90], true)) $period = 7;
        $to   = date('Y-m-d');
        $from = date('Y-m-d', strtotime("-" . ($period - 1) . " days"));
    }

    // Cap to 2-year retention window so we never scan more than that.
    $minFrom = date('Y-m-d', strtotime('-730 days'));
    if ($from < $minFrom) $from = $minFrom;
    $today = date('Y-m-d');
    if ($to > $today) $to = $today;

    return [$from, $to];
}

/* ---------- handlers ---------- */

/** Overview ignores the period selector — it always reports fixed buckets. */
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

function handlePages(PDO $pdo, string $from, string $to, array $labels): array {
    $stmt = $pdo->prepare(
        "SELECT page,
                SUM(total_visits)  AS visits,
                SUM(unique_visits) AS uniq
         FROM visitor_daily_summary
         WHERE visit_date BETWEEN ? AND ?
         GROUP BY page ORDER BY visits DESC"
    );
    $stmt->execute([$from, $to]);
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

function handleCountries(PDO $pdo, string $from, string $to): array {
    $stmt = $pdo->prepare(
        "SELECT country, COUNT(*) AS visits
         FROM visitor_logs
         WHERE visit_date BETWEEN ? AND ?
           AND country IS NOT NULL AND country <> ''
         GROUP BY country ORDER BY visits DESC LIMIT 10"
    );
    $stmt->execute([$from, $to]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[] = ['country' => $r['country'], 'visits' => (int)$r['visits']];
    }
    return $out;
}

function handleDevices(PDO $pdo, string $from, string $to): array {
    $stmt = $pdo->prepare(
        "SELECT device_type, COUNT(*) AS c
         FROM visitor_logs
         WHERE visit_date BETWEEN ? AND ?
           AND device_type IS NOT NULL
         GROUP BY device_type"
    );
    $stmt->execute([$from, $to]);
    $out = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0];
    foreach ($stmt->fetchAll() as $r) {
        $d = $r['device_type'];
        if (isset($out[$d])) $out[$d] = (int)$r['c'];
    }
    return $out;
}

function handleBrowsers(PDO $pdo, string $from, string $to): array {
    $stmt = $pdo->prepare(
        "SELECT browser, COUNT(*) AS c
         FROM visitor_logs
         WHERE visit_date BETWEEN ? AND ?
           AND browser IS NOT NULL
         GROUP BY browser"
    );
    $stmt->execute([$from, $to]);
    $out = ['Chrome' => 0, 'Safari' => 0, 'Firefox' => 0, 'Edge' => 0, 'Opera' => 0, 'Other' => 0];
    foreach ($stmt->fetchAll() as $r) {
        $b = $r['browser'];
        if (!isset($out[$b])) $b = 'Other';
        $out[$b] += (int)$r['c'];
    }
    return $out;
}

function handleDaily(PDO $pdo, string $from, string $to): array {
    $stmt = $pdo->prepare(
        "SELECT visit_date, SUM(total_visits) AS visits
         FROM visitor_daily_summary
         WHERE visit_date BETWEEN ? AND ?
         GROUP BY visit_date"
    );
    $stmt->execute([$from, $to]);
    $byDate = [];
    foreach ($stmt->fetchAll() as $r) $byDate[$r['visit_date']] = (int)$r['visits'];

    // Zero-fill so the chart has no gaps.
    $out = [];
    $cursor = strtotime($from);
    $end    = strtotime($to);
    while ($cursor <= $end) {
        $d = date('Y-m-d', $cursor);
        $out[] = ['date' => $d, 'visits' => $byDate[$d] ?? 0];
        $cursor = strtotime('+1 day', $cursor);
    }
    return $out;
}

function handleReferrers(PDO $pdo, string $from, string $to): array {
    $stmt = $pdo->prepare(
        "SELECT referrer FROM visitor_logs
         WHERE visit_date BETWEEN ? AND ?"
    );
    $stmt->execute([$from, $to]);
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

function handleRecent(PDO $pdo, string $from, string $to, int $limit, array $labels): array {
    $stmt = $pdo->prepare(
        "SELECT page, ip_address, country, city, device_type, browser, os, referrer,
                visit_date, visit_time
         FROM visitor_logs
         WHERE visit_date BETWEEN ? AND ?
         ORDER BY id DESC LIMIT " . (int)$limit
    );
    $stmt->execute([$from, $to]);
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
