<?php
// Deposit resolution logic, shared by the trial-deadline cron.
// Kept here (not in api/) so CLI scripts never have to include a web endpoint file.
require_once __DIR__ . '/../config/features.php';
require_once __DIR__ . '/db.php';

// Only ever called with the feature enabled; the Stripe client is dependency-free.
function resolve_deposit(int $depositId, bool $refund): void {
    if (!DEPOSIT_FEATURE_ENABLED) return;
    require_once __DIR__ . '/../config/stripe.local.php';
    require_once __DIR__ . '/stripe/client.php';

    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM deposits WHERE id = ?');
    $stmt->execute([$depositId]);
    $deposit = $stmt->fetch();
    if (!$deposit || $deposit['status'] !== 'succeeded') return;

    if ($refund) {
        if (empty($deposit['stripe_payment_intent_id'])) {
            // Paid but we never captured the payment intent id (shouldn't happen
            // with the checkout.session.completed webhook in place) — don't mark
            // refunded without a real Stripe refund.
            return;
        }
        $result = stripe_request('POST', '/refunds', ['payment_intent' => $deposit['stripe_payment_intent_id']]);
        $pdo->prepare("UPDATE deposits SET status = 'refunded', stripe_refund_id = ? WHERE id = ? AND status = 'succeeded'")
            ->execute([$result['id'] ?? null, $depositId]);
    } else {
        $pdo->prepare("UPDATE deposits SET status = 'forfeited' WHERE id = ? AND status = 'succeeded'")
            ->execute([$depositId]);
    }
}
