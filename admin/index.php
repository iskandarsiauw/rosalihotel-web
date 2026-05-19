<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();
if (!empty($_SESSION['admin_id'])) {
    header('Location: app.php');
    exit;
}
$error = isset($_GET['error']) ? (int)$_GET['error'] : 0;
$theme = getActiveTheme();
$lang  = getActiveLang();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Login — Rosali Hotel</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'DM Sans',sans-serif}
body{background:oklch(14% 0.018 250);color:oklch(92% 0.010 240);display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}

.card{width:100%;max-width:400px;background:oklch(18% 0.020 250);border:1px solid oklch(30% 0.022 250);border-radius:12px;padding:40px 36px}

.logo{display:flex;align-items:center;gap:10px;margin-bottom:32px;justify-content:center}
.logo-mark{width:36px;height:36px;border-radius:8px;background:oklch(42% 0.18 22);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:white;font-family:'Playfair Display',serif;flex-shrink:0}
.logo-text{font-family:'Playfair Display',serif;font-size:18px;font-weight:600;color:oklch(92% 0.010 240)}
.logo-sub{font-size:11px;color:oklch(58% 0.015 240);letter-spacing:0.1em;text-transform:uppercase}

h1{font-family:'Playfair Display',serif;font-size:22px;font-weight:600;color:oklch(92% 0.010 240);margin-bottom:6px;text-align:center}
.subtitle{font-size:13px;color:oklch(58% 0.015 240);text-align:center;margin-bottom:28px}

.error-msg{background:oklch(60% 0.20 25 / 0.12);border:1px solid oklch(60% 0.20 25 / 0.4);color:oklch(75% 0.18 25);border-radius:6px;padding:10px 14px;font-size:13px;margin-bottom:20px}

label{display:block;font-size:11px;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;color:oklch(58% 0.015 240);margin-bottom:6px}
input{width:100%;background:oklch(12% 0.015 250);border:1px solid oklch(32% 0.022 250);border-radius:6px;padding:11px 14px;color:oklch(92% 0.010 240);font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
input:focus{border-color:oklch(62% 0.18 22)}

.field{margin-bottom:18px}

button[type=submit]{width:100%;background:oklch(62% 0.18 22);border:none;color:white;border-radius:6px;padding:12px;font-size:14px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;margin-top:8px;transition:opacity .2s}
button[type=submit]:hover{opacity:.85}

.back{display:block;text-align:center;margin-top:20px;font-size:12px;color:oklch(42% 0.018 250);text-decoration:none;transition:color .2s}
.back:hover{color:oklch(62% 0.18 22)}
</style>
</head>
<body class="theme-<?= $theme ?>">
<div class="card">
  <div class="logo">
    <div class="logo-mark">R</div>
    <div>
      <div class="logo-text">Rosali Hotel</div>
      <div class="logo-sub">Admin Panel</div>
    </div>
  </div>

  <h1>Welcome back</h1>
  <p class="subtitle">Sign in to manage your hotel</p>

  <?php if ($error === 1): ?>
  <div class="error-msg">Invalid username or password. Please try again.</div>
  <?php endif; ?>

  <form method="POST" action="login_process.php">
    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autocomplete="username" autofocus/>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password"/>
    </div>
    <button type="submit">Sign In</button>
  </form>

  <a href="../index.php" class="back">← Back to Website</a>
</div>
</body>
</html>
