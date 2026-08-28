<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $table = 'tblapp_family';
    protected $primaryKey = 'fam_id';
    public $timestamps = false;

    protected $guarded = ['fam_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
