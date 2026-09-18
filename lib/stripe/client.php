<?php
// Minimal raw-cURL Stripe REST client — deliberately not the Stripe PHP SDK,
// to keep the project dependency-free (no Composer/vendor directory).
// Only ever loaded when DEPOSIT_FEATURE_ENABLED is true and config/stripe.local.php exists.

function stripe_request(string $method, string $path, array $params = []): array {
    $ch = curl_init('https://api.stripe.com/v1' . $path);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
        CURLOPT_CUSTOMREQUEST => $method,
    ];
    if ($params) $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true) ?: [];
    if ($status >= 400) {
        throw new RuntimeException('Stripe API error: ' . ($decoded['error']['message'] ?? $response));
    }
    return $decoded;
}
