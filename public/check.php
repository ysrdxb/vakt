<?php
// Quick server diagnostic - DELETE THIS FILE AFTER USE
$projectFormPath = __DIR__ . '/../app/Livewire/Projects/ProjectForm.php';
$viewCachePath = __DIR__ . '/../storage/framework/views/';

echo "<pre style='font-family:monospace; font-size:13px; background:#0f172a; color:#cbd5e1; padding:20px;'>";
echo "<strong style='color:#60a5fa;'>===== SERVER DIAGNOSTIC =====</strong>\n\n";

// 1. Check ProjectForm.php
echo "<strong style='color:#f59e0b;'>1. ProjectForm.php</strong>\n";
if (file_exists($projectFormPath)) {
    $content = file_get_contents($projectFormPath);
    $hasDebugOutput   = strpos($content, 'debugOutput') !== false;
    $hasPathDebugger  = strpos($content, 'runPathDebugger') !== false;
    $hasAutoDetect    = strpos($content, 'autoDetectLogPath') !== false;
    $hasDuplicate     = substr_count($content, 'public function updatedServerPath') > 1;
    $lastModified     = date('Y-m-d H:i:s', filemtime($projectFormPath));

    echo "  Last modified : <strong style='color:#10b981;'>$lastModified</strong>\n";
    echo "  debugOutput   : " . ($hasDebugOutput  ? "<span style='color:#10b981;'>YES ✅</span>" : "<span style='color:#ef4444;'>MISSING ❌ (old file!)</span>") . "\n";
    echo "  runPathDebugger: " . ($hasPathDebugger ? "<span style='color:#10b981;'>YES ✅</span>" : "<span style='color:#ef4444;'>MISSING ❌ (old file!)</span>") . "\n";
    echo "  autoDetectLogPath: " . ($hasAutoDetect ? "<span style='color:#10b981;'>YES ✅</span>" : "<span style='color:#ef4444;'>MISSING ❌ (old file!)</span>") . "\n";
    echo "  Duplicate methods: " . ($hasDuplicate  ? "<span style='color:#ef4444;'>YES ❌ (FATAL ERROR CAUSE!)</span>" : "<span style='color:#10b981;'>NO ✅</span>") . "\n";

    // PHP syntax check
    $output = [];
    $return = 0;
    if (function_exists('exec')) {
        exec('php -l ' . escapeshellarg($projectFormPath) . ' 2>&1', $output, $return);
        echo "  PHP syntax    : " . ($return === 0 ? "<span style='color:#10b981;'>OK ✅</span>" : "<span style='color:#ef4444;'>ERROR ❌ " . implode(' ', $output) . "</span>") . "\n";
    } else {
        echo "  PHP syntax    : <span style='color:#f59e0b;'>Cannot check (exec disabled)</span>\n";
    }
} else {
    echo "  <span style='color:#ef4444;'>FILE NOT FOUND ❌</span>\n";
}

// 2. View cache
echo "\n<strong style='color:#f59e0b;'>2. View Cache (storage/framework/views/)</strong>\n";
if (is_dir($viewCachePath)) {
    $files = glob($viewCachePath . '*.php');
    $count = count($files);
    echo "  Cached files  : " . ($count > 0 ? "<span style='color:#ef4444;'>$count files (STALE CACHE - DELETE THESE!)</span>" : "<span style='color:#10b981;'>0 files - clean ✅</span>") . "\n";
} else {
    echo "  <span style='color:#ef4444;'>Directory not found</span>\n";
}

// 3. PHP version
echo "\n<strong style='color:#f59e0b;'>3. Environment</strong>\n";
echo "  PHP version   : " . PHP_VERSION . "\n";
echo "  exec() enabled: " . (function_exists('exec') ? 'YES' : 'NO') . "\n";
echo "  shell_exec()  : " . (function_exists('shell_exec') ? 'YES' : 'NO') . "\n";

echo "\n<strong style='color:#60a5fa;'>===== END =====</strong>\n";
echo "</pre>";
