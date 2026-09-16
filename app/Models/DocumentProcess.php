<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The run HR has started to collect the applicant's outstanding documents
 * (tblapp_document_processes).
 *
 * HR owns this: it is started, configured and closed in zen-admin. The only
 * thing the applicant does to it is withdraw, which ends it.
 */
class DocumentProcess extends Model
{
    protected $table = 'tblapp_document_processes';
    protected $guarded = ['id'];

    public const ACTIVE = 'active';
    public const COMPLETE = 'complete';
    public const NON_RESPONSIVE = 'non_responsive';
    public const REQUIREMENTS_NOT_MET = 'requirements_not_met';
    public const WITHDRAWN = 'withdrawn';

    protected $casts = [
        'deadline_at' => 'datetime',
        'started_at' => 'datetime',
        'outcome_at' => 'datetime',
        'deadline_days' => 'integer',
        'max_attempts' => 'integer',
        'attempts_used' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::ACTIVE);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function getStatusLabelAttribute(): string
    {
        return config('documents.completion.statuses.' . $this->status, $this->status);
    }

    public function getAttemptsLeftAttribute(): int
    {
        return max(0, $this->max_attempts - $this->attempts_used);
    }

    /** Whole days left, floored at zero. Today counts as a day in hand. */
    public function getDaysLeftAttribute(): int
    {
        return max(0, (int) ceil(now()->diffInDays($this->deadline_at, false)));
    }
}
