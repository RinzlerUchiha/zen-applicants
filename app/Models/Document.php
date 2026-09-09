<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'tblapp_documents';
    protected $guarded = ['id'];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'doc_size' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    /**
     * Human-readable name for this document's type. Falls back to the raw
     * stored value if the type was removed from config since upload, so an
     * older row still renders something meaningful rather than blank.
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->doc_type === config('documents.other_type')) {
            return $this->doc_label ?: 'Other';
        }

        return config('documents.types.' . $this->doc_type, $this->doc_type);
    }

    /** Path on the storage disk. The only place this is assembled. */
    public function getStoragePathAttribute(): string
    {
        return config('documents.path') . '/' . $this->app_id . '/' . $this->doc_file;
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->doc_mime === 'application/pdf';
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
