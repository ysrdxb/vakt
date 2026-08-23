<?php

use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

echo "<html><head><title>Livewire Diagnostic</title><style>body { font-family: monospace; background: #111; color: #0f0; padding: 20px; }</style></head><body>";
echo "<h1>Livewire Production Diagnostic</h1>";

echo "<h2>1. URL Generation Checks</h2>";
echo "APP_URL in .env: " . config('app.url') . "<br>";
echo "Calculated url('/') : " . url('/') . "<br>";
echo "Livewire Script URL: " . url('/livewire-js-asset') . "<br>";
echo "Livewire Update URL: " . url('/livewire-api-update') . "<br>";

echo "<h2>2. Request Data</h2>";
echo "Scheme: " . $request->getScheme() . "<br>";
echo "Host: " . $request->getHost() . "<br>";
echo "Base URL: " . $request->getBaseUrl() . "<br>";
echo "Root URL: " . $request->root() . "<br>";
echo "Path Info: " . $request->getPathInfo() . "<br>";

echo "<h2>3. Important Server Variables</h2>";
echo "<pre>";
print_r([
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'NOT SET',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'NOT SET',
    'HTTPS' => $_SERVER['HTTPS'] ?? 'NOT SET',
    'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'NOT SET',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'NOT SET',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'NOT SET',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET',
]);
echo "</pre>";

echo "<h2>4. File Checks</h2>";
$manifest = __DIR__.'/vendor/livewire/manifest.json';
echo "Livewire Manifest Exists: " . (file_exists($manifest) ? 'YES' : 'NO') . "<br>";
if (file_exists($manifest)) {
    echo "Manifest Content: " . file_get_contents($manifest) . "<br>";
}
echo "Livewire JS Exists: " . (file_exists(__DIR__.'/vendor/livewire/livewire.min.js') ? 'YES' : 'NO') . "<br>";

echo "</body></html>";
