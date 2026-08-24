<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Projects\ProjectForm;
use App\Livewire\Dashboard\ClientDashboard;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Livewire\Incidents\IncidentList;
use App\Livewire\Incidents\IncidentDetail;
use App\Livewire\Logs\LogViewer;
use App\Livewire\FileIntegrity\FileIntegrityView;
use App\Livewire\Audit\AuditTracker;
use App\Livewire\DailyLogs\DailyLogCalendar;

// ─── Guest ───────────────────────────────────────────
Route::middleware('guest')->group(function () {
    require __DIR__.'/auth.php';
});

// Isolated Livewire Diagnostic Page
Route::get('/livewire-test', \App\Livewire\TestLivewire::class);

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
        Route::get('/daily-logs', DailyLogCalendar::class)->name('daily-logs.index');

        // Vulnerabilities
        Route::get('/vulnerabilities', [\App\Http\Controllers\VulnerabilityController::class, 'index'])->name('vulnerabilities.index');
        Route::post('/vulnerabilities/fetch', [\App\Http\Controllers\VulnerabilityController::class, 'fetch'])->name('vulnerabilities.fetch');
        Route::post('/vulnerabilities/{v}/patch', [\App\Http\Controllers\VulnerabilityController::class, 'markPatched'])->name('vulnerabilities.patch');
        Route::post('/vulnerabilities/{v}/accept-risk', [\App\Http\Controllers\VulnerabilityController::class, 'acceptRisk'])->name('vulnerabilities.accept-risk');

        // Improvements (operator: all statuses)
        Route::get('/improvements', [\App\Http\Controllers\ImprovementController::class, 'index'])->name('improvements.index');
        Route::post('/improvements', [\App\Http\Controllers\ImprovementController::class, 'store'])->name('improvements.store');
        Route::post('/improvements/{i}/status', [\App\Http\Controllers\ImprovementController::class, 'updateStatus'])->name('improvements.status');

        // SQA Reports
        Route::get('/reports', [\App\Http\Controllers\SqaReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/fetch', [\App\Http\Controllers\SqaReportController::class, 'fetch'])->name('reports.fetch');
        Route::post('/reports/generate', [\App\Http\Controllers\SqaReportController::class, 'generate'])->name('reports.generate');
        Route::post('/reports/{report}/mark-sent', [\App\Http\Controllers\SqaReportController::class, 'markSent'])->name('reports.mark-sent');
        Route::get('/reports/view/{report}', function(\App\Models\SqaReport $report) {
            return view('reports.show', compact('report'));
        })->name('reports.show');

        // Alerts
        Route::get('/alerts', [\App\Http\Controllers\AlertLogController::class, 'index'])->name('alerts.index');
        Route::post('/alerts/fetch', [\App\Http\Controllers\AlertLogController::class, 'fetch'])->name('alerts.fetch');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

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
        Route::get('/client/dashboard', ClientDashboard::class)->name('client.dashboard');

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
