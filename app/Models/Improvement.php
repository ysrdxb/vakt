<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Improvement extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'category', 'priority', 'effort', 'status',
        'proposed_by', 'approved_by', 'approved_at', 'started_at', 'completed_at',
        'research_notes', 'implementation_notes', 'decline_reason', 'estimated_hours', 'actual_hours',
    ];

    protected $casts = [
        'approved_at'  => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
