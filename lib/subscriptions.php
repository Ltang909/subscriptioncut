<?php
require_once __DIR__ . '/db.php';

function cadence_interval(string $cadence): DateInterval {
    switch ($cadence) {
        case 'weekly': return new DateInterval('P1W');
        case 'annual': return new DateInterval('P1Y');
        case 'monthly':
        default: return new DateInterval('P1M');
    }
}

// First occurrence of the cadence, starting from $startedOn, that falls on
// or after "today" (or $now, for testability).
function compute_next_renewal(string $startedOn, string $cadence, ?DateTime $now = null): string {
    $now = $now ?? new DateTime('today');
    $next = new DateTime($startedOn);
    $interval = cadence_interval($cadence);
    while ($next < $now) {
        $next->add($interval);
    }
    return $next->format('Y-m-d');
}

function advance_renewal(string $currentRenewal, string $cadence): string {
    $date = new DateTime($currentRenewal);
    $date->add(cadence_interval($cadence));
    return $date->format('Y-m-d');
}

function monthly_equivalent_cents(int $priceCents, string $cadence): float {
    switch ($cadence) {
        case 'weekly': return $priceCents * 4.345;
        case 'annual': return $priceCents / 12;
        case 'monthly':
        default: return $priceCents;
    }
}

function get_user_subscriptions(int $userId, string $status = 'active'): array {
    $stmt = db()->prepare(
        'SELECT * FROM subscriptions WHERE user_id = ? AND status = ? ORDER BY next_renewal_on ASC'
    );
    $stmt->execute([$userId, $status]);
    return $stmt->fetchAll();
}

// Returns [['category' => ..., 'monthly_cents' => float], ...] sorted by spend desc.
function get_category_spend(int $userId): array {
    $subs = get_user_subscriptions($userId, 'active');
    $totals = [];
    foreach ($subs as $sub) {
        $monthly = monthly_equivalent_cents((int)$sub['price_cents'], $sub['cadence']);
        $cat = $sub['category'];
        $totals[$cat] = ($totals[$cat] ?? 0) + $monthly;
    }
    $result = [];
    foreach ($totals as $cat => $cents) {
        $result[] = ['category' => $cat, 'monthly_cents' => $cents];
    }
    usort($result, fn($a, $b) => $b['monthly_cents'] <=> $a['monthly_cents']);
    return $result;
}

function get_total_monthly_cents(int $userId): float {
    $total = 0;
    foreach (get_category_spend($userId) as $row) $total += $row['monthly_cents'];
    return $total;
}

function create_subscription(int $userId, array $data): int {
    $nextRenewal = compute_next_renewal($data['started_on'], $data['cadence']);
    $stmt = db()->prepare(
        'INSERT INTO subscriptions
            (user_id, catalog_service_id, custom_name, category, tier_name, price_cents,
             currency, cadence, started_on, next_renewal_on, is_free_trial, trial_ends_on)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $data['catalog_service_id'] ?? null,
        $data['custom_name'] ?? null,
        $data['category'],
        $data['tier_name'] ?? null,
        $data['price_cents'],
        $data['currency'] ?? 'USD',
        $data['cadence'],
        $data['started_on'],
        $nextRenewal,
        !empty($data['is_free_trial']) ? 1 : 0,
        $data['trial_ends_on'] ?? null,
    ]);
    return (int)db()->lastInsertId();
}

function update_subscription(int $userId, int $id, array $data): void {
    $nextRenewal = compute_next_renewal($data['started_on'], $data['cadence']);
    $stmt = db()->prepare(
        'UPDATE subscriptions SET
            custom_name = ?, category = ?, tier_name = ?, price_cents = ?, cadence = ?,
            started_on = ?, next_renewal_on = ?, is_free_trial = ?, trial_ends_on = ?,
            updated_at = datetime(\'now\')
         WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([
        $data['custom_name'] ?? null,
        $data['category'],
        $data['tier_name'] ?? null,
        $data['price_cents'],
        $data['cadence'],
        $data['started_on'],
        $nextRenewal,
        !empty($data['is_free_trial']) ? 1 : 0,
        $data['trial_ends_on'] ?? null,
        $id,
        $userId,
    ]);
}

function cancel_subscription(int $userId, int $id): void {
    db()->prepare("UPDATE subscriptions SET status = 'cancelled', updated_at = datetime('now') WHERE id = ? AND user_id = ?")
        ->execute([$id, $userId]);
}

function get_subscription(int $userId, int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM subscriptions WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_catalog_services(): array {
    return db()->query('SELECT * FROM catalog_services WHERE is_active = 1 ORDER BY category, name')->fetchAll();
}
