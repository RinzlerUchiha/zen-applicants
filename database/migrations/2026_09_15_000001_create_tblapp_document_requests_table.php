<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — Milestone 2.
|
| HR asking an applicant for a document: either one that was never sent, or a
| replacement for one HR could not accept. Written by zen-admin, read by the
| applicant portal.
|
| Lifecycle:  open → submitted → closed        (or cancelled at any point)
|   open       HR is waiting on the applicant
|   submitted  the applicant uploaded; HR has not checked it yet
|   closed     HR accepted what was sent
| A rejected upload moves the same request back to open.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblapp_document_requests', function (Blueprint $table) {
            $table->id();

            // Owned by the applicant, like the documents themselves.
            $table->unsignedInteger('app_id');

            // Optional context: the application HR was looking at. Null when HR
            // asks before the applicant has applied to anything.
            $table->unsignedBigInteger('application_id')->nullable();

            $table->string('doc_type', 50);

            // missing | replacement
            $table->string('kind', 20);

            // HR's message to the applicant.
            $table->text('note')->nullable();

            $table->string('status', 20)->default('open');

            $table->string('requested_by', 20);
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->string('closed_by', 20)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['app_id', 'doc_type', 'status']);
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_document_requests');
    }
};
