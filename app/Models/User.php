<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'tblapp_persinfo';
    protected $primaryKey = 'app_id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protected $fillable = [
    //     'app_mobile',
    //     'app_email',
    //     'app_code',
    // ];

    protected $guarded = ['app_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'app_code',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password' => 'hashed',
    //     ];
    // }

    public function getAppAgeAttribute()
    {
        return \Carbon\Carbon::parse($this->app_bdate)->age;
    }

    // Override the getAuthPassword method to return the custom password field
    public function getAuthPassword()
    {
        return $this->app_code; // Custom password field
    }

    public function getFirstLastNameAttribute()
    {
        return "{$this->app_fname} {$this->app_lname}";
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'app_id', 'app_id');
    }

    public function family()
    {
        return $this->hasMany(Family::class, 'app_id', 'app_id');
    }

    public function skill()
    {
        return $this->hasMany(Skill::class, 'app_id', 'app_id');
    }

    public function education()
    {
        return $this->hasMany(Education::class, 'app_id', 'app_id');
    }

    public function eligibility()
    {
        return $this->hasMany(Eligibility::class, 'app_id', 'app_id');
    }

    public function certificate()
    {
        return $this->hasMany(Certificate::class, 'app_id', 'app_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'app_id', 'app_id');
    }

    public function employmentRec()
    {
        return $this->hasMany(EmploymentRec::class, 'app_id', 'app_id');
    }

    public function characterRef()
    {
        return $this->hasMany(CharacterRef::class, 'app_id', 'app_id');
    }

    public function emergencyContact()
    {
        return $this->hasMany(EmergencyContact::class, 'app_id', 'app_id');
    }

    public function enneagram()
    {
        return $this->hasOne(Enneagram::class, 'app_id', 'app_id');
    }

    public function tapt()
    {
        return $this->hasOne(Tapt::class, 'app_id', 'app_id');
    }

    public function disc()
    {
        return $this->hasOne(Disc::class, 'app_id', 'app_id');
    }

    public function miq()
    {
        return $this->hasOne(Miq::class, 'app_id', 'app_id');
    }

    public function color()
    {
        return $this->hasOne(Color::class, 'app_id', 'app_id');
    }

    public function vak()
    {
        return $this->hasOne(Vak::class, 'app_id', 'app_id');
    }

    public function whyIWork()
    {
        return $this->hasOne(WhyIWork::class, 'app_id', 'app_id');
    }

    public function careerAnchor()
    {
        return $this->hasOne(CareerAnchor::class, 'app_id', 'app_id');
    }

    public function basicAbstractReasoning()
    {
        return $this->hasOne(BasicAbstractReasoning::class, 'app_id', 'app_id');
    }

    public function basicMath()
    {
        return $this->hasOne(BasicMath::class, 'app_id', 'app_id');
    }

    public function maya()
    {
        return $this->hasOne(Maya::class, 'app_id', 'app_id');
    }
}
