<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — assessment gating.
|
| The assessments are "provided by HR after your initial interview". HR issues
| a one-time access code from the applicant's profile in zen-admin and gives it
| to the applicant; entering it opens the assessments in that browser session
| for a limited time.
|
|   code_hash        the code, hashed — HR sees it once, when it is issued
|   expires_at       the code must be used before this
|   failed_attempts  wrong entries against this code; too many revoke it
|   redeemed_at      when the applicant entered it (single use)
|   session_hash     the browser session it was entered in — the unlock is
|                    good there only
|   unlocked_until   starting (or resuming) an assessment is allowed until then
|   revoked_at       replaced by a newer code, or too many wrong entries
|
| zen-admin writes the row (over its "applicant" connection); this app reads it
| and records the redemption.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblapp_assessment_access', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('app_id');
            $table->string('code_hash', 255);
            $table->string('issued_by', 20)->nullable();
            $table->dateTime('issued_at');
            $table->dateTime('expires_at');
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->dateTime('redeemed_at')->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->dateTime('unlocked_until')->nullable();
            $table->dateTime('revoked_at')->nullable();

            $table->index(['app_id', 'redeemed_at'], 'tblapp_assessment_access_app_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_assessment_access');
    }
};
