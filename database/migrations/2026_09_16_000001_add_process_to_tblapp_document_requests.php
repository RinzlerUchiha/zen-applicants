<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — Milestone 3.
|
| Ties a document request to the completion process it belongs to, so the
| deadline and the attempt counter are shared across every request in the run.
|
| Deliberately nullable: requests made before Milestone 3, and requests HR makes
| with no process running, stay exactly as they were.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_document_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('process_id')->nullable()->after('application_id');
            $table->index(['process_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tblapp_document_requests', function (Blueprint $table) {
            $table->dropIndex(['process_id', 'status']);
            $table->dropColumn('process_id');
        });
    }
};
