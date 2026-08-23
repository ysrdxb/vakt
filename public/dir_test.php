<?php
echo "<pre>";
echo "Listing /var/www/virtual/kunnatta.is/health/:\n";
system('ls -la /var/www/virtual/kunnatta.is/health/');
echo "\nListing /var/www/virtual/kunnatta.is/health/htdocs/:\n";
system('ls -la /var/www/virtual/kunnatta.is/health/htdocs/');
echo "</pre>";
