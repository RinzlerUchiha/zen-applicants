<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One applicant's attempt at one assessment (tblapp_assessment_attempts).
 *
 * The result itself still lives in the assessment's own table; this row only
 * governs WHEN a result may be written. The rules live in
 * App\Services\AssessmentAttempts — this model is the record.
 */
class AssessmentAttempt extends Model
{
    protected $table = 'tblapp_assessment_attempts';

    protected $guarded = ['id'];

    public const ACTIVE = 'active';
    public const INTERRUPTED = 'interrupted';
    public const SUBMITTED = 'submitted';
    public const TIMED_OUT = 'timed_out';

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'interrupted_at' => 'datetime',
        'ended_at' => 'datetime',
        'draft_saved_at' => 'datetime',
        'draft' => 'array',
        'result_saved' => 'boolean',
        'duration_seconds' => 'integer',
        'time_used_seconds' => 'integer',
        'seed' => 'integer',
    ];

    protected $hidden = ['draft', 'tab_token', 'session_hash', 'seed'];

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isInterrupted(): bool
    {
        return $this->status === self::INTERRUPTED;
    }

    /** Submitted or timed out: nothing more can be written. */
    public function isFinished(): bool
    {
        return in_array($this->status, [self::SUBMITTED, self::TIMED_OUT], true);
    }
}
