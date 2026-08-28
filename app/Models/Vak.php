<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vak extends Model
{
    protected $table = 'tblapp_vak';
    protected $primaryKey = 'vak_id';
    public $timestamps = false;

    protected $guarded = ['vak_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        /* 
            a = Visual
            b = Auditory
            c = Kinestetic
        */
        $list = [
            1 => [
                'question' => '1. When I operate new equipment I generally:',
                'answer' => [
                    'a' => 'a) read the instructions first',
                    'b' => 'b) listen to an explanation from someone who has used it before',
                    'c' => 'c) go ahead and have a go, I can figure it out as I use it',
                ]
            ],
            2 => [
                'question' => '2. When I need directions for travelling I usually:',
                'answer' => [
                    'a' => 'a) look at a map',
                    'b' => 'b) ask for spoken directions',
                    'c' => 'c) follow my nose and maybe use a compass',
                ]
            ],
            3 => [
                'question' => '3. When I cook a new dish, I like to:',
                'answer' => [
                    'a' => 'a) follow a written recipe',
                    'b' => 'b) call a friend for an explanation',
                    'c' => 'c) follow my instincts, testing as I cook',
                ]
            ],
            4 => [
                'question' => '4. If I am teaching someone something new, I tend to:',
                'answer' => [
                    'a' => 'a) write instructions down for them',
                    'b' => 'b) give them a verbal explanation',
                    'c' => 'c) demonstrate first and then let them have a go',
                ]
            ],
            5 => [
                'question' => '5. I tend to say:',
                'answer' => [
                    'a' => 'a) watch how I do it',
                    'b' => 'b) listen to me explain',
                    'c' => 'c) you have a go',
                ]
            ],
            6 => [
                'question' => '6. During my free time I most enjoy:',
                'answer' => [
                    'a' => 'a) going to museums and galleries',
                    'b' => 'b) listening to music and talking to my friends',
                    'c' => 'c) playing sport or doing DIY',
                ]
            ],
            7 => [
                'question' => '7. When I go shopping for clothes, I tend to:',
                'answer' => [
                    'a' => 'a) imagine what they would look like on',
                    'b' => 'b) discuss them with the shop staff',
                    'c' => 'c) try them on and test them out',
                ]
            ],
            8 => [
                'question' => '8. When I am choosing a holiday I usually:',
                'answer' => [
                    'a' => 'a) read lots of brochures',
                    'b' => 'b) listen to recommendations from friends',
                    'c' => 'c) imagine what it would be like to be there',
                ]
            ],
            9 => [
                'question' => '9. If I was buying a new car, I would:',
                'answer' => [
                    'a' => 'a) read reviews in newspapers and magazines',
                    'b' => 'b) discuss what I need with my friends',
                    'c' => 'c) test-drive lots of different types',
                ]
            ],
            10 => [
                'question' => '10. When I am learning a new skill, I am most comfortable:',
                'answer' => [
                    'a' => 'a)	watching what the teacher is doing',
                    'b' => 'b)	talking through with the teacher exactly what I’m supposed to do',
                    'c' => 'c)	giving it a try myself and work it out as I go',
                ]
            ],
            11 => [
                'question' => '11. If I am choosing food off a menu, I tend to:',
                'answer' => [
                    'a' => 'a) imagine what the food will look like',
                    'b' => 'b) talk through the options in my head or with my partner',
                    'c' => 'c) imagine what the food will taste like',
                ]
            ],
            12 => [
                'question' => '12. When I listen to a band, I can’t help:',
                'answer' => [
                    'a' => 'a) watching the band members and other people in the audience',
                    'b' => 'b) listening to the lyrics and the beats',
                    'c' => 'c) moving in time with the music',
                ]
            ],
            13 => [
                'question' => '13. When I concentrate, I most often:',
                'answer' => [
                    'a' => 'a) focus on the words or the pictures in front of me',
                    'b' => 'b) discuss the problem and the possible solutions in my head',
                    'c' => 'c) move around a lot, fiddle with pens and pencils and touch things',
                ]
            ],
            14 => [
                'question' => '14. I choose household furnishings because I like:',
                'answer' => [
                    'a' => 'a) their colours and how they look',
                    'b' => 'b) the descriptions the sales-people give me',
                    'c' => 'c) their textures and what it feels like to touch them',
                ]
            ],
            15 => [
                'question' => '15. My first memory is of:',
                'answer' => [
                    'a' => 'a) looking at something',
                    'b' => 'b) being spoken to',
                    'c' => 'c) doing something',
                ]
            ],
            16 => [
                'question' => '16. When I am anxious, I:',
                'answer' => [
                    'a' => 'a) visualise the worst-case scenarios',
                    'b' => 'b) talk over in my head what worries me most',
                    'c' => 'c) can’t sit still, fiddle and move around constantly',
                ]
            ],
            17 => [
                'question' => '17. I feel especially connected to other people because of:',
                'answer' => [
                    'a' => 'a) how they look',
                    'b' => 'b) what they say to me',
                    'c' => 'c) how they make me feel',
                ]
            ],
            18 => [
                'question' => '18. When I have to revise for an exam, I generally:',
                'answer' => [
                    'a' => 'a) write lots of revision notes and diagrams',
                    'b' => 'b) talk over my notes, alone or with other people',
                    'c' => 'c) imagine making the movement or creating the formula',
                ]
            ],
            19 => [
                'question' => '19. If I am explaining to someone I tend to:',
                'answer' => [
                    'a' => 'a) show them what I mean',
                    'b' => 'b) explain to them in different ways until they understand',
                    'c' => 'c) encourage them to try and talk them through my idea as they do it',
                ]
            ],
            20 => [
                'question' => '20. I really love:',
                'answer' => [
                    'a' => 'a) watching films, photography, looking at art or people watching',
                    'b' => 'b) listening to music, the radio or talking to friends',
                    'c' => 'c) taking part in sporting activities, eating fine foods and wines or dancing',
                ]
            ],
            21 => [
                'question' => '21. Most of my free time is spent:',
                'answer' => [
                    'a' => 'a) watching television',
                    'b' => 'b) talking to friends',
                    'c' => 'c) doing physical activity or making things',
                ]
            ],
            22 => [
                'question' => '22. When I first contact a new person, I usually:',
                'answer' => [
                    'a' => 'a) arrange a face to face meeting',
                    'b' => 'b) talk to them on the telephone',
                    'c' => 'c) try to get together whilst doing something else, such as an activity or a meal',
                ]
            ],
            23 => [
                'question' => '23. I first notice how people:',
                'answer' => [
                    'a' => 'a) look and dress',
                    'b' => 'b) sound and speak',
                    'c' => 'c) stand and move',
                ]
            ],
            24 => [
                'question' => '24. If I am angry, I tend to:',
                'answer' => [
                    'a' => 'a) keep replaying in my mind what it is that has upset me',
                    'b' => 'b) raise my voice and tell people how I feel',
                    'c' => 'c) stamp about, slam doors and physically demonstrate my anger',
                ]
            ],
            25 => [
                'question' => '25. I find it easiest to remember:',
                'answer' => [
                    'a' => 'a) faces',
                    'b' => 'b) names',
                    'c' => 'c) things I have done',
                ]
            ],
            26 => [
                'question' => '26. I think that you can tell if someone is lying if:',
                'answer' => [
                    'a' => 'a) they avoid looking at you',
                    'b' => 'b) their voices changes',
                    'c' => 'c) they give me funny vibes',
                ]
            ],
            27 => [
                'question' => '27. When I meet an old friend:',
                'answer' => [
                    'a' => 'a) I say “it’s great to see you!”',
                    'b' => 'b) I say “it’s great to hear from you!”',
                    'c' => 'c) I give them a hug or a handshake',
                ]
            ],
            28 => [
                'question' => '28. I remember things best by:',
                'answer' => [
                    'a' => 'a) writing notes or keeping printed details',
                    'b' => 'b) saying them aloud or repeating words and key points in my head',
                    'c' => 'c) doing and practising the activity or imagining it being done',
                ]
            ],
            29 => [
                'question' => '29. If I have to complain about faulty goods, I am most comfortable:',
                'answer' => [
                    'a' => 'a) writing a letter',
                    'b' => 'b) complaining over the phone',
                    'c' => 'c) taking the item back to the store or posting it to head office',
                ]
            ],
            30 => [
                'question' => '30. I tend to say:',
                'answer' => [
                    'a' => 'a) I see what you mean',
                    'b' => 'b) I hear what you are saying',
                    'c' => 'c) I know how you feel',
                ]
            ],
        ];

        return $list;
    }
}
