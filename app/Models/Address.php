<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'tblapp_address';
    protected $primaryKey = 'add_id';
    public $timestamps = false;

    protected $guarded = ['add_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
