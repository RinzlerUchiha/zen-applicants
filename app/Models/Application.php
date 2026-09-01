<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Application extends Model
{
    protected $table = 'tblapp_applications';
    protected $guarded = [];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    /** Read-only pull of the linked job posting from zen-admin's portal_db. */
    public function jobPosting()
    {
        if (!$this->job_posting_id) {
            return null;
        }
        return DB::connection('zen')->table('tbl_job_posting')
            ->where('id', $this->job_posting_id)
            ->first();
    }

    /** Read-only pull of the linked manpower request position + parent MR No. from HireFlow. */
    public function requestPosition()
    {
        return DB::connection('hrd2')->table('tbl_manpower_request_position as p')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'p.request_id')
            ->select('p.*', 'r.mr_no')
            ->where('p.id', $this->request_position_id)
            ->first();
    }
}