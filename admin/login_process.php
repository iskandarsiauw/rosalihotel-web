<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once '../includes/db.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: index.php?error=1');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    header('Location: app.php');
    exit;
}

header('Location: index.php?error=1');
exit;
