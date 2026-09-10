<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records that the applicant was shown and acknowledged the privacy notice.
 *
 * RA 10173 requires the data subject to be informed before their personal
 * information is processed. An acknowledgement that is collected but never
 * stored is evidence of nothing, so the timestamp is kept alongside the
 * record it relates to.
 *
 * Nullable: existing applicants predate this flow and must not be assumed to
 * have acknowledged anything.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_persinfo', function (Blueprint $table) {
            if (!Schema::hasColumn('tblapp_persinfo', 'app_privacy_ack_at')) {
                $table->timestamp('app_privacy_ack_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tblapp_persinfo', function (Blueprint $table) {
            if (Schema::hasColumn('tblapp_persinfo', 'app_privacy_ack_at')) {
                $table->dropColumn('app_privacy_ack_at');
            }
        });
    }
};
