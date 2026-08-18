<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SOC2 Monthly Report - {{ $project->domain }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #6366f1; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #4f46e5; }
        .title { font-size: 28px; margin-top: 10px; margin-bottom: 5px; }
        .subtitle { color: #6b7280; font-size: 14px; }
        
        .stats-grid { width: 100%; margin-bottom: 40px; }
        .stats-grid td { padding: 15px; background: #f3f4f6; border-radius: 8px; text-align: center; width: 25%; border: 2px solid #fff; }
        .stat-value { font-size: 24px; font-weight: bold; color: #111827; }
        .stat-label { font-size: 12px; text-transform: uppercase; color: #6b7280; margin-top: 5px; }
        
        .section-title { font-size: 18px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        table.data-table th { background: #f9fafb; color: #374151; font-size: 12px; text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        table.data-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge.critical { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
        .badge.high { background: #fff7ed; color: #ea580c; border: 1px solid #fdba74; }
        .badge.medium { background: #fefce8; color: #ca8a04; border: 1px solid #fde047; }
        
        .footer { margin-top: 50px; font-size: 12px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">Vakt SOC Platform</div>
        <div class="title">Security & Uptime Report</div>
        <div class="subtitle">Project: {{ $project->domain }}<br>Period: {{ $startDate }} to {{ $endDate }}</div>
    </div>

    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-value">{{ $uptimePercentage }}%</div>
                <div class="stat-label">Uptime</div>
            </td>
            <td>
                <div class="stat-value">{{ $totalIncidents }}</div>
                <div class="stat-label">Total Incidents</div>
            </td>
            <td>
                <div class="stat-value" style="color: {{ $criticalIncidents > 0 ? '#dc2626' : '#111827' }}">{{ $criticalIncidents }}</div>
                <div class="stat-label">Critical Incidents</div>
            </td>
            <td>
                <div class="stat-value" style="color: #059669">{{ $resolvedIncidents }}</div>
                <div class="stat-label">Resolved Incidents</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Incident Summary</div>
    
    @if($incidents->isEmpty())
        <p style="color: #6b7280; font-style: italic;">No security incidents recorded during this period.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Severity</th>
                    <th>Title</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incidents as $incident)
                <tr>
                    <td style="white-space: nowrap">{{ $incident->detected_at->format('M d, H:i') }}</td>
                    <td>
                        <span class="badge {{ strtolower($incident->severity) }}">{{ strtoupper($incident->severity) }}</span>
                    </td>
                    <td>{{ $incident->title }}</td>
                    <td>{{ ucfirst($incident->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generated automatically by Vakt Security Operations Center on {{ now()->format('F j, Y, g:i a') }} UTC.<br>
        This document serves as proof of continuous monitoring for compliance purposes.
    </div>

</body>
</html>
