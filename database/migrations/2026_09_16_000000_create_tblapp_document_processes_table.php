<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — Milestone 3.
|
| One run at getting an applicant's outstanding documents in. The document
| requests of Milestone 2 sit underneath it; this row carries what they share:
| a single deadline and a single attempt counter for the whole run, not one of
| each per document.
|
| Lifecycle:
|   active                 HR is waiting on the applicant
|   complete               every request under it was accepted — the applicant
|                          moves on to the next screening stage
|   non_responsive         the deadline passed with requests still unresolved
|   requirements_not_met   the attempts ran out with requests still unresolved
|   withdrawn              the applicant pulled out
|
| The last four are terminal and are never changed again. They are also what
| puts the candidate in the Candidate Pool — the record is kept, not deleted.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblapp_document_processes', function (Blueprint $table) {
            $table->id();

            // Owned by the applicant, like the documents and the requests.
            $table->unsignedInteger('app_id');

            // The application HR was working on, when there was one. Null keeps
            // the Milestone 2 behaviour of acting on an applicant directly.
            $table->unsignedBigInteger('application_id')->nullable();

            $table->string('status', 30)->default('active');

            // What HR chose for THIS run. Defaults come from config, but they
            // are copied here so changing the default later cannot move a
            // deadline an applicant has already been given.
            $table->unsignedSmallInteger('deadline_days');

            // datetime, not timestamp: MariaDB turns the first NOT NULL
            // TIMESTAMP column without an explicit default into
            // "DEFAULT current_timestamp() ON UPDATE current_timestamp()", which
            // would quietly reset the applicant's deadline on every write to
            // the row — including the attempt counter going up.
            $table->dateTime('deadline_at');
            $table->unsignedSmallInteger('max_attempts');
            $table->unsignedSmallInteger('attempts_used')->default(0);

            $table->string('started_by', 20);
            $table->timestamp('started_at')->useCurrent();

            // How the run ended. closed_by is HR's Emp_No, and is null when the
            // applicant withdrew or the deadline sweep closed it.
            $table->dateTime('outcome_at')->nullable();
            $table->string('closed_by', 20)->nullable();
            $table->text('closed_note')->nullable();

            $table->timestamps();

            // One active run per applicant is enforced in the service, under the
            // same applicant-row lock the rest of the document flow takes.
            $table->index(['app_id', 'status']);

            // The deadline sweep's query: active runs that are now overdue.
            $table->index(['status', 'deadline_at']);

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_document_processes');
    }
};
