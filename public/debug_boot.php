<?php
// Standalone diagnostic - bypasses Laravel completely
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Boot Diagnostic</h2>";
echo "<b>PHP:</b> " . PHP_VERSION . "<br>";
echo "<b>Base path:</b> " . dirname(__DIR__) . "<br><br>";

// Try to load Laravel manually and catch the error
try {
    require dirname(__DIR__) . '/vendor/autoload.php';
    echo "<b>✅ Autoload OK</b><br>";
} catch (Throwable $e) {
    echo "<b>❌ Autoload FAILED:</b> " . $e->getMessage() . "<br>";
    exit;
}

try {
    $app = require dirname(__DIR__) . '/bootstrap/app.php';
    echo "<b>✅ App bootstrap OK</b><br>";
} catch (Throwable $e) {
    echo "<b>❌ App bootstrap FAILED:</b> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<b>✅ Kernel OK</b><br>";
} catch (Throwable $e) {
    echo "<b>❌ Kernel FAILED:</b> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<br><b>App booted successfully. The 500 may be route/middleware specific.</b>";
