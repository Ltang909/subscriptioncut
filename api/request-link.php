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

// Throttle: max 5 sign-in emails per address per hour. Without this the
// endpoint can be used to spam someone else's inbox with login emails.
$stmt = db()->prepare(
    "SELECT COUNT(*) AS c FROM auth_tokens t
     JOIN users u ON u.id = t.user_id
     WHERE u.email = ? AND t.purpose = 'login'
       AND t.created_at > datetime('now', '-1 hour')"
);
$stmt->execute([$email]);
if ((int)$stmt->fetch()['c'] >= 5) {
    http_response_code(429);
    echo json_encode(['error' => 'rate_limited']);
    exit;
}

$token = create_login_token($email);
$scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$dir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$link = $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir . '/verify.php?token=' . urlencode($token);

$body = "Click below to sign in to Cutline. This link expires in " . LOGIN_TOKEN_LIFETIME_MINUTES . " minutes and can only be used once.\n\n"
      . $link . "\n\n"
      . "If you didn't request this, you can ignore this email.\n";

$sent = send_mail($email, 'Your Cutline sign-in link', $body);
if (!$sent) {
    // The mailer logs the underlying reason to data/mail-errors.log.
    // Tell the user the truth instead of "check your email".
    error_log("request-link: send_mail failed for {$email}");
    http_response_code(500);
    echo json_encode(['error' => 'email_failed']);
    exit;
}

echo json_encode(['ok' => true]);
