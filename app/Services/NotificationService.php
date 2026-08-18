<?php

namespace App\Services;

use App\Models\Incident;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyIncidentCreated(Incident $incident): void
    {
        $project = $incident->project;

        if ($project->slack_webhook_url) {
            $this->notifySlack($project->slack_webhook_url, $incident);
        }

        if ($project->discord_webhook_url) {
            $this->notifyDiscord($project->discord_webhook_url, $incident);
        }
    }

    private function notifySlack(string $webhookUrl, Incident $incident): void
    {
        $color = match($incident->severity) {
            'critical' => '#dc2626',
            'high'     => '#f97316',
            'medium'   => '#eab308',
            default    => '#3b82f6',
        };

        $payload = [
            'attachments' => [
                [
                    'fallback' => "New Incident: {$incident->title} on {$incident->project->domain}",
                    'color'    => $color,
                    'pretext'  => "🚨 *New Security Incident Detected*",
                    'title'    => $incident->title,
                    'title_link' => route('incidents.show', $incident),
                    'text'     => "An incident was detected on project *{$incident->project->domain}*.",
                    'fields'   => [
                        [
                            'title' => 'Severity',
                            'value' => strtoupper($incident->severity),
                            'short' => true,
                        ],
                        [
                            'title' => 'Status',
                            'value' => 'Open',
                            'short' => true,
                        ],
                        [
                            'title' => 'AI Summary',
                            'value' => $incident->ai_summary ?: 'Analysis pending...',
                            'short' => false,
                        ]
                    ],
                    'footer' => 'Vakt SOC Platform',
                    'ts'     => now()->timestamp
                ]
            ]
        ];

        try {
            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error("Failed to send Slack notification for incident {$incident->id}: " . $e->getMessage());
        }
    }

    private function notifyDiscord(string $webhookUrl, Incident $incident): void
    {
        $color = match($incident->severity) {
            'critical' => 14427942, // #dc2626
            'high'     => 16348182, // #f97316
            'medium'   => 15381256, // #eab308
            default    => 3900150,  // #3b82f6
        };

        $payload = [
            'embeds' => [
                [
                    'title'       => "🚨 New Incident: " . $incident->title,
                    'description' => "An incident was detected on **{$incident->project->domain}**.\n\n" . ($incident->ai_summary ?: ''),
                    'url'         => route('incidents.show', $incident),
                    'color'       => $color,
                    'fields'      => [
                        [
                            'name'   => 'Severity',
                            'value'  => strtoupper($incident->severity),
                            'inline' => true,
                        ],
                        [
                            'name'   => 'Status',
                            'value'  => 'Open',
                            'inline' => true,
                        ]
                    ],
                    'footer' => [
                        'text' => 'Vakt SOC Platform'
                    ],
                    'timestamp' => now()->toIso8601String(),
                ]
            ]
        ];

        try {
            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error("Failed to send Discord notification for incident {$incident->id}: " . $e->getMessage());
        }
    }

    public function notifyDailyReport(Project $project, array $stats): void
    {
        if ($project->slack_webhook_url) {
            $this->sendDailySlack($project->slack_webhook_url, $project, $stats);
        }

        if ($project->discord_webhook_url) {
            $this->sendDailyDiscord($project->discord_webhook_url, $project, $stats);
        }
    }

    private function sendDailySlack(string $webhookUrl, Project $project, array $stats): void
    {
        $payload = [
            'attachments' => [
                [
                    'fallback' => "Daily SOC Report for {$project->domain}",
                    'color'    => '#10b981', // green
                    'pretext'  => "📊 *Daily Security Briefing*",
                    'title'    => "Status Report: {$project->domain}",
                    'title_link' => route('projects.show', $project),
                    'text'     => "All checks completed for the last 24 hours.",
                    'fields'   => [
                        [
                            'title' => 'Uptime',
                            'value' => "{$stats['uptime_percentage']}%",
                            'short' => true,
                        ],
                        [
                            'title' => 'Open Incidents',
                            'value' => (string) $stats['open_incidents'],
                            'short' => true,
                        ],
                        [
                            'title' => 'Backup Status',
                            'value' => $stats['backup_healthy'] ? '✅ Verified' : '❌ Missing/Failed',
                            'short' => false,
                        ]
                    ],
                    'footer' => 'Vakt SOC Platform',
                    'ts'     => now()->timestamp
                ]
            ]
        ];

        try {
            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error("Failed to send Daily Slack notification: " . $e->getMessage());
        }
    }

    private function sendDailyDiscord(string $webhookUrl, Project $project, array $stats): void
    {
        $payload = [
            'embeds' => [
                [
                    'title'       => "📊 Daily Security Briefing: " . $project->domain,
                    'description' => "All systematic checks completed for the last 24 hours.",
                    'url'         => route('projects.show', $project),
                    'color'       => 1095945, // #10b981
                    'fields'      => [
                        [
                            'name'   => 'Uptime',
                            'value'  => "{$stats['uptime_percentage']}%",
                            'inline' => true,
                        ],
                        [
                            'name'   => 'Open Incidents',
                            'value'  => (string) $stats['open_incidents'],
                            'inline' => true,
                        ],
                        [
                            'name'   => 'Backup Status',
                            'value'  => $stats['backup_healthy'] ? '✅ Verified' : '❌ Missing/Failed',
                            'inline' => false,
                        ]
                    ],
                    'footer' => [
                        'text' => 'Vakt SOC Platform'
                    ],
                    'timestamp' => now()->toIso8601String(),
                ]
            ]
        ];

        try {
            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error("Failed to send Daily Discord notification: " . $e->getMessage());
        }
    }
}
