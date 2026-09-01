<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblapp_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('app_id');
            $table->unsignedInteger('request_position_id');
            $table->unsignedInteger('job_posting_id')->nullable();
            $table->string('status', 30)->default('Applied');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->index('app_id');
            $table->index('request_position_id');
            $table->index('job_posting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblapp_applications');
    }
};