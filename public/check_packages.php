<?php
echo "<pre>";
$file = '/var/www/virtual/kunnatta.is/health/htdocs/bootstrap/cache/packages.php';
if (file_exists($file)) {
    echo htmlspecialchars(file_get_contents($file));
} else {
    echo "packages.php DOES NOT EXIST";
}
echo "</pre>";
