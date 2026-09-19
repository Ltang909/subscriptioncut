<?php
// Core app configuration. Safe to commit — no secrets live here.
// Real secrets (Stripe keys) live in config/stripe.local.php, which is
// gitignored; see config/stripe.local.php.example.

define('DB_PATH', dirname(__DIR__) . '/data/cutline.sqlite');

// Optional server-local secrets. Copy config/mail.local.php.example to
// config/mail.local.php on the server (it is gitignored and survives deploys)
// and putenv() the CUTLINE_* values there. Loaded before the defines below
// so local values win over defaults.
$mail_local = __DIR__ . '/mail.local.php';
if (file_exists($mail_local)) {
    require $mail_local;
}

// 'php_mail' uses PHP's mail() like api/notify.php already does in production.
// 'smtp' sends via authenticated SMTP (recommended on shared hosting, where
// PHP mail() is frequently throttled or silently dropped).
// 'log' appends outgoing mail to MAIL_LOG_PATH instead of sending — used for
// local development where there's usually no configured MTA.
define('MAIL_DRIVER', getenv('CUTLINE_MAIL_DRIVER') ?: 'php_mail');
define('MAIL_LOG_PATH', dirname(__DIR__) . '/data/mail.log');
// Must match the domain this site actually sends from (builtbylt.com) --
// a mismatched From domain fails SPF/DMARC alignment and gets silently
// spam-filtered or dropped by providers like Gmail.
define('MAIL_FROM', 'Cutline <noreply@builtbylt.com>');

// SMTP driver settings (only used when MAIL_DRIVER === 'smtp').
define('SMTP_HOST', getenv('CUTLINE_SMTP_HOST') ?: '');
define('SMTP_PORT', (int)(getenv('CUTLINE_SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('CUTLINE_SMTP_USER') ?: '');
define('SMTP_PASS', getenv('CUTLINE_SMTP_PASS') ?: '');
// Envelope sender; defaults to SMTP_USER. Should be the mailbox itself.
define('SMTP_FROM', getenv('CUTLINE_SMTP_FROM') ?: '');

define('SESSION_COOKIE_NAME', 'cutline_session');
define('SESSION_LIFETIME_DAYS', 30);
define('LOGIN_TOKEN_LIFETIME_MINUTES', 15);
