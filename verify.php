<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

$token = (string)($_GET['token'] ?? '');
$userId = $token !== '' ? verify_login_token($token) : null;

if ($userId === null) {
    http_response_code(400);
    echo '<p style="font-family:sans-serif;padding:40px;">That link is invalid or has expired. <a href="login.php">Request a new one</a>.</p>';
    exit;
}

create_session($userId);

$stmt = db()->prepare('SELECT COUNT(*) AS c FROM subscriptions WHERE user_id = ?');
$stmt->execute([$userId]);
$hasSubs = (int)$stmt->fetch()['c'] > 0;

header('Location: ' . ($hasSubs ? 'dashboard.php' : 'onboarding.php'));
