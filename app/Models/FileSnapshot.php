<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileSnapshot extends Model
{
    protected $fillable = [
        'project_id', 'file_path', 'file_hash', 'file_size',
        'last_modified', 'status', 'flagged_patterns', 'first_seen_at', 'changed_at',
    ];

    protected $casts = [
        'last_modified'   => 'datetime',
        'first_seen_at'   => 'datetime',
        'changed_at'      => 'datetime',
        'flagged_patterns' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
