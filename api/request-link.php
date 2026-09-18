<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/mailer.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$email = trim((string)($input['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_email']);
    exit;
}

$token = create_login_token($email);
$scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$dir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$link = $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir . '/verify.php?token=' . urlencode($token);

$body = "Click below to sign in to Cutline. This link expires in " . LOGIN_TOKEN_LIFETIME_MINUTES . " minutes and can only be used once.\n\n"
      . $link . "\n\n"
      . "If you didn't request this, you can ignore this email.\n";

send_mail($email, 'Your Cutline sign-in link', $body);

echo json_encode(['ok' => true]);
