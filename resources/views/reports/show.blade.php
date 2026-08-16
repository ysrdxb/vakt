<x-app-layout>
    <x-slot name="title">Report: {{ $report->title }}</x-slot>

    <div class="mb-6" style="display:flex;justify-content:space-between;align-items:center;">
        <div class="breadcrumbs">
            <a href="{{ route('dashboard') }}" class="text-muted" style="text-decoration:none;">Dashboard</a>
            <span class="sep" style="margin:0 8px;color:var(--color-border)">›</span>
            <a href="{{ route('reports.index') }}" class="text-muted" style="text-decoration:none;">SQA Reports</a>
            <span class="sep" style="margin:0 8px;color:var(--color-border)">›</span>
            <span style="font-weight:600;color:var(--color-text)">{{ $report->period_month }}</span>
        </div>
        
        <div class="no-print">
            <x-btn variant="primary" onclick="window.print()" title="Print / Save as PDF">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Export PDF
            </x-btn>
        </div>
    </div>

    <div class="card p-0 overflow-hidden" style="max-width:900px;margin:0 auto;box-shadow:0 12px 24px rgba(0,0,0,0.15);border:1px solid var(--color-border);">
        {{-- Report Header --}}
        <div style="background:var(--color-surface-2);padding:40px;border-bottom:1px solid var(--color-border);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-family:var(--font-mono);font-size:0.875rem;color:var(--color-primary);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;">
                        Security Quality Assurance Report
                    </div>
                    <h1 style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;line-height:1.2;margin:0;color:var(--color-text);letter-spacing:-0.02em;">
                        {{ $report->project->domain }}
                    </h1>
                    <div style="margin-top:12px;color:var(--color-muted);font-size:1.1rem;">
                        Period: <strong>{{ \Carbon\Carbon::parse($report->period_month)->format('F Y') }}</strong>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--color-text);margin-bottom:4px;">
                        Vakt<span style="color:var(--color-primary)">.</span>
                    </div>
                    <div style="color:var(--color-muted);font-size:0.875rem;margin-bottom:12px;">Generated {{ $report->created_at->format('M d, Y') }}</div>
                    <x-badge :type="$report->status === 'sent' ? 'success' : 'warning'">{{ strtoupper($report->status) }}</x-badge>
                </div>
            </div>
        </div>

        <div style="padding:40px;">
            {{-- Key Metrics Grid --}}
            <div class="grid grid-3 gap-6 mb-8">
                {{-- Score --}}
                <div class="card p-6" style="background:var(--color-surface);border:1px solid var(--color-border);box-shadow:none;text-align:center;">
                    <div style="color:var(--color-muted);font-size:0.875rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px;font-weight:600;">Overall Security Score</div>
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:100px;height:100px;border-radius:50%;border:4px solid var(--color-{{ $report->security_score >= 80 ? 'primary' : ($report->security_score >= 60 ? 'warning' : 'danger') }});font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:var(--color-text);">
                        {{ $report->security_score }}
                    </div>
                </div>

                {{-- Incidents --}}
                <div class="card p-6" style="background:var(--color-surface);border:1px solid var(--color-border);box-shadow:none;display:flex;flex-direction:column;justify-content:center;">
                    <div style="color:var(--color-muted);font-size:0.875rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px;font-weight:600;">Incident Resolution</div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <span style="color:var(--color-text-dim);">Total Detected</span>
                        <span style="font-size:1.25rem;font-weight:700;color:var(--color-text);">{{ $report->incidents_summary['total'] ?? 0 }}</span>
                    </div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <span style="color:var(--color-text-dim);">Critical (P1)</span>
                        <span style="font-size:1.25rem;font-weight:700;color:var(--color-danger);">{{ $report->incidents_summary['p1'] ?? 0 }}</span>
                    </div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px dashed var(--color-border);">
                        <span style="color:var(--color-text-dim);">Successfully Resolved</span>
                        <span style="font-size:1.25rem;font-weight:700;color:var(--color-success);">{{ $report->incidents_summary['resolved'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- Monitoring --}}
                <div class="card p-6" style="background:var(--color-surface);border:1px solid var(--color-border);box-shadow:none;display:flex;flex-direction:column;justify-content:center;">
                    <div style="color:var(--color-muted);font-size:0.875rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px;font-weight:600;">Monitoring Activity</div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <span style="color:var(--color-text-dim);">Automated Checks</span>
                        <span style="font-size:1.25rem;font-weight:700;color:var(--color-text);">{{ number_format($report->monitoring_summary['checks_run'] ?? 0) }}</span>
                    </div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px dashed var(--color-border);">
                        <span style="color:var(--color-text-dim);">Anomalies Found</span>
                        <span style="font-size:1.25rem;font-weight:700;color:{{ ($report->monitoring_summary['errors_found'] ?? 0) > 0 ? 'var(--color-warning)' : 'var(--color-success)' }};">{{ $report->monitoring_summary['errors_found'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Summary Section --}}
            <div style="background:var(--color-surface-2);border-radius:12px;padding:24px;border:1px solid var(--color-border);">
                <h3 style="margin-top:0;margin-bottom:16px;font-size:1.1rem;color:var(--color-text);">Executive Summary</h3>
                <p style="color:var(--color-muted);line-height:1.7;margin:0;">
                    During the month of {{ \Carbon\Carbon::parse($report->period_month)->format('F') }}, Vakt monitored the infrastructure and application layers of <strong>{{ $report->project->domain }}</strong>. 
                    A total of {{ number_format($report->monitoring_summary['checks_run'] ?? 0) }} automated integrity and uptime checks were executed.
                    @if(($report->incidents_summary['total'] ?? 0) == 0)
                        Zero security incidents were detected, indicating a highly stable and secure posture for this period.
                    @else
                        Our systems detected and logged {{ $report->incidents_summary['total'] ?? 0 }} incidents, of which {{ $report->incidents_summary['resolved'] ?? 0 }} have been fully resolved by our engineering team. 
                        Overall security posture remains {{ $report->security_score >= 80 ? 'strong' : 'under active improvement' }}.
                    @endif
                </p>
            </div>
            
            {{-- Footer --}}
            <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--color-border);text-align:center;color:var(--color-muted);font-size:0.875rem;">
                <p style="margin:0;">This report is strictly confidential and generated exclusively for {{ $report->project->domain }}.</p>
                <p style="margin:4px 0 0 0;">&copy; {{ date('Y') }} Vakt Security Operations. All rights reserved.</p>
            </div>
        </div>

        @if(request()->has('download'))
            <script>
                window.onload = function() {
                    setTimeout(() => window.print(), 500);
                }
            </script>
        @endif
        
        <style>
            @media print {
                body * { visibility: hidden; }
                .app-layout { display: block !important; }
                .sidebar, .topbar, .no-print, .breadcrumbs { display: none !important; }
                .main-content { margin: 0 !important; width: 100% !important; }
                .card { visibility: visible; position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
                .card * { visibility: visible; }
                /* Force background colors for print */
                * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            }
        </style>
    </div>
</x-app-layout>
