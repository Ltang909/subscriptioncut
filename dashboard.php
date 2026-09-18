<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/subscriptions.php';
require_once __DIR__ . '/lib/assets.php';
$userId = require_login();

$subs = get_user_subscriptions($userId, 'active');
$categorySpend = get_category_spend($userId);
$totalMonthly = get_total_monthly_cents($userId);

$categoryLabels = [
    'streaming' => 'Streaming', 'music' => 'Music', 'software' => 'Software & SaaS',
    'cloud_storage' => 'Cloud storage', 'fitness' => 'Fitness', 'reading' => 'News & reading',
    'gaming' => 'Gaming', 'food_delivery' => 'Food delivery', 'other' => 'Other',
];
function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function category_label($cat) { global $categoryLabels; return $categoryLabels[$cat] ?? ucfirst($cat); }
function money($cents) { return '$' . number_format($cents / 100, 2); }
function days_until(string $date): int {
    $target = new DateTime($date);
    $now = new DateTime('today');
    return (int)$now->diff($target)->format('%r%a');
}
function renewal_pill(int $d): string {
    if ($d < 0) return '<span class="pill pill-danger">Overdue</span>';
    if ($d === 0) return '<span class="pill pill-danger">Today</span>';
    if ($d <= 3) return '<span class="pill pill-warning">In ' . $d . ' day' . ($d === 1 ? '' : 's') . '</span>';
    return '<span class="pill pill-slate">In ' . $d . ' days</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard · Cutline</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>" />
</head>
<body>
<header class="site-head">
  <div class="wrap site-head-row">
    <a class="wordmark" href="dashboard.php" style="text-decoration:none;">Cutline<span class="wordmark-dot">.</span></a>
    <nav class="nav-links">
      <a class="head-link" href="onboarding.php">Add from catalog</a>
      <a class="head-link" href="subscription-edit.php">Add custom</a>
      <a class="head-link" href="settings.php">Settings</a>
      <a class="head-link" href="logout.php">Sign out</a>
    </nav>
  </div>
</header>
<main>
  <div class="wrap" style="padding-top:32px;">

    <div class="stat-grid">
      <div class="card stat-card">
        <div class="stat-value"><?= money($totalMonthly) ?></div>
        <div class="stat-label">per month</div>
      </div>
      <div class="card stat-card">
        <div class="stat-value"><?= money($totalMonthly * 12) ?></div>
        <div class="stat-label">per year</div>
      </div>
      <div class="card stat-card">
        <div class="stat-value"><?= count($subs) ?></div>
        <div class="stat-label">active subscription<?= count($subs) === 1 ? '' : 's' ?></div>
      </div>
    </div>

    <?php if (!count($categorySpend)): ?>
      <div class="card empty-state" style="margin-bottom:24px;">
        <p style="margin:0 0 16px;">Nothing tracked yet.</p>
        <a href="onboarding.php" class="btn btn-primary btn-sm">Check off what you're subscribed to</a>
      </div>
    <?php else: ?>
      <div class="card" style="margin-bottom:24px;">
        <h2 style="font-size:15px;font-weight:700;margin:0 0 18px;">Spend by category</h2>
        <div id="category-bars"></div>
      </div>
    <?php endif; ?>

    <?php if (count($subs)): ?>
    <h2 style="font-size:15px;font-weight:700;margin:0 0 14px;">Upcoming renewals</h2>
    <div class="renewal-list">
      <?php foreach ($subs as $s): $d = days_until($s['next_renewal_on']);
            $title = $s['custom_name'] ?: ($s['tier_name'] ?: 'Subscription');
            $subtitle = ($s['custom_name'] && $s['tier_name']) ? $s['tier_name'] : null; ?>
      <div class="card-flat renewal-item">
        <div class="renewal-main">
          <div>
            <div class="renewal-name"><?= esc($title) ?><?php if ($subtitle): ?> <span style="font-weight:400;color:var(--text-muted);">(<?= esc($subtitle) ?>)</span><?php endif; ?></div>
            <div class="renewal-meta"><?= esc(category_label($s['category'])) ?> · <?= money($s['price_cents']) ?> / <?= esc($s['cadence']) ?> · renews <?= esc($s['next_renewal_on']) ?></div>
          </div>
          <?= renewal_pill($d) ?>
          <?php if ($s['is_free_trial']): ?><span class="pill pill-accent">Free trial</span><?php endif; ?>
        </div>
        <a href="subscription-edit.php?id=<?= (int)$s['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</main>
<footer class="site-foot"><div class="wrap"><p>We'll email you a few days before anything renews so you can decide to keep it or cut it.</p></div></footer>
<script>window.CATEGORY_SPEND = <?= json_encode($categorySpend) ?>;</script>
<script src="<?= asset_url('assets/dashboard.js') ?>"></script>
</body>
</html>
