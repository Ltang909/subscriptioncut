<?php
// Core app configuration. Safe to commit — no secrets live here.
// Real secrets (Stripe keys) live in config/stripe.local.php, which is
// gitignored; see config/stripe.local.php.example.

define('DB_PATH', dirname(__DIR__) . '/data/cutline.sqlite');

// 'php_mail' uses PHP's mail() like api/notify.php already does in production.
// 'log' appends outgoing mail to MAIL_LOG_PATH instead of sending — used for
// local development where there's usually no configured MTA.
define('MAIL_DRIVER', getenv('CUTLINE_MAIL_DRIVER') ?: 'php_mail');
define('MAIL_LOG_PATH', dirname(__DIR__) . '/data/mail.log');
define('MAIL_FROM', 'Cutline <noreply@leontang.ca>');

define('SESSION_COOKIE_NAME', 'cutline_session');
define('SESSION_LIFETIME_DAYS', 30);
define('LOGIN_TOKEN_LIFETIME_MINUTES', 15);
