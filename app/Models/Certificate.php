<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'tblapp_certificate';
    protected $primaryKey = 'cert_id';
    public $timestamps = false;

    protected $guarded = ['cert_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
