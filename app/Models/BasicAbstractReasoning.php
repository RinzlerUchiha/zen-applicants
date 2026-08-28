<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicAbstractReasoning extends Model
{
    protected $table = 'tblapp_basicabstract';
    protected $primaryKey = 'abstract_id';
    public $timestamps = false;

    protected $guarded = ['abstract_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            1 => [
                'question' => '/file/basic-abstract-reasoning/q1.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q1-A.png',
                    'b' => '/file/basic-abstract-reasoning/q1-B.png',
                    'c' => '/file/basic-abstract-reasoning/q1-C.png',
                    'd' => '/file/basic-abstract-reasoning/q1-D.png',
                ]
            ],
            2 => [
                'question' => '/file/basic-abstract-reasoning/q2.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q2-A.png',
                    'b' => '/file/basic-abstract-reasoning/q2-B.png',
                    'c' => '/file/basic-abstract-reasoning/q2-C.png',
                    'd' => '/file/basic-abstract-reasoning/q2-D.png',
                ]
            ],
            3 => [
                'question' => '/file/basic-abstract-reasoning/q3.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q3-A.png',
                    'b' => '/file/basic-abstract-reasoning/q3-B.png',
                    'c' => '/file/basic-abstract-reasoning/q3-C.png',
                ]
            ],
            4 => [
                'question' => '/file/basic-abstract-reasoning/q4.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q4-A.png',
                    'b' => '/file/basic-abstract-reasoning/q4-B.png',
                    'c' => '/file/basic-abstract-reasoning/q4-C.png',
                    'd' => '/file/basic-abstract-reasoning/q4-D.png',
                ]
            ],
            5 => [
                'question' => '/file/basic-abstract-reasoning/q5.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q5-A.png',
                    'b' => '/file/basic-abstract-reasoning/q5-B.png',
                    'c' => '/file/basic-abstract-reasoning/q5-C.png',
                ]
            ],
            6 => [
                'question' => '/file/basic-abstract-reasoning/q6.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q6-A.png',
                    'b' => '/file/basic-abstract-reasoning/q6-B.png',
                    'c' => '/file/basic-abstract-reasoning/q6-C.png',
                ]
            ],
            7 => [
                'question' => '/file/basic-abstract-reasoning/q7.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q7-A.png',
                    'b' => '/file/basic-abstract-reasoning/q7-B.png',
                ]
            ],
            8 => [
                'question' => '/file/basic-abstract-reasoning/q8.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q8-A.png',
                    'b' => '/file/basic-abstract-reasoning/q8-B.png',
                ]
            ],
            9 => [
                'question' => '/file/basic-abstract-reasoning/q9.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q9-A.png',
                    'b' => '/file/basic-abstract-reasoning/q9-B.png',
                    'c' => '/file/basic-abstract-reasoning/q9-C.png',
                ]
            ],
            10 => [
                'question' => '/file/basic-abstract-reasoning/q10.png',
                'option' => [
                    'a' => '/file/basic-abstract-reasoning/q10-A.png',
                    'b' => '/file/basic-abstract-reasoning/q10-B.png',
                    'c' => '/file/basic-abstract-reasoning/q10-C.png',
                ]
            ],
        ];

        return $list;
    }
}
