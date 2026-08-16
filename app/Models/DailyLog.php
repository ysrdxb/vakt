<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    protected $fillable = [
        'project_id', 'operator_id', 'checked_at', 'status',
        'summary', 'findings', 'actions_taken', 'next_steps', 'auto_generated',
    ];

    protected $casts = [
        'checked_at'     => 'datetime',
        'findings'       => 'array',
        'auto_generated' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
