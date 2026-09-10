<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two declaration flags the agreed completeness rules depend on.
 *
 * Both exist so the applicant can state that something does not apply to
 * them, rather than the system silently skipping a required field. Without
 * these, a first-time jobseeker and anyone still employed can never reach
 * 100% — the rule would be strict in a way that punishes honest answers.
 *
 * Nullable / defaulted so existing rows are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_persinfo', function (Blueprint $table) {
            if (!Schema::hasColumn('tblapp_persinfo', 'app_no_work_experience')) {
                // "This is my first job" — satisfies the Employment section
                // with zero rows.
                $table->boolean('app_no_work_experience')->default(false);
            }
        });

        Schema::table('tblapp_employment', function (Blueprint $table) {
            if (!Schema::hasColumn('tblapp_employment', 'empl_is_current')) {
                // "I currently work here" — exempts empl_to and empl_reason,
                // neither of which has an answer for a job still held.
                $table->boolean('empl_is_current')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tblapp_persinfo', function (Blueprint $table) {
            if (Schema::hasColumn('tblapp_persinfo', 'app_no_work_experience')) {
                $table->dropColumn('app_no_work_experience');
            }
        });

        Schema::table('tblapp_employment', function (Blueprint $table) {
            if (Schema::hasColumn('tblapp_employment', 'empl_is_current')) {
                $table->dropColumn('empl_is_current');
            }
        });
    }
};
