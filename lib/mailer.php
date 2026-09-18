<?php
require_once __DIR__ . '/../config/app.php';

function send_mail(string $to, string $subject, string $body): bool {
    if (MAIL_DRIVER === 'log') {
        $line = sprintf(
            "---- %s ----\nTo: %s\nSubject: %s\n\n%s\n\n",
            date('Y-m-d H:i:s'), $to, $subject, $body
        );
        file_put_contents(MAIL_LOG_PATH, $line, FILE_APPEND);
        return true;
    }
    return @mail($to, $subject, $body, "From: " . MAIL_FROM . "\r\n");
}
