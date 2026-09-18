<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/subscriptions.php';
$userId = require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$sub = $id ? get_subscription($userId, $id) : null;
if ($id && !$sub) { header('Location: dashboard.php'); exit; }

$categories = ['streaming', 'music', 'software', 'cloud_storage', 'fitness', 'reading', 'gaming', 'food_delivery', 'other'];
$categoryLabels = [
    'streaming' => 'Streaming', 'music' => 'Music', 'software' => 'Software & SaaS',
    'cloud_storage' => 'Cloud storage', 'fitness' => 'Fitness', 'reading' => 'News & reading',
    'gaming' => 'Gaming', 'food_delivery' => 'Food delivery', 'other' => 'Other',
];

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function val($sub, $key, $default = '') { return $sub ? esc($sub[$key]) : $default; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= $sub ? 'Edit subscription' : 'Add a subscription' ?> — Cutline</title>
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
    <a class="head-link" href="dashboard.php">Back to dashboard</a>
  </div>
</header>
<main>
  <section class="hero">
    <div class="wrap">
      <p class="hero-kicker"><?= $sub ? 'Edit subscription' : 'Add a subscription' ?></p>
      <div class="letter" style="padding:28px;">
        <form method="post" action="api/save-subscription.php">
          <?php if ($sub): ?><input type="hidden" name="id" value="<?= (int)$sub['id'] ?>" /><?php endif; ?>

          <div class="form-field">
            <label for="f-name">Name</label>
            <input id="f-name" name="custom_name" type="text" required value="<?= val($sub, 'custom_name') ?>" placeholder="e.g. Netflix, my gym, etc." />
          </div>

          <div class="form-row">
            <div class="form-field">
              <label for="f-category">Category</label>
              <select id="f-category" name="category">
                <?php foreach ($categories as $c): ?>
                  <option value="<?= $c ?>" <?= ($sub && $sub['category'] === $c) ? 'selected' : '' ?>><?= esc($categoryLabels[$c]) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="f-tier">Tier / plan name (optional)</label>
              <input id="f-tier" name="tier_name" type="text" value="<?= val($sub, 'tier_name') ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-field">
              <label for="f-price">Price ($)</label>
              <input id="f-price" name="price_dollars" type="number" step="0.01" min="0" required value="<?= $sub ? esc(number_format($sub['price_cents'] / 100, 2, '.', '')) : '' ?>" />
            </div>
            <div class="form-field">
              <label for="f-cadence">Billed</label>
              <select id="f-cadence" name="cadence">
                <?php foreach (['weekly', 'monthly', 'annual'] as $c): ?>
                  <option value="<?= $c ?>" <?= ($sub && $sub['cadence'] === $c) || (!$sub && $c === 'monthly') ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-field">
              <label for="f-started">Started on</label>
              <input id="f-started" name="started_on" type="date" required value="<?= val($sub, 'started_on', date('Y-m-d')) ?>" />
            </div>
            <div class="form-field">
              <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="f-trial" name="is_free_trial" value="1" <?= ($sub && $sub['is_free_trial']) ? 'checked' : '' ?> /> This is a free trial</label>
              <input id="f-trial-end" name="trial_ends_on" type="date" value="<?= val($sub, 'trial_ends_on') ?>" style="margin-top:8px;" />
            </div>
          </div>

          <button type="submit" class="letter-submit"><?= $sub ? 'Save changes' : 'Add subscription' ?> &rarr;</button>
        </form>

        <?php if ($sub && $sub['status'] === 'active'): ?>
        <form method="post" action="api/save-subscription.php" style="margin-top:20px;border-top:1px solid var(--line);padding-top:16px;">
          <input type="hidden" name="id" value="<?= (int)$sub['id'] ?>" />
          <input type="hidden" name="action" value="cancel" />
          <button type="submit" class="copy-btn danger-link">Cancel this subscription</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<footer class="site-foot"><div class="wrap"><p>Cancelling here just stops Cutline from tracking it — it doesn't cancel the subscription itself with the company.</p></div></footer>
</body>
</html>
