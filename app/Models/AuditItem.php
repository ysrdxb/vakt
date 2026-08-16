<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditItem extends Model
{
    protected $fillable = [
        'project_id', 'category', 'item_name', 'description',
        'status', 'severity', 'notes', 'remediation_steps', 'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
