<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'tblapp_documents';
    protected $guarded = ['id'];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'doc_size' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    /**
     * Human-readable name for this document's type. Falls back to the stored
     * value if the type was removed from config since upload, so an older row
     * still renders something meaningful rather than blank.
     */
    public function getTypeLabelAttribute(): string
    {
        return config('documents.types.' . $this->doc_type, $this->doc_label ?: $this->doc_type);
    }

    /**
     * Key on the documents disk. doc_file holds the full key; rows written
     * before Milestone 2 held only the filename, so those are resolved against
     * the folder they were written to.
     */
    public function getStoragePathAttribute(): string
    {
        if (str_contains($this->doc_file, '/')) {
            return $this->doc_file;
        }

        return config('documents.path') . '/' . $this->app_id . '/' . $this->doc_file;
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->doc_mime === 'application/pdf';
    }

    public function getExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->doc_file, PATHINFO_EXTENSION));
    }

    /** What the applicant is told about HR's check. */
    public function getReviewLabelAttribute(): string
    {
        return config('documents.review_statuses.' . $this->review_status, '');
    }

    /** The applicant-facing explanation of why a replacement is needed. */
    public function getReviewReasonTextAttribute(): ?string
    {
        if ($this->review_status !== 'rejected') {
            return null;
        }

        return config('documents.review_reasons.' . $this->review_reason);
    }

    /** Size rendered for the list, e.g. "842 KB" / "1.7 MB". */
    public function getSizeForHumansAttribute(): string
    {
        $bytes = (int) $this->doc_size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
