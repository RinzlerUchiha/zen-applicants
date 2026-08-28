<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tapt extends Model
{
    protected $table = 'tblapp_tapt';
    protected $primaryKey = 'tapt_id';
    public $timestamps = false;

    protected $guarded = ['tapt_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            'e_i' => [
                1 => ['e' => 'Lively', 'i' => 'Calm'],
                2 => ['e' => 'Talkative', 'i' => 'Reserved'],
                3 => ['e' => 'Expressive', 'i' => 'Quiet'],
                4 => ['e' => 'Interaction', 'i' => 'Concentration'],
                5 => ['e' => 'Outward', 'i' => 'Inward'],
                6 => ['e' => 'Talk', 'i' => 'Listen'],
                7 => ['e' => 'Outspoken', 'i' => 'Introspective (thoughtful)']
            ],
            's_n' => [
                1 => ['s' => 'Concrete', 'n' => 'Abstract'],
                2 => ['s' => 'Builder', 'n' => 'Inventor'],
                3 => ['s' => 'Realistic', 'n' => 'Idealistic'],
                4 => ['s' => 'Practical', 'n' => 'Ingenious'],
                5 => ['s' => 'Literal', 'n' => 'Figurative'],
                6 => ['s' => 'Application', 'n' => 'Implication'],
                7 => ['s' => 'Realities', 'n' => 'Possibilities']
            ],
            't_f' => [
                1 => ['t' => 'Just', 'f' => 'Humane'],
                2 => ['t' => 'Logical', 'f' => 'Sentimental'],
                3 => ['t' => 'Thinking', 'f' => 'Feeling'],
                4 => ['t' => 'Analyze', 'f' => 'Empathize'],
                5 => ['t' => 'Head', 'f' => 'Heart'],
                6 => ['t' => 'Critique', 'f' => 'Appreciate'],
                7 => ['t' => 'Firm-minded', 'f' => 'Tender-hearted']
            ],
            'j_p' => [
                1 => ['j' => 'Scheduled', 'p' => 'Spontaneous'],
                2 => ['j' => 'Disciplined', 'p' => 'Free Spirit'],
                3 => ['j' => 'Decide', 'p' => 'Wait & See'],
                4 => ['j' => 'Structure', 'p' => 'Flow'],
                5 => ['j' => 'Plan', 'p' => 'Improvise'],
                6 => ['j' => 'Organized', 'p' => 'Free-flowing'],
                7 => ['j' => 'Finish', 'p' => 'Start']
            ]
        ];

        return $list;
    }
}
