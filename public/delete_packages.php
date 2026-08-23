<?php
$file = '/var/www/virtual/kunnatta.is/health/htdocs/bootstrap/cache/packages.php';
$services = '/var/www/virtual/kunnatta.is/health/htdocs/bootstrap/cache/services.php';
if (file_exists($file)) unlink($file);
if (file_exists($services)) unlink($services);
echo "Deleted cache files.";
