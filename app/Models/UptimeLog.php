<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UptimeLog extends Model
{
    protected $fillable = [
        'project_id',
        'status_code',
        'response_time_ms',
        'error_message',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
