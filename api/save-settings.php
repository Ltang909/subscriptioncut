<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
$userId = require_login();

$days = (int)($_POST['notify_days_before'] ?? 3);
if ($days < 1) $days = 1;
if ($days > 30) $days = 30;

db()->prepare('UPDATE users SET notify_days_before = ? WHERE id = ?')->execute([$days, $userId]);

header('Location: ../settings.php');
