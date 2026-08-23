<?php
echo "<pre>";
echo "Listing /var/www/virtual/kunnatta.is/health/:\n";
$dirs = @scandir('/var/www/virtual/kunnatta.is/health/');
if ($dirs) {
    foreach ($dirs as $dir) {
        echo $dir . "\n";
    }
} else {
    echo "Could not read directory.\n";
}

echo "\nListing /var/www/virtual/kunnatta.is/health/htdocs/:\n";
$dirs = @scandir('/var/www/virtual/kunnatta.is/health/htdocs/');
if ($dirs) {
    foreach ($dirs as $dir) {
        echo $dir . "\n";
    }
} else {
    echo "Could not read directory.\n";
}
echo "</pre>";
