<?php
/*
 * Visitor analytics tracker.
 *
 * Include-only file. Must not produce any output. Wrapped in try/catch so
 * a failure here never breaks the page being rendered. Logs one row to
 * visitor_logs per request, updates visitor_daily_summary, and lazily
 * resolves the IP's country/city via ip-api.com (cached daily per IP).
 */

(function () {

    // --- Hard early-outs: never track admin pages or non-browser clients ---
    $self = $_SERVER['PHP_SELF'] ?? '';
    if (stripos($self, 'admin') !== false) return;

    $script = $_SERVER['SCRIPT_NAME'] ?? $self;
    if (stripos($script, '/admin/') !== false) return;

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '') return; // no UA → almost certainly not a human browser

    $botPattern = '/bot|crawler|spider|crawling|curl|wget|python|scrapy|httpclient|libwww|java\//i';
    if (preg_match($botPattern, $ua)) return;

    try {
        require_once __DIR__ . '/db.php';
        global $pdo;
        if (!isset($pdo) || !($pdo instanceof PDO)) return;

        $page = basename(parse_url($self, PHP_URL_PATH) ?: $self);
        if ($page === '' || stripos($page, 'admin') !== false) return;
        // Only track real front-end PHP pages.
        if (!preg_match('/\.php$/i', $page)) return;

        // --- Resolve real IP (respect proxy header) ---
        $rawIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($parts[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) $rawIp = $candidate;
        }
        if ($rawIp === '') $rawIp = '0.0.0.0';

        // --- Anonymize IPv4 (keep first 3 octets); for IPv6 keep first 4 hextets ---
        $anonIp = $rawIp;
        if (filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $oct = explode('.', $rawIp);
            if (count($oct) === 4) $anonIp = $oct[0] . '.' . $oct[1] . '.' . $oct[2] . '.xxx';
        } elseif (filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $hx = explode(':', $rawIp);
            $anonIp = implode(':', array_slice($hx, 0, 4)) . ':xxxx';
        }

        // --- Device / browser / OS detection ---
        [$device, $browser, $os] = trackerDetect($ua);

        // --- Referrer (truncated) ---
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referrer !== null) {
            $referrer = substr($referrer, 0, 500);
            if ($referrer === '') $referrer = null;
        }

        // --- Geo lookup (cached daily per IP, never blocks on failure) ---
        [$country, $city] = trackerGeo($pdo, $rawIp);

        // --- Insert visitor log ---
        $stmt = $pdo->prepare(
            'INSERT INTO visitor_logs
             (page, ip_address, country, city, device_type, browser, os, referrer, visit_date, visit_time)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME())'
        );
        $stmt->execute([$page, $anonIp, $country, $city, $device, $browser, $os, $referrer]);

        // --- Update daily summary (total + unique-by-anonymized-IP) ---
        // Unique = first time this anonymized IP visits this page today.
        $uStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM visitor_logs
             WHERE page = ? AND ip_address = ? AND visit_date = CURDATE()'
        );
        $uStmt->execute([$page, $anonIp]);
        $isFirstToday = ((int)$uStmt->fetchColumn() === 1) ? 1 : 0;

        $sumStmt = $pdo->prepare(
            'INSERT INTO visitor_daily_summary (page, visit_date, total_visits, unique_visits)
             VALUES (?, CURDATE(), 1, ?)
             ON DUPLICATE KEY UPDATE
               total_visits  = total_visits + 1,
               unique_visits = unique_visits + VALUES(unique_visits)'
        );
        $sumStmt->execute([$page, $isFirstToday]);

        // --- Opportunistic 2-year retention prune (≈1% of requests) ---
        // Keeps the analytics tables bounded without needing a cron job.
        if (random_int(1, 100) === 1) {
            $pdo->exec("DELETE FROM visitor_logs           WHERE visit_date < DATE_SUB(CURDATE(), INTERVAL 730 DAY)");
            $pdo->exec("DELETE FROM visitor_daily_summary  WHERE visit_date < DATE_SUB(CURDATE(), INTERVAL 730 DAY)");
            $pdo->exec("DELETE FROM visitor_ip_cache       WHERE cached_at  < DATE_SUB(NOW(),    INTERVAL 30  DAY)");
        }

    } catch (Throwable $e) {
        // Silent: tracking must never affect the page.
    }
})();

