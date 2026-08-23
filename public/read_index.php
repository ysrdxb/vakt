<?php
echo "<pre>";
$indexContent = @file_get_contents('/var/www/virtual/kunnatta.is/health/htdocs/index.php');
echo htmlspecialchars($indexContent);
echo "</pre>";
