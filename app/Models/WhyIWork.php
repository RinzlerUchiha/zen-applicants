<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyIWork extends Model
{
    protected $table = 'tblapp_whyiwork';
    protected $primaryKey = 'wiw_id';
    public $timestamps = false;

    protected $guarded = ['wiw_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            1 => [
                'cat' => 'Achievement',
                'desc' => 'I work because I like the feeling that I get from successfully accomplishing a job, overcoming obstacles and obtaining goals.'
            ],
            2 => [
                'cat' => 'Family',
                'desc' => 'I work because I love my family and my family inspires me to work.'
            ],
            3 => [
                'cat' => 'Independence',
                'desc' => 'I work because my work makes me feel free to manage the tasks and responsibilities assigned to me, the way I think they ought to be managed, with no or very little help from my superiors. I enjoy the opportunity to " be my own boss"'
            ],
            4 => [
                'cat' => 'Money',
                'desc' => 'I work because of the amount of personal financial income provided by this job.'
            ],
            5 => [
                'cat' => 'Personal Growth',
                'desc' => 'I work because I like the feeling of growing as an individual or becoming more competent, more efficient - a better person, because of the challenges of my work.'
            ],
            6 => [
                'cat' => 'Pleasure',
                'desc' => 'I work because this work of mine allows me to enjoy things, activities and people, I would not be able to enjoy in another job.'
            ],
            7 => [
                'cat' => 'Power',
                'desc' => 'I work because of the sense of control I feel over my destiny and the destiny of others. I feel very good about the power I have to influence to direct behavior of others.'
            ],
            8 => [
                'cat' => 'Pressure',
                'desc' => 'I work because I like the constant feeling or need to show continuing improvement in the performance of my job.'
            ],
            9 => [
                'cat' => 'Recognition',
                'desc' => 'I work because my work makes me feel good when I am recognized or rewarded for doing a job well done, in speech, or when I am awarded a certificate or plaque of recognition.'
            ],
            10 => [
                'cat' => 'Security',
                'desc' => 'I work because in this job and company, I feel secured: there is certainty that I will be able to maintain my position and I feel that tomorrow will be least as good as today'
            ],
            11 => [
                'cat' => 'Self-Esteem',
                'desc' => 'I work because work makes me feel good about my-self: I have a greater sense of self-worth and self-esteem'
            ],
            12 => [
                'cat' => 'Prestige',
                'desc' => 'I work because I feel good about the respect and admiration I get from my peers and others.'
            ],
        ];

        return $list;
    }
}