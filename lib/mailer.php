<?php
require_once __DIR__ . '/../config/app.php';

/**
 * Sends a plain-text email. Returns true on success, false on failure.
 * Failures are appended to data/mail-errors.log with the underlying reason —
 * never silently swallowed.
 */
function send_mail(string $to, string $subject, string $body): bool {
    if (MAIL_DRIVER === 'log') {
        $line = sprintf(
            "---- %s ----\nTo: %s\nSubject: %s\n\n%s\n\n",
            date('Y-m-d H:i:s'), $to, $subject, $body
        );
        file_put_contents(MAIL_LOG_PATH, $line, FILE_APPEND);
        return true;
    }

    if (MAIL_DRIVER === 'smtp') {
        [$ok, $error] = smtp_send($to, $subject, $body);
        if (!$ok) {
            mail_error_log($to, $subject, $error);
        }
        return $ok;
    }

    // php_mail: PHP's mail(). On shared hosting this hands the message to the
    // local MTA, which may silently drop it — prefer the smtp driver in
    // production. The @ suppresses PHP warnings; the boolean is still
    // returned honestly so callers can react.
    $headers = "From: " . MAIL_FROM . "\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "MIME-Version: 1.0\r\n";
    $ok = @mail($to, $subject, $body, $headers);
    if (!$ok) {
        mail_error_log($to, $subject, 'PHP mail() returned false');
    }
    return $ok;
}

function mail_error_log(string $to, string $subject, string $error): void {
    $line = sprintf(
        "[%s] FAILED to=%s subject=%s error=%s\n",
        date('Y-m-d H:i:s'), $to, $subject, $error
    );
    @file_put_contents(dirname(__DIR__) . '/data/mail-errors.log', $line, FILE_APPEND);
}

/**
 * Minimal dependency-free SMTP client (AUTH LOGIN + STARTTLS on port 587).
 * Returns [bool $ok, string $error].
 */
function smtp_send(string $to, string $subject, string $body): array {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    if ($host === '' || SMTP_USER === '') {
        return [false, 'SMTP not configured (CUTLINE_SMTP_HOST / CUTLINE_SMTP_USER empty)'];
    }

    $fp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15);
    if (!$fp) {
        return [false, "connect failed: {$errstr} ({$errno})"];
    }
    stream_set_timeout($fp, 15);

    $expect = function (array $codes) use ($fp): array {
        $line = '';
        while (($l = fgets($fp, 1024)) !== false) {
            $line .= $l;
            if (preg_match('/^\d{3} /', $l)) break;
        }
        $code = (int)substr($line, 0, 3);
        return [in_array($code, $codes, true), trim($line)];
    };
    $cmd = function (string $c, array $codes) use ($fp, $expect): array {
        fwrite($fp, $c . "\r\n");
        return $expect($codes);
    };

    [$ok, $greet] = $expect([220]);
    if (!$ok) { fclose($fp); return [false, "greeting failed: {$greet}"]; }

    $ehlo_host = gethostname() ?: 'localhost';
    [$ok, $msg] = $cmd("EHLO {$ehlo_host}", [250]);
    if (!$ok) { fclose($fp); return [false, "EHLO failed: {$msg}"]; }

    [$ok, $msg] = $cmd("STARTTLS", [220]);
    if (!$ok) { fclose($fp); return [false, "STARTTLS failed: {$msg}"]; }
    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($fp);
        return [false, 'TLS negotiation failed'];
    }
    [$ok, $msg] = $cmd("EHLO {$ehlo_host}", [250]);
    if (!$ok) { fclose($fp); return [false, "EHLO (after TLS) failed: {$msg}"]; }

    [$ok, $msg] = $cmd("AUTH LOGIN", [334]);
    if (!$ok) { fclose($fp); return [false, "AUTH LOGIN rejected: {$msg}"]; }
    [$ok, $msg] = $cmd(base64_encode(SMTP_USER), [334]);
    if (!$ok) { fclose($fp); return [false, "SMTP username rejected: {$msg}"]; }
    [$ok, $msg] = $cmd(base64_encode(SMTP_PASS), [235]);
    if (!$ok) { fclose($fp); return [false, "SMTP authentication failed: {$msg}"]; }

    $envelope_from = SMTP_FROM !== '' ? SMTP_FROM : SMTP_USER;
    [$ok, $msg] = $cmd("MAIL FROM:<{$envelope_from}>", [250]);
    if (!$ok) { fclose($fp); return [false, "MAIL FROM rejected: {$msg}"]; }
    [$ok, $msg] = $cmd("RCPT TO:<{$to}>", [250, 251]);
    if (!$ok) { fclose($fp); return [false, "RCPT TO rejected: {$msg}"]; }
    [$ok, $msg] = $cmd("DATA", [354]);
    if (!$ok) { fclose($fp); return [false, "DATA rejected: {$msg}"]; }

    // Dot-stuff lines starting with '.' per RFC 5321 section 4.5.2.
    $safe_body = preg_replace('/^\./m', '..', $body);
    $headers = "From: " . MAIL_FROM . "\r\n"
             . "To: {$to}\r\n"
             . "Subject: {$subject}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Date: " . date('r') . "\r\n";
    fwrite($fp, $headers . "\r\n" . $safe_body . "\r\n.\r\n");
    [$ok, $msg] = $expect([250]);
    $cmd("QUIT", [221]);
    fclose($fp);
    if (!$ok) {
        return [false, "message rejected: {$msg}"];
    }
    return [true, ''];
}
