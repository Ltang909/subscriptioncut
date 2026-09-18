<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../config/app.php';

function find_or_create_user(string $email): int {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row) return (int)$row['id'];

    $stmt = $pdo->prepare('INSERT INTO users (email) VALUES (?)');
    $stmt->execute([$email]);
    return (int)$pdo->lastInsertId();
}

// Returns the raw token to embed in the emailed link. Only its hash is stored.
function create_login_token(string $email): string {
    $userId = find_or_create_user($email);
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expiresAt = gmdate('Y-m-d H:i:s', time() + LOGIN_TOKEN_LIFETIME_MINUTES * 60);

    $stmt = db()->prepare(
        'INSERT INTO auth_tokens (user_id, token_hash, purpose, expires_at) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $hash, 'login', $expiresAt]);

    return $token;
}

// Returns the user id on success, or null if the token is invalid/expired/used.
function verify_login_token(string $token): ?int {
    $hash = hash('sha256', $token);
    $pdo = db();
    $stmt = $pdo->prepare(
        "SELECT id, user_id FROM auth_tokens
         WHERE token_hash = ? AND purpose = 'login' AND used_at IS NULL
           AND expires_at > datetime('now')"
    );
    $stmt->execute([$hash]);
    $row = $stmt->fetch();
    if (!$row) return null;

    $pdo->prepare('UPDATE auth_tokens SET used_at = datetime(\'now\') WHERE id = ?')
        ->execute([$row['id']]);

    return (int)$row['user_id'];
}

function create_session(int $userId): void {
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $lifetimeSeconds = SESSION_LIFETIME_DAYS * 86400;
    $expiresAt = gmdate('Y-m-d H:i:s', time() + $lifetimeSeconds);

    $stmt = db()->prepare(
        'INSERT INTO sessions (user_id, session_token_hash, expires_at) VALUES (?, ?, ?)'
    );
    $stmt->execute([$userId, $hash, $expiresAt]);

    setcookie(SESSION_COOKIE_NAME, $token, [
        'expires' => time() + $lifetimeSeconds,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
}

function current_user_id(): ?int {
    static $cached = false;
    if ($cached !== false) return $cached;

    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? '';
    if ($token === '') { $cached = null; return null; }

    $hash = hash('sha256', $token);
    $stmt = db()->prepare(
        "SELECT user_id FROM sessions WHERE session_token_hash = ? AND expires_at > datetime('now')"
    );
    $stmt->execute([$hash]);
    $row = $stmt->fetch();
    $cached = $row ? (int)$row['user_id'] : null;
    return $cached;
}

function require_login(): int {
    $userId = current_user_id();
    if ($userId === null) {
        header('Location: login.php');
        exit;
    }
    return $userId;
}

function logout(): void {
    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? '';
    if ($token !== '') {
        $hash = hash('sha256', $token);
        db()->prepare('DELETE FROM sessions WHERE session_token_hash = ?')->execute([$hash]);
    }
    setcookie(SESSION_COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/']);
}
