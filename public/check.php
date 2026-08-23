<?php
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found at $logFile";
    exit;
}

$lines = file($logFile);
$lastLines = array_slice($lines, -100);
echo "<pre>" . htmlentities(implode("", $lastLines)) . "</pre>";


