<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/subscriptions.php';
$userId = require_login();

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;

if ($id && ($_POST['action'] ?? '') === 'cancel') {
    cancel_subscription($userId, $id);
    header('Location: ../dashboard.php');
    exit;
}

$validCadences = ['weekly', 'monthly', 'annual'];
$cadence = $_POST['cadence'] ?? 'monthly';
if (!in_array($cadence, $validCadences, true)) $cadence = 'monthly';

$priceDollars = (float)($_POST['price_dollars'] ?? 0);
$startedOn = $_POST['started_on'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startedOn)) $startedOn = date('Y-m-d');

$data = [
    'custom_name' => trim((string)($_POST['custom_name'] ?? '')),
    'category' => (string)($_POST['category'] ?? 'other'),
    'tier_name' => trim((string)($_POST['tier_name'] ?? '')) ?: null,
    'price_cents' => (int)round($priceDollars * 100),
    'cadence' => $cadence,
    'started_on' => $startedOn,
    'is_free_trial' => !empty($_POST['is_free_trial']),
    'trial_ends_on' => $_POST['trial_ends_on'] ?: null,
];

if ($data['custom_name'] === '' || $data['price_cents'] <= 0) {
    header('Location: ../subscription-edit.php' . ($id ? '?id=' . $id : ''));
    exit;
}

if ($id) {
    $existing = get_subscription($userId, $id);
    if ($existing) update_subscription($userId, $id, $data);
} else {
    create_subscription($userId, $data);
}

header('Location: ../dashboard.php');
