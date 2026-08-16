<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringCheck extends Model
{
    protected $fillable = [
        'project_id', 'checked_at', 'status', 'log_lines_scanned',
        'errors_found', 'warnings_found', 'critical_patterns_found', 'duration_ms', 'notes',
    ];

    protected $casts = [
        'checked_at'              => 'datetime',
        'critical_patterns_found' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
