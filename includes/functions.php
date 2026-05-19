<?php
require_once __DIR__ . '/db.php';

/* ─── Settings helpers ─── */

function getSetting(string $key, ?string $fallback = null): string {
    $fallback ??= '';
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT `value` FROM `settings` WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return ($row !== false && $row['value'] !== null) ? (string)$row['value'] : $fallback;
    } catch (PDOException) {
        return $fallback;
    }
}

function setSetting(string $key, string $value): void {
    global $pdo;
    $stmt = $pdo->prepare(
        'INSERT INTO `settings` (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );
    $stmt->execute([$key, $value]);
}

function getSettingJson(string $key, $fallback = []) {
    $raw = getSetting($key, '');
    if ($raw === '') return $fallback;
    $d = json_decode($raw, true);
    return is_array($d) ? $d : $fallback;
}

function getActiveTheme(): string {
    $t = getSetting('active_theme', 'rosa');
    $valid = ['garden','boutique','javanese','rosa','coastal','batik'];
    return in_array($t, $valid, true) ? $t : 'rosa';
}

function getActiveLang(): string {
    $l = getSetting('active_lang', 'id');
    return in_array($l, ['en','id'], true) ? $l : 'id';
}

function isSplatEnabled(): bool {
    return getSetting('splat_enabled', '0') === '1';
}

/* ─── CSRF ─── */

function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfVerify(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!$token || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

/* Reject the request with 403 JSON if CSRF token is missing/invalid.
   For GET requests, CSRF is skipped (idempotent reads). */
function requireCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') return;
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
    if (!$token) {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $token = $body['csrf_token'] ?? null;
    }
    if (!csrfVerify($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'CSRF token invalid']);
        exit;
    }
}

/* ─── Media helpers ─── */

/* Map admin "slot key" (e.g. hero___garden_entrance) to the assigned_to we use
   in the media table. Slots are pre-defined buckets used by RosaliImg. */
function mediaForSlot(string $slotKey): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "SELECT id, filename, file_type, mime_type FROM media
             WHERE assigned_to = ? AND is_published = 1
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['slot:' . $slotKey]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['url'] = mediaUrl($row['file_type'], $row['filename']);
        return $row;
    } catch (PDOException) { return null; }
}

function mediaUrl(string $fileType, string $filename): string {
    $dir = match($fileType) {
        'image' => 'uploads/media/images/',
        'video' => 'uploads/media/videos/',
        'splat' => 'uploads/splats/',
        default => 'uploads/media/',
    };
    return $dir . $filename;
}

function mediaByCategory(string $category, ?string $fileType = null): array {
    $fileType ??= 'image';
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "SELECT id, filename, original_name, file_type, category, assigned_to
             FROM media WHERE category = ? AND file_type = ? AND is_published = 1
             ORDER BY id DESC"
        );
        $stmt->execute([$category, $fileType]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) $r['url'] = mediaUrl($r['file_type'], $r['filename']);
        return $rows;
    } catch (PDOException) { return []; }
}

/* All slot assignments — used to populate window.ROSALI.images on every page. */
function mediaSlotMap(): array {
    global $pdo;
    $out = [];
    try {
        $stmt = $pdo->query(
            "SELECT filename, file_type, assigned_to FROM media
             WHERE assigned_to LIKE 'slot:%' AND is_published = 1
             ORDER BY id DESC"
        );
        foreach ($stmt->fetchAll() as $r) {
            $slot = substr($r['assigned_to'], 5); // strip 'slot:'
            if (!isset($out[$slot])) {
                $out[$slot] = mediaUrl($r['file_type'], $r['filename']);
            }
        }
    } catch (PDOException) {}
    return $out;
}

/* ─── Layout / content / colors for window.ROSALI ─── */

function allContentOverrides(): array {
    global $pdo;
    $out = [];
    try {
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'rc_%'");
        foreach ($stmt->fetchAll() as $r) $out[substr($r['key'], 3)] = $r['value'];
    } catch (PDOException) {}
    return $out;
}

function allLayoutPrefs(): array {
    global $pdo;
    $out = [];
    try {
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'layout_%'");
        foreach ($stmt->fetchAll() as $r) $out[substr($r['key'], 7)] = $r['value'];
    } catch (PDOException) {}
    return $out;
}

function colorOverridesAll(): array {
    return getSettingJson('rosali_color_overrides', []);
}

function pageVisibility(): array {
    $defaults = ['home'=>true,'rooms'=>true,'events'=>true,'cafe'=>true,'gallery'=>true,'tourism'=>true,'contact'=>true];
    $v = getSettingJson('page_visibility', $defaults);
    foreach ($defaults as $k => $d) if (!isset($v[$k])) $v[$k] = $d;
    return $v;
}

function pageOrder(): array {
    $raw = getSetting('admin_pages', '');
    if ($raw === '') return [];
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}
