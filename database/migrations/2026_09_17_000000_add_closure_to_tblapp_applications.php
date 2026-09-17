<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — Milestone 3 (revised).
|
| An application can now end, and a person can apply to the same posting again
| later. Both need the application to record that it closed, and the one-row-
| per-posting rule to become one OPEN row per posting.
|
|   closed_at    when it ended. NULL means the application is open.
|   closed_by    HR's Emp_No. NULL when the applicant withdrew or the deadline
|                job closed it — the status says which.
|   closed_note  why, in HR's or the applicant's words.
|
| The status column says WHY it closed (Withdrawn, Not Selected, Non-Responsive);
| config/applications.php says what each status means.
|
| Duplicate protection moves from (app_id, job_posting_id) to
| (app_id, open_posting_id). open_posting_id is the posting while the
| application is open and NULL once it closes, and NULLs never collide in a
| unique index — so at most one open application per posting, with any number
| of closed ones kept as history. The database still enforces it, so the
| duplicate-entry handling in JobApplicationService keeps working unchanged.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_applications', function (Blueprint $table) {
            // datetime, not timestamp: MariaDB can attach ON UPDATE
            // current_timestamp() to a timestamp column and silently move it.
            $table->dateTime('closed_at')->nullable()->after('applied_at');
            $table->string('closed_by', 20)->nullable()->after('closed_at');
            $table->text('closed_note')->nullable()->after('closed_by');
        });

        // Separate statement: the generated column reads closed_at.
        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->unsignedInteger('open_posting_id')
                ->nullable()
                ->storedAs('CASE WHEN closed_at IS NULL THEN job_posting_id END')
                ->after('closed_note');
        });

        Schema::table('tblapp_applications', function (Blueprint $table) {
            // The new guard is in place before the old one is removed, so there
            // is no moment without duplicate protection.
            $table->unique(['app_id', 'open_posting_id'], 'tblapp_applications_app_open_posting_unique');
            $table->dropUnique('tblapp_applications_app_job_unique');

            // The re-application cooldown looks up earlier applications for
            // one posting by when they closed.
            $table->index(['app_id', 'job_posting_id', 'closed_at'], 'tblapp_applications_reapply_index');
        });
    }

    /*
    | Restoring (app_id, job_posting_id) as unique is only possible while no
    | applicant has applied to the same posting twice. Once repeat applications
    | exist this rollback fails on purpose rather than deleting history.
    */
    public function down(): void
    {
        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->dropIndex('tblapp_applications_reapply_index');
            $table->unique(['app_id', 'job_posting_id'], 'tblapp_applications_app_job_unique');
            $table->dropUnique('tblapp_applications_app_open_posting_unique');
        });

        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->dropColumn('open_posting_id');
        });

        Schema::table('tblapp_applications', function (Blueprint $table) {
            $table->dropColumn(['closed_at', 'closed_by', 'closed_note']);
        });
    }
};
