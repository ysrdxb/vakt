<?php
$config = @file_get_contents('/var/www/virtual/kunnatta.is/health/htdocs/config/livewire.php');
echo $config ? "Config found" : "Config missing";
