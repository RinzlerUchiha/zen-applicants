<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    // protected $table = 'tblapp_skill_software';
    protected $table = 'tblapp_skills';
    protected $primaryKey = 'skill_id';
    public $timestamps = false;

    protected $guarded = ['skill_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }
}
