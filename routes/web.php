<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;

// ─── Auth Routes ───────────────────────────────────────────
require __DIR__.'/auth.php';


// Temporary route to fix the database on shared hosting
Route::get('/fix-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return 'Database Fixed! You can now go back to the dashboard.';
});

// ─── Authenticated ────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Root redirect based on role
    Route::get('/', function () {
        return auth()->user()->isOperator()
            ? redirect()->route('dashboard')
            : redirect()->route('client.dashboard');
    });

    // ─── OPERATOR ROUTES ──────────────────────────────
    Route::middleware(['role:operator'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Projects
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::post('/projects/{project}/toggle-active', [ProjectController::class, 'toggleActive'])->name('projects.toggle-active');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::post('/projects/test-connection', [ProjectController::class, 'testConnection'])->name('projects.test-connection');
        Route::post('/projects/auto-detect-log', [ProjectController::class, 'autoDetectLogPath'])->name('projects.auto-detect-log');
        
        Route::post('/projects/{project}/confirm-whitelist', [ProjectController::class, 'confirmWhitelist'])->name('projects.confirm-whitelist');
        Route::post('/projects/{project}/run-scan', [ProjectController::class, 'runScan'])->name('projects.run-scan');
        Route::post('/projects/{project}/test-report', [ProjectController::class, 'testReport'])->name('projects.test-report');
        
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        // Incidents
        Route::get('/incidents', [\App\Http\Controllers\IncidentController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{incident}', [\App\Http\Controllers\IncidentController::class, 'show'])->name('incidents.show');
        Route::post('/incidents/{incident}/status', [\App\Http\Controllers\IncidentController::class, 'transitionStatus'])->name('incidents.transition-status');
        Route::post('/incidents/{incident}/notes', [\App\Http\Controllers\IncidentController::class, 'saveNotes'])->name('incidents.save-notes');
        Route::post('/incidents/{incident}/command', [\App\Http\Controllers\IncidentController::class, 'executeCommand'])->name('incidents.command');

        // Logs
        Route::get('/logs', [\App\Http\Controllers\LogViewerController::class, 'index'])->name('logs.index');
        Route::post('/logs/{entry}/review', [\App\Http\Controllers\LogViewerController::class, 'markReviewed'])->name('logs.review');
        Route::post('/logs/{entry}/analyze', [\App\Http\Controllers\LogViewerController::class, 'analyzeWithAI'])->name('logs.analyze');

        // Audit Tracker
        Route::get('/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
        Route::post('/audit/seed', [\App\Http\Controllers\AuditController::class, 'seed'])->name('audit.seed');
        Route::post('/audit/{item}/status', [\App\Http\Controllers\AuditController::class, 'updateStatus'])->name('audit.update-status');
        Route::post('/audit/{item}/notes', [\App\Http\Controllers\AuditController::class, 'updateNotes'])->name('audit.update-notes');

        // File Integrity
        Route::get('/file-integrity', [\App\Http\Controllers\FileIntegrityController::class, 'index'])->name('file-integrity.index');
        Route::post('/file-integrity/scan', [\App\Http\Controllers\FileIntegrityController::class, 'initScan'])->name('file-integrity.scan');
        Route::post('/file-integrity/{snapshot}/approve', [\App\Http\Controllers\FileIntegrityController::class, 'approveChange'])->name('file-integrity.approve');

        // Daily Logs
        Route::get('/daily-logs', [\App\Http\Controllers\DailyLogController::class, 'index'])->name('daily-logs.index');
        Route::post('/daily-logs/{log}/note', [\App\Http\Controllers\DailyLogController::class, 'addNote'])->name('daily-logs.add-note');

        // Vulnerabilities
        Route::get('/vulnerabilities', [\App\Http\Controllers\VulnerabilityController::class, 'index'])->name('vulnerabilities.index');
        Route::post('/vulnerabilities/{vulnerability}/patched', [\App\Http\Controllers\VulnerabilityController::class, 'markPatched'])->name('vulnerabilities.patched');
        Route::post('/vulnerabilities/{vulnerability}/accept-risk', [\App\Http\Controllers\VulnerabilityController::class, 'acceptRisk'])->name('vulnerabilities.accept-risk');

        // Improvements (operator: all statuses)
        Route::get('/improvements', [\App\Http\Controllers\ImprovementController::class, 'index'])->name('improvements.index');
        Route::post('/improvements', [\App\Http\Controllers\ImprovementController::class, 'store'])->name('improvements.store');
        Route::post('/improvements/{improvement}/status', [\App\Http\Controllers\ImprovementController::class, 'updateStatus'])->name('improvements.status');

        // SQA Reports
        Route::get('/reports', [\App\Http\Controllers\SqaReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [\App\Http\Controllers\SqaReportController::class, 'store'])->name('reports.store');
        Route::post('/reports/{report}/mark-sent', [\App\Http\Controllers\SqaReportController::class, 'markSent'])->name('reports.mark-sent');
        Route::get('/reports/{report}', [\App\Http\Controllers\SqaReportController::class, 'show'])->name('reports.show');

        // TEMPORARY TEST ROUTE FOR FILE ACCESS
        Route::get('/test-log-access', function (\Illuminate\Http\Request $request) {
            $path = $request->input('path', '/var/www/virtual/kunnatta.is/arnrun.is/htdocs/timetable/storage/logs/laravel.log');
            
            $shellOutput = null;
            if (function_exists('shell_exec')) {
                $shellOutput = shell_exec('cat ' . escapeshellarg($path) . ' | head -n 5 2>&1');
            }

            return response()->json([
                'path' => $path,
                'file_exists_web' => @file_exists($path),
                'open_basedir' => ini_get('open_basedir'),
                'user' => get_current_user(),
                'shell_exec_enabled' => function_exists('shell_exec'),
                'shell_output_preview' => $shellOutput ?: 'Empty or blocked'
            ]);
        });

        // Alerts
        Route::get('/alerts', [\App\Http\Controllers\AlertLogController::class, 'index'])->name('alerts.index');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'saveProfile'])->name('settings.profile');
        Route::post('/settings/password', [\App\Http\Controllers\SettingsController::class, 'changePassword'])->name('settings.password');
        Route::post('/settings/client-password', [\App\Http\Controllers\SettingsController::class, 'updateClientPassword'])->name('settings.client-password');

        // Agent PHP file download
        Route::get('/projects/{project}/agent-download', function (\App\Models\Project $project) {
            $content = view('agent.template', compact('project'))->render();
            return response($content, 200, [
                'Content-Type'        => 'application/x-php',
                'Content-Disposition' => 'attachment; filename="soc-agent-' . $project->domain . '.php"',
            ]);
        })->name('projects.agent-download');

        // SOC2 Monthly Report
        Route::get('/projects/{project}/report/monthly', [\App\Http\Controllers\ReportController::class, 'downloadMonthly'])->name('projects.report.monthly');

    });

    // ─── CLIENT ROUTES ────────────────────────────────
    Route::middleware(['role:client'])->group(function () {
        Route::get('/client/dashboard', [\App\Http\Controllers\DashboardController::class, 'clientIndex'])->name('client.dashboard');

        // Client-visible improvements (client_review + approved)
        Route::get('/client/improvements', function () {
            return view('client.improvements');
        })->name('client.improvements');

        // Client SQA Reports
        Route::get('/client/reports', function () {
            $reports = \App\Models\SqaReport::where('status', 'sent')
                ->with('project')
                ->orderByDesc('created_at')
                ->paginate(10);
            return view('client.reports', compact('reports'));
        })->name('client.reports');
    });

});

Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    return 'Cache, config, and views cleared successfully! You can now go back and refresh the page.';
});

Route::get('/pull-updates', function() {
    try {
        $output = shell_exec('git pull origin main 2>&1');
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Illuminate\Support\Facades\Artisan::output();
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $viewOutput = \Illuminate\Support\Facades\Artisan::output();
        
        return 'Git Pull Output: <br><pre>' . htmlspecialchars($output) . '</pre><br>Migration Output:<br><pre>' . htmlspecialchars($migrateOutput) . '</pre><br>View Cache Clear:<br><pre>' . htmlspecialchars($viewOutput) . '</pre>';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/run-migrations', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migrations ran successfully: <br>' . nl2br(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Exception $e) {
        return 'Error running migrations: ' . $e->getMessage();
    }
});

Route::get('/server-path', function() {
    return 'Absolute path of this project: ' . base_path();
});
