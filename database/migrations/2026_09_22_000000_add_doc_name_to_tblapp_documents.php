<?php

use App\Services\DocumentName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * doc_name: the document's standard name ("DELA CRUZ, JUAN, P - CV.pdf"),
     * shown to HR and the applicant and used when the file is downloaded. The
     * stored file keeps its random name (doc_file); the applicant's own file
     * name stays in doc_original_name for reference.
     *
     * Existing documents are named from the applicant's current profile name.
     * Only empty values are filled — nothing else is changed.
     */
    public function up(): void
    {
        Schema::table('tblapp_documents', function (Blueprint $table) {
            $table->string('doc_name', 255)->nullable()->after('doc_original_name');
        });

        DB::table('tblapp_documents as d')
            ->join('tblapp_persinfo as p', 'p.app_id', '=', 'd.app_id')
            ->whereNull('d.doc_name')
            ->select('d.id', 'd.doc_type', 'd.doc_file', 'p.app_lname', 'p.app_fname', 'p.app_mname', 'p.app_suffix')
            ->orderBy('d.id')
            ->get()
            ->each(fn ($row) => DB::table('tblapp_documents')->where('id', $row->id)->update([
                'doc_name' => DocumentName::make(
                    $row->app_lname, $row->app_fname, $row->app_mname, $row->app_suffix,
                    $row->doc_type, pathinfo($row->doc_file, PATHINFO_EXTENSION)
                ),
            ]));
    }

    public function down(): void
    {
        Schema::table('tblapp_documents', function (Blueprint $table) {
            $table->dropColumn('doc_name');
        });
    }
};
