<?php
require_once __DIR__ . '/../config/app.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $isNew = !file_exists(DB_PATH);
    $dataDir = dirname(DB_PATH);
    if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    // Wait instead of instantly failing if another request is writing —
    // avoids "database is locked" fatals under concurrent traffic.
    $pdo->exec('PRAGMA busy_timeout = 5000');

    if ($isNew) {
        // Seeded inside a transaction so two concurrent first-requests can't
        // interleave and double-insert the catalog (the UNIQUE index on
        // catalog_services.name is the backstop).
        $pdo->beginTransaction();
        try {
            $schema = file_get_contents(dirname(__DIR__) . '/data/schema.sql');
            $pdo->exec($schema);
            $seed = file_get_contents(dirname(__DIR__) . '/data/seed-catalog.sql');
            $pdo->exec($seed);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    migrate($pdo);

    return $pdo;
}

// Lightweight migrations for databases created before a schema change.
// Each block is idempotent — safe to run on every request.
function migrate(PDO $pdo): void {
    static $ran = false;
    if ($ran) return;
    $ran = true;

    $columns = $pdo->query("PRAGMA table_info(deposits)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('stripe_session_id', $columns, true)) {
        $pdo->exec('ALTER TABLE deposits ADD COLUMN stripe_session_id TEXT');
    }
}
