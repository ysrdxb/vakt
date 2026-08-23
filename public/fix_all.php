<?php
echo "<pre>Starting server fix...\n";

// 1. Delete ghost Livewire files if they exist
$ghostJs = '/var/www/virtual/kunnatta.is/health/htdocs/vendor/livewire/livewire.js';
$ghostManifest = '/var/www/virtual/kunnatta.is/health/htdocs/vendor/livewire/manifest.json';
if (file_exists($ghostJs)) {
    unlink($ghostJs);
    echo "Deleted ghost livewire.js\n";
}
if (file_exists($ghostManifest)) {
    unlink($ghostManifest);
    echo "Deleted ghost manifest.json\n";
}

// 2. Download Composer if not present
putenv('COMPOSER_HOME=/var/www/virtual/kunnatta.is/health/htdocs/.composer');
$cwd = '/var/www/virtual/kunnatta.is/health/htdocs';
if (!file_exists($cwd . '/composer.phar')) {
    echo "Downloading composer.phar...\n";
    copy('https://getcomposer.org/download/latest-stable/composer.phar', $cwd . '/composer.phar');
}

// 3. Run composer dump-autoload to regenerate installed.json and autoload files
echo "Running composer dump-autoload...\n";
$output = shell_exec('php ' . escapeshellarg($cwd . '/composer.phar') . ' dump-autoload -d ' . escapeshellarg($cwd) . ' 2>&1');
echo htmlspecialchars($output) . "\n";

// 4. Clear Laravel Caches
echo "Clearing Laravel caches...\n";
$output2 = shell_exec('php ' . escapeshellarg($cwd . '/artisan') . ' optimize:clear 2>&1');
echo htmlspecialchars($output2) . "\n";

echo "Done!</pre>";
