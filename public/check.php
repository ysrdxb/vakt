<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    echo "STATUS: " . $response->getStatusCode() . "<br>";
    echo "BODY: " . htmlentities(substr($response->getContent(), 0, 500));
} catch (\Throwable $e) {
    echo "<h1>ERROR CAUGHT!</h1>";
    echo "<pre>" . (string)$e . "</pre>";
}
