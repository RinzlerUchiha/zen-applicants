<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One applicant may hold at most one application per job posting.
     *
     * JobApplicationService::apply() already checks for an existing row, but
     * check-then-insert is not atomic: a double-submitted form, or the
     * register-then-apply path racing a manual apply, could insert twice.
     * The database is the only place that can settle that reliably.
     *
     * job_posting_id is nullable and MySQL permits repeated NULLs in a unique
     * index, so rows without a posting are unaffected by this constraint.
     *
     * Verified before adding: zero existing duplicate (app_id, job_posting_id)
     * pairs.
     */
    public function up(): void
    {
        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->unique(['app_id', 'job_posting_id'], 'tblapp_applications_app_job_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->dropUnique('tblapp_applications_app_job_unique');
        });
    }
};
