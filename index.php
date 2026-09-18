<?php
require_once __DIR__ . '/lib/auth.php';
if (current_user_id() !== null) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Cutline — know what you're actually paying for</title>
<meta name="description" content="Track every subscription, see what you spend by category, and get an email before anything renews — so you decide, not the calendar." />

<meta property="og:title" content="Cutline — know what you're actually paying for" />
<meta property="og:description" content="Track your subscriptions, see spend by category, and get renewal reminders before you're charged again." />
<meta property="og:type" content="website" />

<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✂️</text></svg>" />

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/styles.css" />
</head>
<body>

<div class="grain"></div>

<header class="site-head">
  <div class="wrap site-head-row">
    <div class="wordmark">Cutline<span class="wordmark-dot">.</span></div>
    <a class="head-link" href="login.php">Sign in</a>
  </div>
</header>

<main>

  <section class="hero">
    <div class="wrap">
      <p class="hero-kicker">Every subscription, in one place.</p>

      <div class="letter">
        <p class="letter-line">Dear future me,</p>
        <p class="letter-line">Here's everything I'm actually paying for, what it costs, and when it renews next.</p>
        <p class="letter-line">Cancel the ones that aren't worth it — before they renew, not after.</p>
        <a href="login.php" class="letter-submit" style="display:inline-block;text-decoration:none;">Get started <span aria-hidden="true">&rarr;</span></a>
      </div>

      <p class="hero-sub">Check off what you've got from a catalog of the usual suspects, or add anything manually. We track cost, category, and renewal date — and email you before you're charged again.</p>
    </div>
  </section>

  <section id="playbook" class="playbook">
    <div class="wrap">
      <h2 class="playbook-title">How it works</h2>

      <div class="moves">
        <div class="move">
          <div class="move-index">First</div>
          <h3 class="move-title">Check off what you're subscribed to</h3>
          <p class="move-text">Pick from a catalog of popular streaming, music, software, fitness, and other subscriptions — pick the tier and billing cadence, or add anything custom.</p>
        </div>
        <div class="move">
          <div class="move-index">Second</div>
          <h3 class="move-title">See what it actually adds up to</h3>
          <p class="move-text">A running total by month and year, broken down by category, so you can see exactly where the money's going instead of guessing.</p>
        </div>
        <div class="move">
          <div class="move-index">Third</div>
          <h3 class="move-title">Get a heads-up before it renews</h3>
          <p class="move-text">A few days before any subscription renews, we email you — so cancelling (or keeping it) is a decision, not something that happens to you by default.</p>
        </div>
      </div>
    </div>
  </section>

</main>

<footer class="site-foot">
  <div class="wrap">
    <p>Cutline tracks subscriptions and reminds you before renewals. It doesn't cancel anything on your behalf — you're always the one who decides.</p>
  </div>
</footer>

</body>
</html>
