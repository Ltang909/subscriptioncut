<?php
require_once __DIR__ . '/../config/features.php';
if (!DEPOSIT_FEATURE_ENABLED) { http_response_code(404); exit; }

require_once __DIR__ . '/../config/stripe.local.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/stripe/client.php';

$userId = require_login();

$session = stripe_request('POST', '/checkout/sessions', [
    'mode' => 'payment',
    'success_url' => (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'], 2) . '/settings.php?deposit=success',
    'cancel_url' => (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'], 2) . '/settings.php?deposit=cancelled',
    'line_items[0][price_data][currency]' => 'usd',
    'line_items[0][price_data][product_data][name]' => 'Cutline commitment deposit',
    'line_items[0][price_data][unit_amount]' => DEPOSIT_AMOUNT_CENTS,
    'line_items[0][quantity]' => 1,
]);

db()->prepare(
    "INSERT INTO deposits (user_id, stripe_payment_intent_id, amount_cents, status, deadline_at)
     VALUES (?, ?, ?, 'created', datetime('now', '+30 days'))"
)->execute([$userId, $session['payment_intent'] ?? null, DEPOSIT_AMOUNT_CENTS]);

header('Location: ' . $session['url']);
