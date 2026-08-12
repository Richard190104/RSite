<?php
/**
 * One-off remote cache clearer for hosts without SSH access. Same pattern
 * as run-migrations.php — see that file's comment for the full rationale.
 *
 * SECURITY: this file MUST be deleted from the server immediately after
 * use. Anyone who finds this URL (with the right token) can clear the
 * production cache repeatedly (a mild DoS: forces the app to rebuild
 * translation/schema caches on every request until it's cached again).
 *
 * The placeholder below is swapped for the real value (from CACHE_TOKEN in
 * the environment) by bin/deploy.mjs's --clear-cache flow at upload time —
 * the real token is never committed. Uploading this file by hand instead?
 * Replace the placeholder yourself before uploading.
 *
 * Usage: upload to webroot/, visit
 *   https://yourdomain/clear-cache.php?token=YOUR_SECRET
 * then DELETE this file from the server right away.
 */

const SECRET_TOKEN = '__CACHE_TOKEN__';

if (!hash_equals(SECRET_TOKEN, $_GET['token'] ?? '')) {
    http_response_code(404);
    exit('Not found.');
}

chdir(__DIR__ . '/..');
require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/bootstrap.php';

header('Content-Type: text/plain');

use Cake\Cache\Cache;

echo "=== Configured cache engines ===\n";
foreach (Cache::configured() as $name) {
    echo "- {$name}\n";
}

echo "\n=== Clearing all ===\n";
foreach (Cache::configured() as $name) {
    $result = Cache::clear($name);
    echo sprintf("%-20s %s\n", $name, $result ? 'cleared' : 'FAILED (or was already empty)');
}

echo "\n=== DELETE THIS FILE FROM THE SERVER NOW ===\n";
