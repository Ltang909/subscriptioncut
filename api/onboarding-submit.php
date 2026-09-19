<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/subscriptions.php';

header('Content-Type: application/json');
$userId = require_login();

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$items = $input['items'] ?? [];

if (!is_array($items) || !count($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'no_items']);
    exit;
}

function valid_ymd(?string $d): bool {
    return $d !== null && $d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1
        && checkdate((int)substr($d, 5, 2), (int)substr($d, 8, 2), (int)substr($d, 0, 4));
}

$validCadences = ['weekly', 'monthly', 'annual'];
$created = 0;
$skipped = [];

foreach ($items as $item) {
    $label = trim((string)($item['custom_name'] ?? $item['tier_name'] ?? 'item'));
    $cadence = $item['cadence'] ?? 'monthly';
    if (!in_array($cadence, $validCadences, true)) { $skipped[] = $label; continue; }
    $startedOn = $item['started_on'] ?? null;
    if (!valid_ymd($startedOn)) { $skipped[] = $label; continue; }
    $priceCents = (int)($item['price_cents'] ?? 0);
    if ($priceCents <= 0) { $skipped[] = $label; continue; }
    $trialEndsOn = $item['trial_ends_on'] ?? null;
    if ($trialEndsOn !== null && $trialEndsOn !== '' && !valid_ymd($trialEndsOn)) {
        $trialEndsOn = null; // bad trial date shouldn't nuke the whole item
    }

    create_subscription($userId, [
        'catalog_service_id' => isset($item['catalog_service_id']) ? (int)$item['catalog_service_id'] : null,
        'custom_name' => trim((string)($item['custom_name'] ?? '')) ?: null,
        'category' => (string)($item['category'] ?? 'other'),
        'tier_name' => $item['tier_name'] ?? null,
        'price_cents' => $priceCents,
        'cadence' => $cadence,
        'started_on' => $startedOn,
        'is_free_trial' => !empty($item['is_free_trial']),
        'trial_ends_on' => $trialEndsOn ?: null,
    ]);
    $created++;
}

echo json_encode(['ok' => true, 'created' => $created, 'skipped' => $skipped]);
