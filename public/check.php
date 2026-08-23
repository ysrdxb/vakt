<?php
echo "<html><head><title>Diagnostic</title><style>body { font-family: monospace; background: #111; color: #0f0; padding: 20px; }</style></head><body>";
echo "<h1>Simple Diagnostic</h1>";
echo "<pre>";
print_r([
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'NOT SET',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'NOT SET',
    'HTTPS' => $_SERVER['HTTPS'] ?? 'NOT SET',
    'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'NOT SET',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'NOT SET',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'NOT SET',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET',
    'PHP_VERSION' => phpversion(),
]);
echo "</pre></body></html>";
