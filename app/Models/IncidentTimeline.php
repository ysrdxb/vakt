<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentTimeline extends Model
{
    protected $table = 'incident_timeline';

    protected $fillable = [
        'incident_id', 'action', 'description', 'performed_by', 'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
