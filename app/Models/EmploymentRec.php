<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentRec extends Model
{
    protected $table = 'tblapp_employment';
    protected $primaryKey = 'empl_id';
    public $timestamps = false;

    protected $guarded = ['empl_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
