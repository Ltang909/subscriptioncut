<?php
// Run daily via Hostinger cron, e.g.:
//   php /home/USER/domains/builtbylt.com/public_html/subscription-cut/cron/send-renewal-reminders.php
// Guarded against being hit over HTTP (belt-and-suspenders alongside the
// cron/.htaccess deny rule, in case the host doesn't honor .htaccess).
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/mailer.php';
require_once __DIR__ . '/../lib/subscriptions.php';

$logPath = __DIR__ . '/reminder-log.txt';
function log_line(string $msg): void {
    global $logPath;
    file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

$pdo = db();
$sent = 0;
$errors = 0;

// 1) Send reminders for subscriptions renewing exactly notify_days_before from now.
$stmt = $pdo->query(
    "SELECT s.*, u.email AS user_email, u.notify_days_before
     FROM subscriptions s
     JOIN users u ON u.id = s.user_id
     WHERE s.status = 'active'
       AND date(s.next_renewal_on) = date('now', '+' || u.notify_days_before || ' days')"
);

$dedupeCheck = $pdo->prepare(
    'SELECT 1 FROM reminder_log WHERE subscription_id = ? AND renewal_date_at_send = ?'
);
$insertLog = $pdo->prepare(
    'INSERT INTO reminder_log (subscription_id, renewal_date_at_send) VALUES (?, ?)'
);

foreach ($stmt->fetchAll() as $sub) {
    $dedupeCheck->execute([$sub['id'], $sub['next_renewal_on']]);
    if ($dedupeCheck->fetch()) continue; // already sent for this renewal date

    $name = $sub['custom_name'] ?: ($sub['tier_name'] ?: 'A subscription');
    $price = number_format($sub['price_cents'] / 100, 2);
    $subject = "Renewing soon: {$name} (\${$price})";
    $body = "{$name} renews on {$sub['next_renewal_on']} — \${$price} / {$sub['cadence']}.\n\n"
          . "Decide now whether to keep it or cancel: sign in to Cutline to review it.\n";

    try {
        $ok = send_mail($sub['user_email'], $subject, $body);
        if ($ok) {
            $insertLog->execute([$sub['id'], $sub['next_renewal_on']]);
            $sent++;
        } else {
            $errors++;
            log_line("mail() returned false for subscription {$sub['id']} ({$sub['user_email']})");
        }
    } catch (Throwable $e) {
        $errors++;
        log_line("Error sending for subscription {$sub['id']}: " . $e->getMessage());
    }
}

// 2) Roll next_renewal_on forward for anything whose renewal date has passed,
//    so the field always points to the next upcoming charge.
$past = $pdo->query(
    "SELECT id, next_renewal_on, cadence FROM subscriptions WHERE status = 'active' AND date(next_renewal_on) <= date('now')"
)->fetchAll();

$advance = $pdo->prepare('UPDATE subscriptions SET next_renewal_on = ?, updated_at = datetime(\'now\') WHERE id = ?');
foreach ($past as $row) {
    $advance->execute([advance_renewal($row['next_renewal_on'], $row['cadence']), $row['id']]);
}

log_line("Run complete: sent=$sent errors=$errors advanced=" . count($past));
echo "sent=$sent errors=$errors advanced=" . count($past) . "\n";
