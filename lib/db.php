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

    if ($isNew) {
        $schema = file_get_contents(dirname(__DIR__) . '/data/schema.sql');
        $pdo->exec($schema);
        $seed = file_get_contents(dirname(__DIR__) . '/data/seed-catalog.sql');
        $pdo->exec($seed);
    }

    return $pdo;
}
