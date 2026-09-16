<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HR asking the applicant for a document. Created and closed in zen-admin;
 * the applicant portal only reads these and marks them submitted on upload.
 */
class DocumentRequest extends Model
{
    protected $table = 'tblapp_document_requests';
    protected $guarded = ['id'];

    /** Statuses in which HR is still waiting on something. */
    public const ACTIVE = ['open', 'submitted'];

    protected $casts = [
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE);
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return config('documents.types.' . $this->doc_type, $this->doc_type);
    }
}
