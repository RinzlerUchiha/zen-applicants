<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterRef extends Model
{
    protected $table = 'tblapp_reference';
    protected $primaryKey = 'ref_id';
    public $timestamps = false;

    protected $guarded = ['ref_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
