<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maya extends Model
{
    protected $table = 'tblapp_maya';
    protected $primaryKey = 'maya_id';
    public $timestamps = false;

    protected $guarded = ['maya_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            'a' => [
                1 => [
                    'question' => '/file/maya-test/a1.png',
                    'answer' => '5',
                    'options' => [1,2,3,4,5,6],
                ],
                2 => [
                    'question' => '/file/maya-test/a2.png',
                    'answer' => '1',
                    'options' => [1,2,3,4,5,6],
                ],
                3 => [
                    'question' => '/file/maya-test/a3.png',
                    'answer' => '4',
                    'options' => [1,2,3,4,5,6],
                ],
                4 => [
                    'question' => '/file/maya-test/a4.png',
                    'answer' => '2',
                    'options' => [1,2,3,4,5,6],
                ],
                5 => [
                    'question' => '/file/maya-test/a5.png',
                    'answer' => '6',
                    'options' => [1,2,3,4,5,6],
                ],
                6 => [
                    'question' => '/file/maya-test/a6.png',
                    'answer' => '3',
                    'options' => [1,2,3,4,5,6],
                ],
                7 => [
                    'question' => '/file/maya-test/a7.png',
                    'answer' => '6',
                    'options' => [1,2,3,4,5,6],
                ],
                8 => [
                    'question' => '/file/maya-test/a8.png',
                    'answer' => '2',
                    'options' => [1,2,3,4,5,6],
                ],
                9 => [
                    'question' => '/file/maya-test/a9.png',
                    'answer' => '1',
                    'options' => [1,2,3,4,5,6],
                ],
                10 => [
                    'question' => '/file/maya-test/a10.png',
                    'answer' => '3',
                    'options' => [1,2,3,4,5,6],
                ],
                11 => [
                    'question' => '/file/maya-test/a11.png',
                    'answer' => '5',
                    'options' => [1,2,3,4,5,6],
                ],
                12 => [
                    'question' => '/file/maya-test/a12.png',
                    'answer' => '4',
                    'options' => [1,2,3,4,5,6],
                ],
            ],
            'b' => [
                1 => [
                    'question' => '/file/maya-test/b1.png',
                    'answer' => '2',
                    'options' => [1,2,3,4,5,6],
                ],
                2 => [
                    'question' => '/file/maya-test/b2.png',
                    'answer' => '6',
                    'options' => [1,2,3,4,5,6],
                ],
                3 => [
                    'question' => '/file/maya-test/b3.png',
                    'answer' => '1',
                    'options' => [1,2,3,4,5,6],
                ],
                4 => [
                    'question' => '/file/maya-test/b4.png',
                    'answer' => '2',
                    'options' => [1,2,3,4,5,6],
                ],
                5 => [
                    'question' => '/file/maya-test/b5.png',
                    'answer' => '1',
                    'options' => [1,2,3,4,5,6],
                ],
                6 => [
                    'question' => '/file/maya-test/b6.png',
                    'answer' => '3',
                    'options' => [1,2,3,4,5,6],
                ],
                7 => [
                    'question' => '/file/maya-test/b7.png',
                    'answer' => '5',
                    'options' => [1,2,3,4,5,6],
                ],
                8 => [
                    'question' => '/file/maya-test/b8.png',
                    'answer' => '6',
                    'options' => [1,2,3,4,5,6],
                ],
                9 => [
                    'question' => '/file/maya-test/b9.png',
                    'answer' => '4',
                    'options' => [1,2,3,4,5,6],
                ],
                10 => [
                    'question' => '/file/maya-test/b10.png',
                    'answer' => '3',
                    'options' => [1,2,3,4,5,6],
                ],
                11 => [
                    'question' => '/file/maya-test/b11.png',
                    'answer' => '4',
                    'options' => [1,2,3,4,5,6],
                ],
                12 => [
                    'question' => '/file/maya-test/b12.png',
                    'answer' => '5',
                    'options' => [1,2,3,4,5,6],
                ],
            ],
            'c' => [
                1 => [
                    'question' => '/file/maya-test/c1.png',
                    'answer' => '8',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                2 => [
                    'question' => '/file/maya-test/c2.png',
                    'answer' => '2',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                3 => [
                    'question' => '/file/maya-test/c3.png',
                    'answer' => '3',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                4 => [
                    'question' => '/file/maya-test/c4.png',
                    'answer' => '8',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                5 => [
                    'question' => '/file/maya-test/c5.png',
                    'answer' => '7',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                6 => [
                    'question' => '/file/maya-test/c6.png',
                    'answer' => '4',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                7 => [
                    'question' => '/file/maya-test/c7.png',
                    'answer' => '5',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                8 => [
                    'question' => '/file/maya-test/c8.png',
                    'answer' => '1',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                9 => [
                    'question' => '/file/maya-test/c9.png',
                    'answer' => '7',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                10 => [
                    'question' => '/file/maya-test/c10.png',
                    'answer' => '6',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                11 => [
                    'question' => '/file/maya-test/c11.png',
                    'answer' => '1',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                12 => [
                    'question' => '/file/maya-test/c12.png',
                    'answer' => '2',
                    'difficulty' => 'd',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
            ],
            'd' => [
                1 => [
                    'question' => '/file/maya-test/d1.png',
                    'answer' => '3',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                2 => [
                    'question' => '/file/maya-test/d2.png',
                    'answer' => '4',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                3 => [
                    'question' => '/file/maya-test/d3.png',
                    'answer' => '3',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                4 => [
                    'question' => '/file/maya-test/d4.png',
                    'answer' => '7',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                5 => [
                    'question' => '/file/maya-test/d5.png',
                    'answer' => '8',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                6 => [
                    'question' => '/file/maya-test/d6.png',
                    'answer' => '6',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                7 => [
                    'question' => '/file/maya-test/d7.png',
                    'answer' => '5',
                    'difficulty' => 've',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                8 => [
                    'question' => '/file/maya-test/d8.png',
                    'answer' => '4',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                9 => [
                    'question' => '/file/maya-test/d9.png',
                    'answer' => '1',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                10 => [
                    'question' => '/file/maya-test/d10.png',
                    'answer' => '2',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                11 => [
                    'question' => '/file/maya-test/d11.png',
                    'answer' => '5',
                    'difficulty' => 'd',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                12 => [
                    'question' => '/file/maya-test/d12.png',
                    'answer' => '6',
                    'difficulty' => 'd',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
            ],
            'e' => [
                1 => [
                    'question' => '/file/maya-test/e1.png',
                    'answer' => '7',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                2 => [
                    'question' => '/file/maya-test/e2.png',
                    'answer' => '6',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                3 => [
                    'question' => '/file/maya-test/e3.png',
                    'answer' => '8',
                    'difficulty' => 'e',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                4 => [
                    'question' => '/file/maya-test/e4.png',
                    'answer' => '2',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                5 => [
                    'question' => '/file/maya-test/e5.png',
                    'answer' => '1',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                6 => [
                    'question' => '/file/maya-test/e6.png',
                    'answer' => '5',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                7 => [
                    'question' => '/file/maya-test/e7.png',
                    'answer' => '2',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                8 => [
                    'question' => '/file/maya-test/e8.png',
                    'answer' => '4',
                    'difficulty' => 'vd',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                9 => [
                    'question' => '/file/maya-test/e9.png',
                    'answer' => '1',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                10 => [
                    'question' => '/file/maya-test/e10.png',
                    'answer' => '6',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                11 => [
                    'question' => '/file/maya-test/e11.png',
                    'answer' => '3',
                    'difficulty' => 'm',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
                12 => [
                    'question' => '/file/maya-test/e12.png',
                    'answer' => '5',
                    'difficulty' => 'd',
                    'options' => [1,2,3,4,5,6,7,8],
                ],
            ]
        ];

        return $list;
    }
}