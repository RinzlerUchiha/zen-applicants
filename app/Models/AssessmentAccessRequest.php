<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An applicant's request to HR for a new assessment access code
 * (tblapp_assessment_access_requests). The rules are in
 * App\Services\AssessmentGate; HR resolves it by issuing a code in zen-admin.
 */
class AssessmentAccessRequest extends Model
{
    protected $table = 'tblapp_assessment_access_requests';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
        'times_asked' => 'integer',
    ];

    public function scopeOpen($query)
    {
        return $query->whereNull('resolved_at');
    }
}
