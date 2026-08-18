<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Incident extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'ai_summary', 'ai_diagnosis', 'severity', 'status', 'source',
        'detected_at', 'responded_at', 'resolved_at', 'closed_at',
        'assigned_to', 'affected_component', 'impact_description',
        'root_cause', 'resolution_notes', 'prevention_notes',
        'estimated_cost_impact', 'related_log_entry_ids',
    ];

    protected $casts = [
        'detected_at'          => 'datetime',
        'responded_at'         => 'datetime',
        'resolved_at'          => 'datetime',
        'closed_at'            => 'datetime',
        'related_log_entry_ids' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(IncidentTimeline::class)->orderBy('performed_at');
    }

    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'p1' => 'P1 Critical',
            'p2' => 'P2 High',
            'p3' => 'P3 Medium',
            'p4' => 'P4 Low',
            default => strtoupper($this->severity),
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'p1' => 'danger',
            'p2' => 'warning',
            'p3' => 'info',
            'p4' => 'success',
            default => 'muted',
        };
    }

    // SLA deadlines in minutes
    public function getSlaRespondMinutesAttribute(): int
    {
        return match($this->severity) {
            'p1' => 15,
            'p2' => 60,
            'p3' => 240,
            'p4' => 1440,
            default => 60,
        };
    }

    public function getSlaResolveMinutesAttribute(): int
    {
        return match($this->severity) {
            'p1' => 240,
            'p2' => 1440,
            'p3' => 4320,
            'p4' => 10080,
            default => 1440,
        };
    }

    public function getSlaRespondBreachedAttribute(): bool
    {
        if ($this->responded_at) return false;
        return $this->detected_at && $this->detected_at->addMinutes($this->sla_respond_minutes)->isPast();
    }

    public function getSlaResolveBreachedAttribute(): bool
    {
        if (in_array($this->status, ['resolved', 'closed'])) return false;
        return $this->detected_at && $this->detected_at->addMinutes($this->sla_resolve_minutes)->isPast();
    }
}
