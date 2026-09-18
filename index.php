<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/assets.php';
if (current_user_id() !== null) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Cutline: know what you're actually paying for</title>
<meta name="description" content="Track every subscription, see what you spend by category, and get an email before anything renews, so you decide, not the calendar." />

<meta property="og:title" content="Cutline: know what you're actually paying for" />
<meta property="og:description" content="Track your subscriptions, see spend by category, and get renewal reminders before you're charged again." />
<meta property="og:type" content="website" />

<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✂️</text></svg>" />

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>" />
</head>
<body>

<header class="site-head">
  <div class="wrap site-head-row">
    <div class="wordmark">Cutline<span class="wordmark-dot">.</span></div>
    <div class="nav-links">
      <a class="head-link" href="login.php">Sign in</a>
      <a class="btn btn-primary btn-sm" href="login.php">Get started</a>
    </div>
  </div>
</header>

<main>

  <section class="hero">
    <div class="wrap" style="text-align:center;">
      <span class="eyebrow">Subscription tracking, without the spreadsheet</span>
      <h1 class="hero-title" style="max-width:16ch;margin-left:auto;margin-right:auto;">Know what you're <span class="accent">actually</span> paying for</h1>
      <p class="hero-sub" style="margin-left:auto;margin-right:auto;">Check off what you're subscribed to, see the total by category, and get an email before anything renews. You decide, not the calendar.</p>
      <div class="hero-actions" style="justify-content:center;">
        <a href="login.php" class="btn btn-primary">Get started, it's free</a>
      </div>
    </div>
  </section>

  <section class="steps">
    <div class="wrap">
      <h2 class="section-title">How it works</h2>
      <div class="step-grid">
        <div class="card step-card">
          <div class="step-num">1</div>
          <h3 class="step-title">Check off what you have</h3>
          <p class="step-text">Pick from a catalog of popular streaming, music, software, fitness, and other subscriptions, or add anything custom.</p>
        </div>
        <div class="card step-card">
          <div class="step-num">2</div>
          <h3 class="step-title">See it add up</h3>
          <p class="step-text">A running total by month and year, broken down by category, so you know exactly where the money goes.</p>
        </div>
        <div class="card step-card">
          <div class="step-num">3</div>
          <h3 class="step-title">Get a heads-up first</h3>
          <p class="step-text">A few days before anything renews, we email you, so cancelling or keeping it is a real decision.</p>
        </div>
      </div>
    </div>
  </section>

</main>

<footer class="site-foot">
  <div class="wrap">
    <p>Cutline tracks subscriptions and reminds you before renewals. It doesn't cancel anything on your behalf; you're always the one who decides.</p>
  </div>
</footer>

</body>
</html>
