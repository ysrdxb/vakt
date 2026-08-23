<?php
$file = '/var/www/virtual/kunnatta.is/health/htdocs/vendor/composer/installed.json';
if (file_exists($file)) {
    echo "installed.json exists. Size: " . filesize($file) . " bytes\n";
    $data = json_decode(file_get_contents($file), true);
    $packages = isset($data['packages']) ? $data['packages'] : $data;
    $found = false;
    foreach ($packages as $pkg) {
        if (isset($pkg['name']) && $pkg['name'] === 'livewire/livewire') {
            echo "Livewire version in installed.json: " . $pkg['version'] . "\n";
            $found = true;
        }
    }
    if (!$found) echo "Livewire NOT FOUND in installed.json\n";
} else {
    echo "installed.json DOES NOT EXIST\n";
}
