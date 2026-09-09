<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblapp_documents', function (Blueprint $table) {
            $table->id();

            // Documents belong to the applicant (tblapp_persinfo.app_id), not
            // to a single application. A PSA birth certificate is a property
            // of the person; keying it per job posting would force a re-upload
            // for every position applied to.
            $table->unsignedInteger('app_id');

            $table->string('doc_type', 50);

            // Only used by the 'other' type, where the applicant names the
            // document themselves.
            $table->string('doc_label', 150)->nullable();

            // Stored filename only — random, never derived from user input.
            // The folder is config('documents.path').'/'.app_id.
            $table->string('doc_file', 255);

            // What the applicant called it. Display only; never used as a path.
            $table->string('doc_original_name', 255);

            $table->string('doc_mime', 100);
            $table->unsignedInteger('doc_size');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index('app_id');

            // Intentionally no unique index on (app_id, doc_type): a TOR or a
            // set of government IDs is routinely more than one file.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_documents');
    }
};
