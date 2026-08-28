<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'tblapp_whatcolorareyou';
    protected $primaryKey = 'wcay_id';
    public $timestamps = false;

    protected $guarded = ['wcay_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        /* 
            1 = Blue | Controller
            2 = Green | Analyst
            3 = Red | Promoter
            4 = Yellow | Supporter
        */
        $list = [
            1 => [
                1 => 'decisive',
                2 => 'orderly',
                3 => 'optimistic',
                4 => 'patient'
            ],
            2 => [
                1 => 'independent',
                2 => 'performs exacting work  (precise)',
                3 => 'tends to be exciting/stimulating',
                4 => 'accommodating'
            ],
            3 => [
                1 => 'tends to be dominant',
                2 => 'likes controlled circumstances',
                3 => 'generates enthusiasm',
                4 => 'good listener'
            ],
            4 => [
                1 => 'strong willed',
                2 => 'likes assurance of security',
                3 => 'often dramatic',
                4 => 'shows loyalty'
            ],
            5 => [
                1 => 'wants immediate results',
                2 => 'uses critical thinking',
                3 => 'talkative',
                4 => 'concentrates on task accuracy'
            ],
            6 => [
                1 => 'causes action',
                2 => 'follows rules',
                3 => 'open and friendly',
                4 => 'likes security and stability'
            ],
            7 => [
                1 => 'likes power & authority',
                2 => 'reads & follows instructions',
                3 => 'likes working with people',
                4 => 'needs good reason for change'
            ],
            8 => [
                1 => 'likes freedom from control',
                2 => 'prefers status quo',
                3 => 'likes working in groups',
                4 => 'home life a priority'
            ],
            9 => [
                1 => 'dislikes supervision',
                2 => 'dislikes sudden or abrupt changes',
                3 => 'desires to help others',
                4 => 'expects credit for work done'
            ],
            10 => [
                1 => 'outspoken',
                2 => 'tends to be serious and persistent',
                3 => 'wants freedom of expression',
                4 => 'likes traditional procedures'
            ],
            11 => [
                1 => 'wants direct answers',
                2 => 'cautious',
                3 => 'wants freedom from detail',
                4 => 'dislikes conflict'
            ],
            12 => [
                1 => 'restless',
                2 => 'diplomatic',
                3 => 'likes change, spontaneity',
                4 => 'neighborly'
            ],
            13 => [
                1 => 'competitive',
                2 => 'respectful',
                3 => 'persuasive',
                4 => 'considerate towards others'
            ],
            14 => [
                1 => 'adventurous',
                2 => 'agreeable',
                3 => 'appears confident',
                4 => 'feels it is important to perform good work'
            ],
            15 => [
                1 => 'assertive',
                2 => 'checks for accuracy',
                3 => 'likes recognition',
                4 => 'finds pleasure in sharing & giving'
            ]
        ];

        return $list;
    }
}