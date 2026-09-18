<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/assets.php';
require_once __DIR__ . '/config/features.php';
$userId = require_login();

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Settings · Cutline</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>" />
</head>
<body>
<header class="site-head">
  <div class="wrap site-head-row">
    <a class="wordmark" href="dashboard.php" style="text-decoration:none;">Cutline<span class="wordmark-dot">.</span></a>
    <a class="head-link" href="dashboard.php">Back to dashboard</a>
  </div>
</header>
<main>
  <section class="hero" style="padding-top:40px;">
    <div class="wrap-narrow">
      <div class="card">
        <h1 style="font-size:22px;font-weight:800;margin:0 0 20px;">Settings</h1>
        <form method="post" action="api/save-settings.php">
          <div class="form-field">
            <label>Email</label>
            <input type="text" value="<?= esc($user['email']) ?>" disabled />
          </div>
          <div class="form-field">
            <label for="f-days">Remind me this many days before a renewal</label>
            <input id="f-days" name="notify_days_before" type="number" min="1" max="30" value="<?= (int)$user['notify_days_before'] ?>" />
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
        </form>

        <?php if (DEPOSIT_FEATURE_ENABLED): ?>
        <div style="margin-top:28px;border-top:1px solid var(--border);padding-top:20px;">
          <h2 style="font-size:16px;font-weight:700;margin:0 0 8px;">Commitment deposit</h2>
          <p style="color:var(--text-muted);font-size:14px;margin:0 0 6px;">Put down $25. Cancel all your tracked free trials before their deadlines and get it back. Otherwise it's forfeited.</p>
          <p style="color:var(--text-faint);font-size:13px;margin:0;">Not yet configured.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<footer class="site-foot"><div class="wrap"><p>Cutline emails you before renewals so you can decide to keep or cancel. Nothing here cancels anything for you.</p></div></footer>
</body>
</html>
