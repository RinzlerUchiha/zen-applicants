<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Makes tblapp_employment.empl_to nullable.
 *
 * Required for the agreed "I currently work here" rule to function at all.
 * The column is NOT NULL with no default, so a job the applicant still holds
 * had no representable end date — the only options were a sentinel like
 * '0000-00-00' (rejected outright under NO_ZERO_DATE, which this server sets)
 * or a fake date, which would corrupt the eventual 201 employment history.
 *
 * This is deliberately narrow: it does NOT touch empl_salary or datastat,
 * which remain parked as a separate strict-mode fix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblapp_employment', function (Blueprint $table) {
            $table->date('empl_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Rows written while "currently employed" hold NULL and would break a
        // straight revert, so give them a value first. They are identifiable
        // by the flag, and the date is a placeholder, not a claim.
        \Illuminate\Support\Facades\DB::table('tblapp_employment')
            ->whereNull('empl_to')
            ->update(['empl_to' => '1970-01-01']);

        Schema::table('tblapp_employment', function (Blueprint $table) {
            $table->date('empl_to')->nullable(false)->change();
        });
    }
};
