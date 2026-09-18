<?php
// Cutline — "notify me / request a company" handler.
// No database for this MVP: it just emails you each request. If this takes
// off and you want a dashboard of requests instead, that's a natural v2.

header('Content-Type: application/json');

// ---- CONFIGURE THIS ----
define('NOTIFY_TO', 'leon@leontang.ca');
// -------------------------

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$company = trim((string)($input['company'] ?? ''));
$email = trim((string)($input['email'] ?? ''));

if ($company === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_company']);
    exit;
}
if (mb_strlen($company) > 200) $company = mb_substr($company, 0, 200);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = '(invalid email provided: ' . htmlspecialchars($email) . ')';

$subject = "Cutline: request to add \"{$company}\"";
$body = "Company requested: {$company}\n"
      . "Visitor email: " . ($email ?: '(not provided)') . "\n"
      . "Time: " . date('Y-m-d H:i:s') . "\n";

$ok = @mail(NOTIFY_TO, $subject, $body, "From: Cutline <noreply@leontang.ca>\r\n");

echo json_encode(['ok' => (bool)$ok]);
