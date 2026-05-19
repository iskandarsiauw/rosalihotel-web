<?php
require_once __DIR__ . '/db.php';

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_id'])) {
        $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        header('Location: ' . $dir . '/index.php');
        exit;
    }
}

function isLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['admin_id']);
}
