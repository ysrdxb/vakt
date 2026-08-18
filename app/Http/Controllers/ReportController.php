<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function downloadMonthly(Project $project): Response
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        $incidents = $project->incidents()
            ->whereBetween('detected_at', [$startDate, $endDate])
            ->orderBy('detected_at', 'desc')
            ->get();

        $uptimeLogs = $project->uptimeLogs()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalPings = $uptimeLogs->count();
        $successfulPings = $uptimeLogs->where('status_code', 200)->count();
        $uptimePercentage = $totalPings > 0 ? round(($successfulPings / $totalPings) * 100, 2) : 100;

        $data = [
            'project' => $project,
            'startDate' => $startDate->format('M d, Y'),
            'endDate' => $endDate->format('M d, Y'),
            'incidents' => $incidents,
            'uptimePercentage' => $uptimePercentage,
            'totalIncidents' => $incidents->count(),
            'criticalIncidents' => $incidents->where('severity', 'p1')->count(),
            'resolvedIncidents' => $incidents->whereIn('status', ['resolved', 'closed'])->count(),
        ];

        $pdf = Pdf::loadView('reports.monthly', $data);

        return $pdf->download("vakt-soc-report-{$project->domain}-{$endDate->format('Y-m')}.pdf");
    }
}
