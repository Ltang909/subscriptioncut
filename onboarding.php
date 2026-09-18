<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/subscriptions.php';
require_login();

$services = get_catalog_services();
$byCategory = [];
foreach ($services as $s) $byCategory[$s['category']][] = $s;

$categoryLabels = [
    'streaming' => 'Streaming', 'music' => 'Music', 'software' => 'Software & SaaS',
    'cloud_storage' => 'Cloud storage', 'fitness' => 'Fitness', 'reading' => 'News & reading',
    'gaming' => 'Gaming', 'food_delivery' => 'Food delivery',
];

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Set up your subscriptions · Cutline</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/styles.css" />
</head>
<body>
<header class="site-head">
  <div class="wrap site-head-row">
    <a class="wordmark" href="dashboard.php" style="text-decoration:none;">Cutline<span class="wordmark-dot">.</span></a>
    <a class="head-link" href="dashboard.php">Skip to dashboard</a>
  </div>
</header>
<main>
  <section class="hero" style="padding-bottom:24px;">
    <div class="wrap">
      <h1 class="hero-title" style="font-size:32px;">Check off what you're subscribed to</h1>
      <p class="hero-sub">Pick a tier, cadence, and when you started. We'll work out when it renews. Anything not listed, you can add manually afterward.</p>
    </div>
  </section>

  <form id="onboarding-form">
    <div class="wrap">
      <?php foreach ($byCategory as $cat => $items): ?>
      <div class="category-section">
        <div class="category-heading"><?= esc($categoryLabels[$cat] ?? ucfirst($cat)) ?></div>
        <?php foreach ($items as $svc): $tiers = json_decode($svc['tiers_json'], true) ?: []; ?>
        <div class="onb-item" data-service-id="<?= (int)$svc['id'] ?>" data-category="<?= esc($cat) ?>" data-name="<?= esc($svc['name']) ?>">
          <label class="onb-item-head">
            <input type="checkbox" class="onb-check" />
            <?= esc($svc['name']) ?>
          </label>
          <div class="onb-fields" hidden>
            <select class="onb-tier">
              <?php foreach ($tiers as $t): ?>
                <option value="<?= (int)$t['price_cents'] ?>" data-name="<?= esc($t['name']) ?>"><?= esc($t['name']) ?>, $<?= number_format($t['price_cents'] / 100, 2) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="onb-cadence">
              <?php foreach (['weekly', 'monthly', 'annual'] as $c): ?>
                <option value="<?= $c ?>" <?= $c === $svc['typical_cadence'] ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="date" class="onb-started" value="<?= date('Y-m-d') ?>" />
            <label class="onb-inline-check"><input type="checkbox" class="onb-trial" /> Free trial</label>
            <input type="date" class="onb-trial-end" hidden />
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

      <div style="padding:12px 0 60px;">
        <button type="submit" class="btn btn-primary">Add to my dashboard</button>
        <p id="onboarding-status" class="status-msg" hidden></p>
      </div>
    </div>
  </form>
</main>
<footer class="site-foot"><div class="wrap"><p>Don't see something? You can add any subscription manually from the dashboard.</p></div></footer>
<script src="assets/onboarding.js"></script>
</body>
</html>
