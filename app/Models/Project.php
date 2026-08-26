<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'domain', 'description', 'server_type', 'server_path',
        'agent_secret', 'agent_url', 'firewall_whitelist_confirmed', 'agent_ip_whitelist', 'ftp_host', 'ftp_user', 'ftp_password',
        'stack', 'php_version', 'laravel_version', 'log_path', 'php_error_log_path',
        'active', 'monitoring_interval_minutes', 'alert_email', 'status', 'last_checked_at',
        'modules', 'incident_rules', 'slack_webhook_url', 'discord_webhook_url'
    ];

    protected $casts = [
        'active' => 'boolean',
        'firewall_whitelist_confirmed' => 'boolean',
        'last_checked_at' => 'datetime',
        'ftp_password' => 'encrypted',
        'agent_secret' => 'encrypted',
        'modules' => 'array',
        'incident_rules' => 'array',
    ];

    public function logEntries(): HasMany
    {
        return $this->hasMany(LogEntry::class);
    }

    public function monitoringChecks(): HasMany
    {
        return $this->hasMany(MonitoringCheck::class);
    }

    public function uptimeLogs(): HasMany
    {
        return $this->hasMany(UptimeLog::class);
    }

    public function fileSnapshots(): HasMany
    {
        return $this->hasMany(FileSnapshot::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function auditItems(): HasMany
    {
        return $this->hasMany(AuditItem::class);
    }

    public function vulnerabilities(): HasMany
    {
        return $this->hasMany(Vulnerability::class);
    }

    public function improvements(): HasMany
    {
        return $this->hasMany(Improvement::class);
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    public function sqaReports(): HasMany
    {
        return $this->hasMany(SqaReport::class);
    }

    public function agentReports(): HasMany
    {
        return $this->hasMany(AgentReport::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'healthy'  => 'success',
            'warning'  => 'warning',
            'critical' => 'danger',
            default    => 'muted',
        };
    }

    public function getOpenIncidentsCountAttribute(): int
    {
        return $this->incidents()->whereNotIn('status', ['resolved', 'closed'])->count();
    }

    public function getSecurityScoreAttribute(): int
    {
        $items = $this->auditItems;
        $incidents = $this->incidents->filter(fn($inc) => !in_array($inc->status, ['resolved', 'closed']));

        $score = 100;
        foreach ($incidents as $inc) {
            $score -= match($inc->severity) {
                'p1' => 25,
                'p2' => 15,
                'p3' => 10,
                default => 5,
            };
        }

        foreach ($items as $item) {
            if ($item->status === 'fail') {
                $score -= match($item->severity) {
                    'critical' => 20,
                    'high'     => 15,
                    'medium'   => 10,
                    'low'      => 5,
                    default    => 5,
                };
            }
        }

        return max(0, min(100, $score));
    }
}
