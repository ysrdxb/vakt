<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentReport extends Model
{
    protected $fillable = [
        'project_id', 'payload', 'agent_ip', 'signature_valid', 'received_at',
    ];

    protected $casts = [
        'payload'        => 'array',
        'signature_valid' => 'boolean',
        'received_at'    => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
