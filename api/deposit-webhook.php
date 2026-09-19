<?php
require_once __DIR__ . '/../config/features.php';
if (!DEPOSIT_FEATURE_ENABLED) { http_response_code(404); exit; }

require_once __DIR__ . '/../config/stripe.local.php';
require_once __DIR__ . '/../lib/db.php';

// Verifies the Stripe-Signature header against STRIPE_WEBHOOK_SECRET before
// trusting the payload — see https://stripe.com/docs/webhooks/signatures
function verify_stripe_signature(string $payload, string $sigHeader, string $secret): bool {
    $parts = [];
    foreach (explode(',', $sigHeader) as $pair) {
        [$k, $v] = array_pad(explode('=', $pair, 2), 2, '');
        $parts[$k] = $v;
    }
    if (empty($parts['t']) || empty($parts['v1'])) return false;
    $expected = hash_hmac('sha256', $parts['t'] . '.' . $payload, $secret);
    return hash_equals($expected, $parts['v1']);
}

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!verify_stripe_signature($payload, $sigHeader, STRIPE_WEBHOOK_SECRET)) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($payload, true) ?: [];
$type = $event['type'] ?? '';

if ($type === 'checkout.session.completed') {
    // This is the reliable signal that a deposit was paid: the Checkout
    // Session id is known at creation time (unlike payment_intent), and the
    // event carries the payment intent id we need later for the refund.
    $sessionId = $event['data']['object']['id'] ?? null;
    $intentId = $event['data']['object']['payment_intent'] ?? null;
    if ($sessionId) {
        db()->prepare(
            "UPDATE deposits
             SET status = 'succeeded', stripe_payment_intent_id = COALESCE(?, stripe_payment_intent_id)
             WHERE stripe_session_id = ? AND status = 'created'"
        )->execute([$intentId, $sessionId]);
    }
} elseif ($type === 'payment_intent.succeeded') {
    // Fallback for payment intents created outside Checkout (or if the
    // session id was never stored). Only matches deposits still awaiting
    // payment so a replayed event can't resurrect a refunded deposit.
    $intentId = $event['data']['object']['id'] ?? null;
    if ($intentId) {
        db()->prepare(
            "UPDATE deposits SET status = 'succeeded'
             WHERE stripe_payment_intent_id = ? AND status = 'created'"
        )->execute([$intentId]);
    }
}

http_response_code(200);
echo 'ok';
