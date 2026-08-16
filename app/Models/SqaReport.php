<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SqaReport extends Model
{
    protected $fillable = [
        'project_id', 'title', 'period_month', 'executive_summary',
        'security_score', 'prev_security_score', 'incidents_summary',
        'monitoring_summary', 'vulnerabilities_summary', 'improvements_summary',
        'audit_summary', 'recommended_actions', 'status', 'sent_at', 'pdf_path',
    ];

    protected $casts = [
        'sent_at'                 => 'datetime',
        'incidents_summary'       => 'array',
        'monitoring_summary'      => 'array',
        'vulnerabilities_summary' => 'array',
        'improvements_summary'    => 'array',
        'audit_summary'           => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
