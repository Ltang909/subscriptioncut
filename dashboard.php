<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/subscriptions.php';
$userId = require_login();

$subs = get_user_subscriptions($userId, 'active');
$categorySpend = get_category_spend($userId);
$totalMonthly = get_total_monthly_cents($userId);

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function money($cents) { return '$' . number_format($cents / 100, 2); }
function days_until(string $date): int {
    $target = new DateTime($date);
    $now = new DateTime('today');
    return (int)$now->diff($target)->format('%r%a');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard — Cutline</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/styles.css" />
</head>
<body>
<div class="grain"></div>
<header class="site-head">
  <div class="wrap site-head-row">
    <a class="wordmark" href="dashboard.php" style="text-decoration:none;color:inherit;">Cutline<span class="wordmark-dot">.</span></a>
    <nav style="display:flex;gap:16px;">
      <a class="head-link" href="onboarding.php">Add from catalog</a>
      <a class="head-link" href="subscription-edit.php">Add custom</a>
      <a class="head-link" href="settings.php">Settings</a>
      <a class="head-link" href="logout.php">Sign out</a>
    </nav>
  </div>
</header>
<main>
  <section class="hero" style="padding-top:20px;">
    <div class="wrap">
      <p class="hero-kicker">Your subscriptions</p>
      <div class="case-file">
        <div class="case-file-head">
          <div>
            <div class="cf-name"><?= money($totalMonthly) ?> / month</div>
            <div class="cf-category"><?= money($totalMonthly * 12) ?> / year across <?= count($subs) ?> active subscription<?= count($subs) === 1 ? '' : 's' ?></div>
          </div>
        </div>

        <?php if (!count($categorySpend)): ?>
          <p class="case-text">Nothing tracked yet. <a href="onboarding.php">Check off what you're subscribed to</a> to get started.</p>
        <?php else: ?>
          <div class="case-block">
            <div class="case-label">Spend by category</div>
            <div id="category-bars"></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (count($subs)): ?>
  <section class="playbook" style="padding-top:20px;">
    <div class="wrap">
      <h2 class="playbook-title">Upcoming renewals</h2>
      <div class="moves">
        <?php foreach ($subs as $s): $d = days_until($s['next_renewal_on']); ?>
        <div class="move">
          <div class="move-index"><?= $d < 0 ? 'overdue' : ($d === 0 ? 'today' : "in $d day" . ($d === 1 ? '' : 's')) ?><?= $s['is_free_trial'] ? ' · free trial' : '' ?></div>
          <?php $title = $s['custom_name'] ?: ($s['tier_name'] ?: 'Subscription');
                $subtitle = ($s['custom_name'] && $s['tier_name']) ? $s['tier_name'] : null; ?>
          <h3 class="move-title"><?= esc($title) ?><?= $subtitle ? ' <span style="font-weight:400;color:var(--ink-soft);font-size:14px;">(' . esc($subtitle) . ')</span>' : '' ?> <span style="font-weight:400;color:var(--ink-soft);font-size:14px;">— <?= esc($s['category']) ?></span></h3>
          <p class="move-text"><?= money($s['price_cents']) ?> / <?= esc($s['cadence']) ?> · renews <?= esc($s['next_renewal_on']) ?></p>
          <p class="move-text"><a href="subscription-edit.php?id=<?= (int)$s['id'] ?>">Edit or cancel</a></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>
<footer class="site-foot"><div class="wrap"><p>We'll email you a few days before anything renews so you can decide to keep it or cut it.</p></div></footer>
<script>window.CATEGORY_SPEND = <?= json_encode($categorySpend) ?>;</script>
<script src="assets/dashboard.js"></script>
</body>
</html>
