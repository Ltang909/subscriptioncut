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
if (($event['type'] ?? '') === 'payment_intent.succeeded') {
    $intentId = $event['data']['object']['id'] ?? null;
    if ($intentId) {
        db()->prepare("UPDATE deposits SET status = 'succeeded' WHERE stripe_payment_intent_id = ?")
            ->execute([$intentId]);
    }
}

http_response_code(200);
echo 'ok';
