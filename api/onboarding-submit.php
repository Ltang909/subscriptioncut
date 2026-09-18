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

$validCadences = ['weekly', 'monthly', 'annual'];
$created = 0;

foreach ($items as $item) {
    $cadence = $item['cadence'] ?? 'monthly';
    if (!in_array($cadence, $validCadences, true)) continue;
    $startedOn = $item['started_on'] ?? null;
    if (!$startedOn || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startedOn)) continue;
    $priceCents = (int)($item['price_cents'] ?? 0);
    if ($priceCents <= 0) continue;

    create_subscription($userId, [
        'catalog_service_id' => isset($item['catalog_service_id']) ? (int)$item['catalog_service_id'] : null,
        'custom_name' => trim((string)($item['custom_name'] ?? '')) ?: null,
        'category' => (string)($item['category'] ?? 'other'),
        'tier_name' => $item['tier_name'] ?? null,
        'price_cents' => $priceCents,
        'cadence' => $cadence,
        'started_on' => $startedOn,
        'is_free_trial' => !empty($item['is_free_trial']),
        'trial_ends_on' => $item['trial_ends_on'] ?: null,
    ]);
    $created++;
}

echo json_encode(['ok' => true, 'created' => $created]);
