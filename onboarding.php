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
<title>Set up your subscriptions — Cutline</title>
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
    <a class="head-link" href="dashboard.php">Skip to dashboard</a>
  </div>
</header>
<main>
  <section class="hero">
    <div class="wrap">
      <p class="hero-kicker">Check off what you're subscribed to</p>
      <p class="hero-sub">Pick a tier, cadence, and when you started — we'll work out when it renews. Anything not listed, you can add manually afterward.</p>
    </div>
  </section>

  <form id="onboarding-form">
    <?php foreach ($byCategory as $cat => $items): ?>
    <section class="playbook" style="padding-top:0;">
      <div class="wrap">
        <h2 class="playbook-title" style="font-size:20px;"><?= esc($categoryLabels[$cat] ?? ucfirst($cat)) ?></h2>
        <div class="moves">
          <?php foreach ($items as $svc): $tiers = json_decode($svc['tiers_json'], true) ?: []; ?>
          <div class="move onb-item" data-service-id="<?= (int)$svc['id'] ?>" data-category="<?= esc($cat) ?>" data-name="<?= esc($svc['name']) ?>">
            <label style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" class="onb-check" />
              <?= esc($svc['name']) ?>
            </label>
            <div class="onb-fields" hidden style="margin-top:10px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
              <select class="onb-tier" style="padding:8px;border:1px solid var(--line);background:var(--paper-2);">
                <?php foreach ($tiers as $t): ?>
                  <option value="<?= (int)$t['price_cents'] ?>" data-name="<?= esc($t['name']) ?>"><?= esc($t['name']) ?> — $<?= number_format($t['price_cents'] / 100, 2) ?></option>
                <?php endforeach; ?>
              </select>
              <select class="onb-cadence" style="padding:8px;border:1px solid var(--line);background:var(--paper-2);">
                <?php foreach (['weekly', 'monthly', 'annual'] as $c): ?>
                  <option value="<?= $c ?>" <?= $c === $svc['typical_cadence'] ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                <?php endforeach; ?>
              </select>
              <label style="font-size:13px;color:var(--ink-soft);">Started
                <input type="date" class="onb-started" value="<?= date('Y-m-d') ?>" style="padding:8px;border:1px solid var(--line);" />
              </label>
              <label style="font-size:13px;color:var(--ink-soft);display:flex;align-items:center;gap:5px;">
                <input type="checkbox" class="onb-trial" /> Free trial
              </label>
              <label class="onb-trial-end-wrap" hidden style="font-size:13px;color:var(--ink-soft);">Ends
                <input type="date" class="onb-trial-end" style="padding:8px;border:1px solid var(--line);" />
              </label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endforeach; ?>

    <section class="notify">
      <div class="wrap">
        <button type="submit" class="letter-submit">Add to my dashboard &rarr;</button>
        <p id="onboarding-status" class="notify-status" hidden></p>
      </div>
    </section>
  </form>
</main>
<footer class="site-foot"><div class="wrap"><p>Don't see something? You can add any subscription manually from the dashboard.</p></div></footer>
<script src="assets/onboarding.js"></script>
</body>
</html>
