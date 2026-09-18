<?php
require_once __DIR__ . '/lib/auth.php';
if (current_user_id() !== null) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Sign in — Cutline</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/styles.css" />
</head>
<body>
<div class="grain"></div>
<header class="site-head">
  <div class="wrap site-head-row">
    <a class="wordmark" href="index.php" style="text-decoration:none;color:inherit;">Cutline<span class="wordmark-dot">.</span></a>
  </div>
</header>
<main>
  <section class="hero">
    <div class="wrap">
      <p class="hero-kicker">Sign in</p>
      <div class="letter">
        <p class="letter-line" style="font-size:20px;">Enter your email and we'll send you a one-click sign-in link — no password to remember.</p>
        <form id="login-form" class="notify-form" style="margin-top:18px;">
          <input id="login-email" type="email" placeholder="you@example.com" required style="flex:1;min-width:220px;font-family:var(--font-body);font-size:14.5px;padding:12px;border:1px solid var(--line);background:var(--paper);color:var(--ink);" />
          <button type="submit" class="letter-submit" style="margin-top:0;">Send link &rarr;</button>
        </form>
        <p id="login-status" class="notify-status" hidden></p>
      </div>
    </div>
  </section>
</main>
<footer class="site-foot"><div class="wrap"><p>Cutline tracks your subscriptions and nudges you before they renew.</p></div></footer>
<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('login-email').value.trim();
  const status = document.getElementById('login-status');
  status.hidden = false;
  status.textContent = 'Sending...';
  try {
    const res = await fetch('api/request-link.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email }),
    });
    const data = await res.json();
    status.textContent = data.ok ? 'Check your email for a sign-in link.' : 'Something went wrong — try again.';
  } catch {
    status.textContent = 'Something went wrong — try again.';
  }
});
</script>
</body>
</html>
