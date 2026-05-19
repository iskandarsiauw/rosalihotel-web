<?php
require_once '../includes/auth.php';
requireLogin();

$counts = [
    'rooms'    => 0,
    'messages' => 0,
    'gallery'  => 0,
    'events'   => 0,
];

try {
    $counts['rooms']    = (int)$pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
    $counts['messages'] = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    $counts['gallery']  = (int)$pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn();
    $counts['events']   = (int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
} catch (PDOException) {
    // Tables exist but may be empty — counts stay 0
}

$username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

$cards = [
    ['label' => 'Rooms',           'value' => $counts['rooms'],    'icon' => '🛏',  'color' => 'oklch(62% 0.18 22)'],
    ['label' => 'Unread Messages', 'value' => $counts['messages'], 'icon' => '✉',   'color' => 'oklch(78% 0.15 84)'],
    ['label' => 'Gallery Items',   'value' => $counts['gallery'],  'icon' => '🖼',  'color' => 'oklch(62% 0.16 148)'],
    ['label' => 'Events',          'value' => $counts['events'],   'icon' => '📅',  'color' => 'oklch(60% 0.14 232)'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Dashboard — Rosali Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'DM Sans',sans-serif}
body{background:oklch(14% 0.018 250);color:oklch(92% 0.010 240);min-height:100vh;display:flex}

/* ── Sidebar ── */
.sidebar{width:220px;flex-shrink:0;background:oklch(18% 0.020 250);border-right:1px solid oklch(30% 0.022 250);display:flex;flex-direction:column;min-height:100vh;position:sticky;top:0}
.sidebar-head{padding:20px 18px 16px;border-bottom:1px solid oklch(30% 0.022 250);display:flex;align-items:center;gap:10px}
.logo-mark{width:30px;height:30px;border-radius:6px;background:oklch(42% 0.18 22);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:white;font-family:'Playfair Display',serif;flex-shrink:0}
.logo-name{font-size:13px;font-weight:600;color:oklch(92% 0.010 240)}
.logo-role{font-size:10px;color:oklch(58% 0.015 240)}
nav{flex:1;padding:10px 8px;display:flex;flex-direction:column;gap:1px}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 11px;border-radius:6px;font-size:13px;color:oklch(58% 0.015 240);text-decoration:none;transition:all .15s;background:transparent;border:none;cursor:pointer;width:100%;text-align:left}
.nav-item:hover{background:oklch(22% 0.022 250);color:oklch(92% 0.010 240)}
.nav-item.active{background:oklch(22% 0.022 250);color:oklch(92% 0.010 240);font-weight:500}
.nav-icon{font-size:14px;opacity:.75}
.sidebar-foot{padding:14px 18px;border-top:1px solid oklch(30% 0.022 250);display:flex;flex-direction:column;gap:8px}
.sidebar-foot a{font-size:11px;color:oklch(42% 0.018 250);text-decoration:none;transition:color .15s}
.sidebar-foot a:hover{color:oklch(62% 0.18 22)}

/* ── Main ── */
main{flex:1;padding:36px 40px;overflow-y:auto;max-height:100vh}

.topbar{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:32px}
.page-title{font-family:'Playfair Display',serif;font-size:26px;color:oklch(92% 0.010 240);margin-bottom:4px}
.page-sub{font-size:13px;color:oklch(58% 0.015 240)}
.user-badge{display:flex;align-items:center;gap:8px;background:oklch(18% 0.020 250);border:1px solid oklch(30% 0.022 250);border-radius:20px;padding:6px 14px 6px 8px}
.user-avatar{width:26px;height:26px;border-radius:50%;background:oklch(42% 0.18 22);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white}
.user-name{font-size:12px;color:oklch(72% 0.012 240)}

/* ── Cards ── */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:40px}
.card{background:oklch(18% 0.020 250);border:1px solid oklch(30% 0.022 250);border-radius:10px;padding:22px 24px;display:flex;flex-direction:column;gap:12px}
.card-icon{font-size:22px;line-height:1}
.card-value{font-size:32px;font-weight:600;line-height:1}
.card-label{font-size:12px;color:oklch(58% 0.015 240);letter-spacing:0.04em}

/* ── Notice ── */
.notice{background:oklch(18% 0.020 250);border:1px solid oklch(30% 0.022 250);border-radius:10px;padding:24px 28px}
.notice h3{font-family:'Playfair Display',serif;font-size:16px;color:oklch(92% 0.010 240);margin-bottom:8px}
.notice p{font-size:13px;color:oklch(58% 0.015 240);line-height:1.7}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-head">
    <div class="logo-mark">R</div>
    <div>
      <div class="logo-name">Rosali Hotel</div>
      <div class="logo-role">Admin Panel</div>
    </div>
  </div>
  <nav>
    <a href="dashboard.php" class="nav-item active"><span class="nav-icon">◈</span> Dashboard</a>
    <span class="nav-item" style="opacity:.4;cursor:default"><span class="nav-icon">🛏</span> Rooms</span>
    <span class="nav-item" style="opacity:.4;cursor:default"><span class="nav-icon">🖼</span> Gallery</span>
    <span class="nav-item" style="opacity:.4;cursor:default"><span class="nav-icon">📅</span> Events</span>
    <span class="nav-item" style="opacity:.4;cursor:default"><span class="nav-icon">☕</span> Café Menu</span>
    <span class="nav-item" style="opacity:.4;cursor:default"><span class="nav-icon">✉</span> Messages</span>
  </nav>
  <div class="sidebar-foot">
    <a href="../index.php">← View Website</a>
    <a href="logout.php">Sign Out</a>
  </div>
</aside>

<main>
  <div class="topbar">
    <div>
      <div class="page-title">Dashboard</div>
      <div class="page-sub">Welcome back, <?= $username ?>. Here's your website at a glance.</div>
    </div>
    <div class="user-badge">
      <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
      <span class="user-name"><?= $username ?></span>
    </div>
  </div>

  <div class="cards">
    <?php foreach ($cards as $c): ?>
    <div class="card">
      <div class="card-icon"><?= $c['icon'] ?></div>
      <div class="card-value" style="color:<?= $c['color'] ?>"><?= $c['value'] ?></div>
      <div class="card-label"><?= $c['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="notice">
    <h3>Phase 1 complete</h3>
    <p>Login, session management, and this dashboard are live. Next phases will add CRUD pages for Rooms, Gallery, Events, Café Menu, and Messages.</p>
  </div>
</main>

</body>
</html>
