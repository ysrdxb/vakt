<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogEntry extends Model
{
    protected $fillable = [
        'project_id', 'level', 'message', 'context', 'detected_patterns',
        'ip_address', 'user_agent', 'url', 'occurred_at',
        'is_reviewed', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'context'           => 'array',
        'detected_patterns' => 'array',
        'occurred_at'       => 'datetime',
        'reviewed_at'       => 'datetime',
        'is_reviewed'       => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            'critical' => 'danger',
            'error'    => 'danger',
            'warning'  => 'warning',
            'info'     => 'primary',
            default    => 'muted',
        };
    }
}
