<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An HR-issued assessment access code (tblapp_assessment_access).
 *
 * zen-admin creates the row; this app records its redemption. The rules live in
 * App\Services\AssessmentGate.
 */
class AssessmentAccess extends Model
{
    protected $table = 'tblapp_assessment_access';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = ['code_hash', 'session_hash'];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'unlocked_until' => 'datetime',
        'revoked_at' => 'datetime',
        'failed_attempts' => 'integer',
    ];
}
