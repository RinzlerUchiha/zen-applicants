<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2 — posting channels.
|
| Where this application came from: the channel key the applicant arrived
| through, tagged on the Careers link HR posted (?src=linkedin), or recorded
| by HR at intake for a walk-in, referral or job fair.
|
| A column, not a table. One application has exactly one source, so there is
| nothing to join — and keeping it on the row means counting applications by
| channel needs no join either.
|
| NULL means unknown, which is what every application made before today is.
| It is not the same as `direct`: direct means we know they came to the
| Careers page untagged, NULL means we were not counting yet. Backfilling
| the existing rows to `direct` would invent a fact, so they stay NULL.
|
| The running totals live on tbl_job_posting_channel.applications; this is
| the per-application record those totals are built from, and the one that
| survives if a posting's channel row is ever removed.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_applications', function (Blueprint $table) {
            // Matches tbl_job_posting_channel.channel_key, by value. No FK:
            // that table is in another database, and HireFlow links across
            // schemas by plain value everywhere else too.
            $table->string('source_channel', 40)->nullable()->after('applied_at');
        });

        Schema::table('tblapp_applications', function (Blueprint $table) {
            // "how many applications came through each channel", which is
            // the question this column exists to answer.
            $table->index(['source_channel', 'applied_at'], 'tblapp_applications_source_channel_index');
        });
    }

    public function down(): void
    {
        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->dropIndex('tblapp_applications_source_channel_index');
        });

        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->dropColumn('source_channel');
        });
    }
};
