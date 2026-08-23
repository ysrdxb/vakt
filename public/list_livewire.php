<?php
echo "<pre>";
echo "Listing /var/www/virtual/kunnatta.is/health/htdocs/vendor/livewire/livewire/:\n";
$dirs = @scandir('/var/www/virtual/kunnatta.is/health/htdocs/vendor/livewire/livewire/');
if ($dirs) {
    foreach ($dirs as $dir) {
        echo $dir . "\n";
    }
} else {
    echo "Could not read directory.\n";
}
echo "</pre>";
