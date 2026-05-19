<?php
require_once __DIR__ . '/db.php';

function getSetting(string $key, string $fallback = ''): string {
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT `value` FROM `settings` WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return ($row !== false) ? (string)$row['value'] : $fallback;
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

function getActiveTheme(): string {
    return getSetting('active_theme', 'rosa');
}

function getActiveLang(): string {
    return getSetting('active_lang', 'id');
}
