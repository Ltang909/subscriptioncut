<?php
require_once __DIR__ . '/../config/features.php';
if (!DEPOSIT_FEATURE_ENABLED) exit; // dormant unless the feature is turned on
if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only'); }

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/deposits.php';

$pdo = db();

// A deposit's window has closed when its deadline has passed. Refund if the
// user has no remaining active free trials past their trial_ends_on date
// (i.e. they cancelled everything in time); forfeit otherwise.
$due = $pdo->query(
    "SELECT * FROM deposits WHERE status = 'succeeded' AND datetime(deadline_at) <= datetime('now')"
)->fetchAll();

foreach ($due as $deposit) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS c FROM subscriptions
         WHERE user_id = ? AND status = 'active' AND is_free_trial = 1 AND date(trial_ends_on) <= date('now')"
    );
    $stmt->execute([$deposit['user_id']]);
    $stillActiveTrialsPastDeadline = (int)$stmt->fetch()['c'];

    resolve_deposit((int)$deposit['id'], $stillActiveTrialsPastDeadline === 0);
}
