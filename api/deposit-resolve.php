<?php
require_once __DIR__ . '/../config/features.php';
if (!DEPOSIT_FEATURE_ENABLED) { http_response_code(404); exit; }

require_once __DIR__ . '/../config/stripe.local.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/stripe/client.php';

// Called by cron/check-trial-deadlines.php — not exposed as a public HTTP
// endpoint action a browser would hit directly. Refunds a succeeded deposit.
function resolve_deposit(int $depositId, bool $refund): void {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM deposits WHERE id = ?');
    $stmt->execute([$depositId]);
    $deposit = $stmt->fetch();
    if (!$deposit || $deposit['status'] !== 'succeeded') return;

    if ($refund) {
        $result = stripe_request('POST', '/refunds', ['payment_intent' => $deposit['stripe_payment_intent_id']]);
        $pdo->prepare("UPDATE deposits SET status = 'refunded', stripe_refund_id = ? WHERE id = ?")
            ->execute([$result['id'] ?? null, $depositId]);
    } else {
        $pdo->prepare("UPDATE deposits SET status = 'forfeited' WHERE id = ?")->execute([$depositId]);
    }
}
