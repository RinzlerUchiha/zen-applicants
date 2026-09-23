<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An applicant asking HR for a new assessment access code — after their
     * access ended, or to resume a paused assessment. At most one OPEN request
     * per applicant (asking again refreshes it). HR issuing a code in zen-admin
     * resolves it. Nothing is sent anywhere: HR sees open requests in
     * zen-admin (Applicant Intake and the applicant's Assessment access tab).
     *
     * datetime, not timestamp: MariaDB would otherwise auto-update the first
     * timestamp column on every write.
     */
    public function up(): void
    {
        Schema::create('tblapp_assessment_access_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('app_id');
            // expired | paused | other_browser — what the applicant was facing.
            $table->string('reason', 20);
            // The assessment they were on, when there was one.
            $table->string('assessment', 40)->nullable();
            $table->dateTime('requested_at');
            $table->unsignedSmallInteger('times_asked')->default(1);
            $table->dateTime('resolved_at')->nullable();
            $table->string('resolved_by', 20)->nullable();

            $table->index(['app_id', 'resolved_at']);
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_assessment_access_requests');
    }
};
