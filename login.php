<?php
require_once __DIR__ . '/lib/auth.php';
if (current_user_id() !== null) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Sign in · Cutline</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/styles.css" />
</head>
<body>
<header class="site-head">
  <div class="wrap site-head-row">
    <a class="wordmark" href="index.php" style="text-decoration:none;">Cutline<span class="wordmark-dot">.</span></a>
  </div>
</header>
<main>
  <section class="hero" style="padding-top:56px;">
    <div class="wrap-narrow">
      <div class="card">
        <h1 style="font-size:24px;font-weight:800;margin:0 0 8px;">Sign in</h1>
        <p style="color:var(--text-muted);font-size:15px;margin:0 0 22px;">Enter your email and we'll send you a one-click sign-in link. No password to remember.</p>
        <form id="login-form">
          <div class="form-field">
            <label for="login-email">Email</label>
            <input id="login-email" type="email" placeholder="you@example.com" required />
          </div>
          <button type="submit" class="btn btn-primary btn-block">Send link</button>
        </form>
        <p id="login-status" class="status-msg" hidden></p>
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
    status.textContent = data.ok ? 'Check your email for a sign-in link.' : 'Something went wrong, try again.';
  } catch {
    status.textContent = 'Something went wrong, try again.';
  }
});
</script>
</body>
</html>