/* ---- Helpers (top-level so the IIFE can call them) ---- */

function trackerDetect(string $ua): array {
    $device = 'desktop';
    if (preg_match('/iPad|Tablet/i', $ua))                   $device = 'tablet';
    elseif (preg_match('/Mobile|Android|iPhone|iPod/i', $ua)) $device = 'mobile';

    $browser = 'Other';
    if      (preg_match('/Edg\//i', $ua))                     $browser = 'Edge';
    elseif  (preg_match('/OPR\/|Opera/i', $ua))               $browser = 'Opera';
    elseif  (preg_match('/Firefox/i', $ua))                   $browser = 'Firefox';
    elseif  (preg_match('/Chrome/i', $ua))                    $browser = 'Chrome';
    elseif  (preg_match('/Safari/i', $ua))                    $browser = 'Safari';

    $os = 'Other';
    if      (preg_match('/Windows/i', $ua))                   $os = 'Windows';
    elseif  (preg_match('/Android/i', $ua))                   $os = 'Android';
    elseif  (preg_match('/iPhone|iPad|iPod|iOS/i', $ua))      $os = 'iOS';
    elseif  (preg_match('/Mac OS X|Macintosh/i', $ua))        $os = 'Mac';
    elseif  (preg_match('/Linux/i', $ua))                     $os = 'Linux';

    return [$device, $browser, $os];
}

function trackerGeo(PDO $pdo, string $rawIp): array {
    $isLocal = !filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

    // Local/loopback IPs are skipped unless the admin enabled the dev toggle.
    // When the toggle is on, we resolve via ip-api without an IP — the API uses
    // the *caller's* (i.e. our server's) public IP, giving the server's geo.
    if ($isLocal) {
        if (getSetting('geo_local_enabled', '0') !== '1') return [null, null];
    }

    $cacheKey = $isLocal ? '__server__' : $rawIp;

    try {
        $sel = $pdo->prepare(
            'SELECT country, city FROM visitor_ip_cache
             WHERE ip_address = ? AND DATE(cached_at) = CURDATE() LIMIT 1'
        );
        $sel->execute([$cacheKey]);
        $row = $sel->fetch();
        if ($row) return [$row['country'], $row['city']];
    } catch (PDOException) { /* fall through to fresh lookup */ }

    // Fresh API call — short timeout so a slow API can't pause page render meaningfully.
    $country = null; $city = null;
    $ctx = stream_context_create(['http' => ['timeout' => 1, 'method' => 'GET']]);
    $url = $isLocal
        ? 'http://ip-api.com/json/?fields=country,city,status'
        : 'http://ip-api.com/json/' . rawurlencode($rawIp) . '?fields=country,city,status';
    $json = @file_get_contents($url, false, $ctx);
    if ($json !== false) {
        $d = json_decode($json, true);
        if (is_array($d) && ($d['status'] ?? '') === 'success') {
            $country = isset($d['country']) ? substr((string)$d['country'], 0, 100) : null;
            $city    = isset($d['city'])    ? substr((string)$d['city'],    0, 100) : null;
        }
    }

    try {
        $up = $pdo->prepare(
            'INSERT INTO visitor_ip_cache (ip_address, country, city, cached_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
               country = VALUES(country),
               city    = VALUES(city),
               cached_at = NOW()'
        );
        $up->execute([$cacheKey, $country, $city]);
    } catch (PDOException) {}

    return [$country, $city];
}
