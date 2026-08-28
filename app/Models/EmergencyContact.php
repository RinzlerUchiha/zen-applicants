<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    protected $table = 'tblapp_emergency';
    protected $primaryKey = 'appemer_id';
    public $timestamps = false;

    protected $guarded = ['appemer_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
