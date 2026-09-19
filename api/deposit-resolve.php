<?php
// Backwards-compatibility shim: resolve_deposit() used to live here and
// cron/check-trial-deadlines.php included this file directly. The function
// now lives in lib/deposits.php so CLI scripts never include a web endpoint.
require_once __DIR__ . '/../config/features.php';
if (!DEPOSIT_FEATURE_ENABLED) { http_response_code(404); exit; }

require_once __DIR__ . '/../lib/deposits.php';
