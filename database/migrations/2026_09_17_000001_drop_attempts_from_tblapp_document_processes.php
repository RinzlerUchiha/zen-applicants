<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — Milestone 3 (revised).
|
| 1. The attempt system is removed. A document process is driven by its
|    requirements and its deadline only: a rejection reopens the request and
|    costs nothing, and there is no attempt-based outcome.
|
| 2. A document process belongs to one application, and an application has at
|    most one active process. active_application_id is the application while
|    the process is active and NULL once it ends; NULLs never collide in a
|    unique index, so the database refuses a second active process for the same
|    application while keeping every finished one.
|
| application_id itself stays nullable. Two processes created before this
| change (#16 and #17) have none and are kept exactly as they are — only the
| two obsolete columns are removed from them. New processes must name an
| application; that is enforced where processes are started.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_document_processes', function (Blueprint $table) {
            $table->dropColumn(['max_attempts', 'attempts_used']);
        });

        Schema::table('tblapp_document_processes', function (Blueprint $table) {
            $table->unsignedBigInteger('active_application_id')
                ->nullable()
                ->storedAs("CASE WHEN status = 'active' THEN application_id END")
                ->after('application_id');

            $table->unique('active_application_id', 'tblapp_document_processes_active_application_unique');
        });
    }

    /*
    | The attempt columns come back with their old defaults (3 and 0). What any
    | process actually had cannot be recovered once dropped.
    */
    public function down(): void
    {
        Schema::table('tblapp_document_processes', function (Blueprint $table) {
            $table->dropUnique('tblapp_document_processes_active_application_unique');
        });

        Schema::table('tblapp_document_processes', function (Blueprint $table) {
            $table->dropColumn('active_application_id');
        });

        Schema::table('tblapp_document_processes', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_attempts')->default(3)->after('deadline_at');
            $table->unsignedSmallInteger('attempts_used')->default(0)->after('max_attempts');
        });
    }
};
