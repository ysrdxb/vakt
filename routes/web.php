<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Dashboard\OperatorDashboard;
use App\Livewire\Dashboard\ClientDashboard;
use App\Livewire\Projects\ProjectList;
use App\Livewire\Projects\ProjectForm;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Incidents\IncidentList;
use App\Livewire\Incidents\IncidentDetail;
use App\Livewire\Logs\LogViewer;
use App\Livewire\FileIntegrity\FileIntegrityView;
use App\Livewire\Audit\AuditTracker;
use App\Livewire\Vulnerabilities\VulnerabilityList;
use App\Livewire\Improvements\ImprovementKanban;
use App\Livewire\Reports\SqaReport;
use App\Livewire\DailyLogs\DailyLogCalendar;
use App\Livewire\Settings\SettingsPage;
use App\Livewire\Alerts\AlertLog;

// ─── Guest ───────────────────────────────────────────
Route::middleware('guest')->group(function () {
    require __DIR__.'/auth.php';
});

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

        Route::get('/dashboard', OperatorDashboard::class)->name('dashboard');

        // Projects
        Route::get('/projects', ProjectList::class)->name('projects.index');
        Route::get('/projects/create', ProjectForm::class)->name('projects.create');
        Route::get('/projects/{project}/edit', ProjectForm::class)->name('projects.edit');
        Route::get('/projects/{project}', ProjectDetail::class)->name('projects.show');

        // Incidents
        Route::get('/incidents', IncidentList::class)->name('incidents.index');
        Route::get('/incidents/{incident}', IncidentDetail::class)->name('incidents.show');

        // Logs
        Route::get('/logs', LogViewer::class)->name('logs.index');
        Route::get('/logs/{project}', LogViewer::class)->name('logs.project');

        // File Integrity
        Route::get('/file-integrity', FileIntegrityView::class)->name('file-integrity.index');
        Route::get('/file-integrity/{project}', FileIntegrityView::class)->name('file-integrity.project');

        // Daily Logs
        Route::get('/daily-logs', DailyLogCalendar::class)->name('daily-logs.index');

        // Security Audit
        Route::get('/audit', AuditTracker::class)->name('audit.index');
        Route::get('/audit/{project}', AuditTracker::class)->name('audit.project');

        // Vulnerabilities
        Route::get('/vulnerabilities', VulnerabilityList::class)->name('vulnerabilities.index');
        Route::get('/vulnerabilities/{project}', VulnerabilityList::class)->name('vulnerabilities.project');

        // Improvements (operator: all statuses)
        Route::get('/improvements', ImprovementKanban::class)->name('improvements.index');

        // SQA Reports
        Route::get('/reports', SqaReport::class)->name('reports.index');
        Route::get('/reports/view/{report}', function(\App\Models\SqaReport $report) {
            return view('reports.show', compact('report'));
        })->name('reports.show');
        Route::get('/reports/{project}', SqaReport::class)->name('reports.project');

        // Alerts
        Route::get('/alerts', AlertLog::class)->name('alerts.index');

        // Settings
        Route::get('/settings', SettingsPage::class)->name('settings.index');

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
