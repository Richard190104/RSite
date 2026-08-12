<?php
/**
 * One-off remote migration runner for hosts without SSH access.
 *
 * SECURITY: this file MUST be deleted from the server immediately after
 * use. Anyone who finds this URL (with the right token) can run pending
 * migrations against the production database.
 *
 * The placeholder below is swapped for the real value (from MIGRATION_TOKEN
 * in the environment) by bin/deploy.mjs's --migrate flow at upload time —
 * the real token is never committed. Uploading this file by hand instead?
 * Replace the placeholder yourself before uploading.
 *
 * Usage: upload to webroot/, visit
 *   https://yourdomain/run-migrations.php?token=YOUR_SECRET
 * then DELETE this file from the server right away.
 */

const SECRET_TOKEN = '__MIGRATION_TOKEN__';

if (!hash_equals(SECRET_TOKEN, $_GET['token'] ?? '')) {
    http_response_code(404);
    exit('Not found.');
}

chdir(__DIR__ . '/..');
require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/bootstrap.php';

header('Content-Type: text/plain');

$migrations = new \Migrations\Migrations();

echo "=== Status BEFORE ===\n";
foreach ($migrations->status() as $row) {
    echo sprintf("%-10s %-20s %s\n", $row['status'], $row['id'], $row['name']);
}

echo "\n=== Running migrate() ===\n";
$success = $migrations->migrate();
echo $success ? "OK: migrate() succeeded.\n" : "FAILED: migrate() returned false.\n";

echo "\n=== Status AFTER ===\n";
foreach ($migrations->status() as $row) {
    echo sprintf("%-10s %-20s %s\n", $row['status'], $row['id'], $row['name']);
}

echo "\n=== DELETE THIS FILE FROM THE SERVER NOW ===\n";