<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2.5 — assessment integrity.
|
| One row per applicant per assessment: the ATTEMPT, kept beside the eleven
| existing result tables rather than inside them. The result tables, their
| answer formats and HR's marking are unchanged — an attempt only decides
| whether, and until when, a result may be written.
|
|   status            active       started, clock running, can be resumed
|                     interrupted  the page went quiet for longer than the
|                                  grace period; the clock stopped where it
|                                  was, and HR's access code resumes it
|                     submitted    the applicant submitted in time — final
|                     timed_out    time ran out; whatever was autosaved was
|                                  submitted for them — final
|   duration_seconds  the limit, copied from config when the attempt started,
|                     so changing config never moves a running clock
|   time_used_seconds time actually spent on the page (the server's clock)
|   last_seen_at      the page's last autosave/heartbeat
|   draft             the autosaved answers, in the SAME payload the quiz
|                     submits — resume and time-up both use it
|   seed              fixes the question/choice order for this attempt, so a
|                     refresh shows the same order
|   tab_token         the one window allowed to answer; the newest one wins
|   session_hash      the browser session the attempt belongs to
|   result_saved      whether a row was written to the result table (a time-up
|                     with an incomplete questionnaire writes none)
|
| datetime, not timestamp: MariaDB can attach ON UPDATE current_timestamp() to a
| timestamp column and silently move it.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblapp_assessment_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('app_id');
            $table->string('assessment', 40);
            $table->string('status', 20);
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('time_used_seconds')->default(0);
            $table->dateTime('started_at');
            $table->dateTime('last_seen_at');
            $table->dateTime('interrupted_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->longText('draft')->nullable();
            $table->dateTime('draft_saved_at')->nullable();
            $table->unsignedInteger('seed');
            $table->string('tab_token', 64)->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->boolean('result_saved')->default(false);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            // One attempt per applicant per assessment. An interruption resumes
            // the same row; it never opens a second one.
            $table->unique(['app_id', 'assessment'], 'tblapp_assessment_attempts_app_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_assessment_attempts');
    }
};
