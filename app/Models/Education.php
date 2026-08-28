<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'tblapp_education';
    protected $primaryKey = 'educ_id';
    public $timestamps = false;

    protected $guarded = ['educ_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
