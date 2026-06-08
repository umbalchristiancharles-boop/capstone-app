<?php
// CLI helper to clear common Laravel caches
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$commands = [
    'php artisan cache:clear',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan optimize:clear',
];

echo "Starting Laravel cache cleanup...\n\n";
foreach ($commands as $cmd) {
    echo "Running: $cmd\n";
    passthru($cmd, $status);
    if ($status !== 0) {
        echo "Command exited with status: $status\n";
    }
    echo "\n";
}

echo "All done.\n";
