<?php
require_once __DIR__ . '/../config/features.php';
if (!DEPOSIT_FEATURE_ENABLED) { http_response_code(404); exit; }

require_once __DIR__ . '/../config/stripe.local.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/stripe/client.php';

$userId = require_login();

// One live deposit per user — don't let them stack up multiples.
$stmt = db()->prepare(
    "SELECT id FROM deposits WHERE user_id = ? AND status IN ('created', 'succeeded')"
);
$stmt->execute([$userId]);
if ($stmt->fetch()) {
    header('Location: ../settings.php?deposit=already_active');
    exit;
}

$session = stripe_request('POST', '/checkout/sessions', [
    'mode' => 'payment',
    'success_url' => (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'], 2) . '/settings.php?deposit=success',
    'cancel_url' => (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'], 2) . '/settings.php?deposit=cancelled',
    'line_items[0][price_data][currency]' => 'usd',
    'line_items[0][price_data][product_data][name]' => 'Cutline commitment deposit',
    'line_items[0][price_data][unit_amount]' => DEPOSIT_AMOUNT_CENTS,
    'line_items[0][quantity]' => 1,
]);

if (empty($session['id']) || empty($session['url'])) {
    http_response_code(502);
    exit('Could not start checkout — please try again.');
}

// NOTE: payment_intent is NOT populated on a Checkout Session until the
// customer actually pays. We store the session id here and capture the
// payment intent id later, from the checkout.session.completed webhook.
db()->prepare(
    "INSERT INTO deposits (user_id, stripe_session_id, amount_cents, status, deadline_at)
     VALUES (?, ?, ?, 'created', datetime('now', '+30 days'))"
)->execute([$userId, $session['id'], DEPOSIT_AMOUNT_CENTS]);

header('Location: ' . $session['url']);
