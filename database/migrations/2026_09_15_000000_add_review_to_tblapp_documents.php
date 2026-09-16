<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — Milestone 2.
|
| HR's check of the document currently on file. There is one state per
| document, not a history: when the applicant replaces a file the row is
| updated and these columns reset to pending, so a decision about the old file
| never applies to the new one.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_documents', function (Blueprint $table) {
            // pending | accepted | rejected
            $table->string('review_status', 20)->default('pending')->after('uploaded_at');

            // Key of config('documents.review_reasons'); only set when rejected.
            $table->string('review_reason', 40)->nullable()->after('review_status');

            // Written by HR for the applicant to read.
            $table->text('review_note')->nullable()->after('review_reason');

            // zen-admin tbl_user2.Emp_No of whoever made the decision.
            $table->string('reviewed_by', 20)->nullable()->after('review_note');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->index(['app_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::table('tblapp_documents', function (Blueprint $table) {
            $table->dropIndex(['app_id', 'doc_type']);
            $table->dropColumn(['review_status', 'review_reason', 'review_note', 'reviewed_by', 'reviewed_at']);
        });
    }
};
