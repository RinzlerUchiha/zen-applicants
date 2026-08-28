<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eligibility extends Model
{
    protected $table = 'tblapp_eligibility';
    protected $primaryKey = 'el_id';
    public $timestamps = false;

    protected $guarded = ['el_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
