<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Miq extends Model
{
    protected $table = 'tblapp_miq';
    protected $primaryKey = 'miq_id';
    public $timestamps = false;

    protected $guarded = ['miq_id'];


    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        /* 
            1 = LINGUISTIC
            2 = LOGICAL/MATHEMATICAL
            3 = VISUAL/SPATIAL
            4 = BODY KINESTHETIC
            5 = MUSICAL/ARTISTIC
            6 = INTERPERSONAL
            7 = INTRAPERSONAL
            8 = NATURALIST
        */
        $list = [
            1 => ['cat' => 1, 'ans' => "1. I enjoy telling stories and jokes."],
            2 => ['cat' => 2, 'ans' => "2. I really enjoy my math class."],
            3 => ['cat' => 3, 'ans' => "3. I prefer a map and written directions."],
            4 => ['cat' => 4, 'ans' => "4. My favorite class is gym since I like sports."],
            5 => ['cat' => 5, 'ans' => "5. I enjoy listening to CDs and the radio."],
            6 => ['cat' => 6, 'ans' => "6. I get along well with others."],
            7 => ['cat' => 7, 'ans' => "7. I like to work alone without anyone."],
            8 => ['cat' => 8, 'ans' => "8. I am keenly aware of my surroundings and of what goes on around me"],
            9 => ['cat' => 1, 'ans' => "9. I have a good memory for trivia."],
            10 => ['cat' => 2, 'ans' => "10. I like logical math puzzles and brain teasers."],
            11 => ['cat' => 3, 'ans' => "11. I daydream a lot."],
            12 => ['cat' => 4, 'ans' => "12. I enjoy activities such as woodworking, sewing, and building models."],
            13 => ['cat' => 5, 'ans' => "13. I tend to hum to myself when working."],
            14 => ['cat' => 6, 'ans' => "14. I like to belong to clubs and organizations."],
            15 => ['cat' => 7, 'ans' => "15. I like to keep a diary."],
            16 => ['cat' => 8, 'ans' => "16. I love walking in the woods."],
            17 => ['cat' => 1, 'ans' => "17. I enjoy word games (e.g. scrabble, boggle, crossword…)"],
            18 => ['cat' => 2, 'ans' => "18. I find solving math problems to be fun."],
            19 => ['cat' => 3, 'ans' => "19. Enjoy hobbies such as photography."],
            20 => ['cat' => 4, 'ans' => "20. When looking at things, I like touching them."],
            21 => ['cat' => 5, 'ans' => "21. I like to sing."],
            22 => ['cat' => 6, 'ans' => "22. I have several very close friends."],
            23 => ['cat' => 7, 'ans' => "23. I like myself (most of the time)."],
            24 => ['cat' => 8, 'ans' => "24. I enjoy gardening."],
            25 => ['cat' => 1, 'ans' => "25. I read books just for fun."],
            26 => ['cat' => 2, 'ans' => "26. If I have to memorize something, I tend to place events in a logical order."],
            27 => ['cat' => 3, 'ans' => "27. I like to draw and create."],
            28 => ['cat' => 4, 'ans' => "28. I have trouble sitting still for any length of time."],
            29 => ['cat' => 5, 'ans' => "29. I play a musical instrument quite well."],
            30 => ['cat' => 6, 'ans' => "30. I like helping teach others."],
            31 => ['cat' => 7, 'ans' => "31. I don't like crowds."],
            32 => ['cat' => 8, 'ans' => "32. I like to collect things (e.g. rocks, sports cards, stamps, etc)"],
            33 => ['cat' => 1, 'ans' => "33. I am a good speller (most of the time)"],
            34 => ['cat' => 2, 'ans' => "34. I like to find out how things work."],
            35 => ['cat' => 3, 'ans' => "35. If I have to memorize something, I draw a diagram to help me remember."],
            36 => ['cat' => 4, 'ans' => "36. I use a lot of body movements when talking."],
            37 => ['cat' => 5, 'ans' => "37. I like to have music playing when doing homework or studying."],
            38 => ['cat' => 6, 'ans' => "38. I like working with others in groups."],
            39 => ['cat' => 7, 'ans' => "39. I know what I am good at and what I am weak at."],
            40 => ['cat' => 8, 'ans' => "40. As an adult, I think I would like to get away from the city and enjoy nature."],
            41 => ['cat' => 1, 'ans' => "41. In an argument, I tend to use put downs or sarcasms."],
            42 => ['cat' => 2, 'ans' => "42. I enjoy computer and other math games."],
            43 => ['cat' => 3, 'ans' => "43. I like to doodle on paper whenever I can."],
            44 => ['cat' => 4, 'ans' => "44. If I have to memorize something, I write it out a number of times until I know it."],
            45 => ['cat' => 5, 'ans' => "45. If I have to memorize something, I try to create a rhyme about the event."],
            46 => ['cat' => 6, 'ans' => "46. Friends ask my advice because I seem to be a natural leader."],
            47 => ['cat' => 7, 'ans' => "47. I find that I am strong willed, independent, and don't follow the crowd."],
            48 => ['cat' => 8, 'ans' => "48. If I have to memorize something, I tend to organize it in categories."],
            49 => ['cat' => 1, 'ans' => "49. I like talking and writing about ideas."],
            50 => ['cat' => 2, 'ans' => "50. I like playing chess, checkers or monopoly."],
            51 => ['cat' => 3, 'ans' => "51. In a magazine, I prefer looking at the pictures rather than reading the text."],
            52 => ['cat' => 4, 'ans' => "52. I tend to tap my finger or play with my pencil during class."],
            53 => ['cat' => 5, 'ans' => "53. In an argument, I tend to shout or punch or move in some sort of rhythm."],
            54 => ['cat' => 6, 'ans' => "54. If I have to memorize something, I ask someone to quiz me to see if I know it."],
            55 => ['cat' => 7, 'ans' => "55. If I have to memorize something, I tend to close my eyes and feel the situation."],
            56 => ['cat' => 8, 'ans' => "56. I enjoy learning the names of living things in our environment, such as flowers and trees."],
            57 => ['cat' => 1, 'ans' => "57. If I have to memorize something, I create a rhyme or a saying to help me remember."],
            58 => ['cat' => 2, 'ans' => "58. In an argument, I try to find a fair and logical solution."],
            59 => ['cat' => 3, 'ans' => "59. In an argument, I try to keep my distance, keep silent or visualize some solutions."],
            60 => ['cat' => 4, 'ans' => "60. In an argument, I tend to strike out and hit or run away."],
            61 => ['cat' => 5, 'ans' => "61. I can remember the melodies of many songs."],
            62 => ['cat' => 6, 'ans' => "62. In an argument, I tend to ask a friend or some person in authority for help."],
            63 => ['cat' => 7, 'ans' => "63. In an argument, I will usually walk away until I calm down."],
            64 => ['cat' => 8, 'ans' => "64. In an argument, I tend to compare my opponent to someone or something I have read or heard about and react accordingly."],
            65 => ['cat' => 1, 'ans' => "65. If something breaks or won't work, I read the instructions book first."],
            66 => ['cat' => 2, 'ans' => "66. If something breaks or won't work, I look at the pieces and try to figure out how it works."],
            67 => ['cat' => 3, 'ans' => "67. If something breaks or won't work, I tend to study the diagram about how it works."],
            68 => ['cat' => 4, 'ans' => "68. If something breaks or won't work, I tend to play with the pieces to try to fit them together."],
            69 => ['cat' => 5, 'ans' => "69. If something breaks or won't work, I tend to tap my fingers to a beat while I figure it out."],
            70 => ['cat' => 6, 'ans' => "70. If something breaks or won't work, I try to find someone who can help me."],
            71 => ['cat' => 7, 'ans' => "71. If something breaks or won't work, I wonder if it's worth fixing up."],
            72 => ['cat' => 8, 'ans' => "72. If something breaks down, I look around me to try and see what I can find to fix the problem."],
            73 => ['cat' => 1, 'ans' => "73. For a group presentation, I prefer to do the writing and library research."],
            74 => ['cat' => 2, 'ans' => "74. For a group presentation, I prefer to create charts and graphs."],
            75 => ['cat' => 3, 'ans' => "75. For a group presentation, I prefer to draw all the pictures."],
            76 => ['cat' => 4, 'ans' => "76. For a group presentation, I prefer to move the props around, holding things up or build a model."],
            77 => ['cat' => 5, 'ans' => "77. For a group presentation, I prefer to put new words to a popular tune or use music."],
            78 => ['cat' => 6, 'ans' => "78. For a group presentation, I like to help organize the group's efforts."],
            79 => ['cat' => 7, 'ans' => "79. For a group presentation, I like to contribute something that is uniquely mine often based on how I feel."],
            80 => ['cat' => 8, 'ans' => "80. For a group presentation, I prefer to organize and classify the information into categories so it makes sense."]
        ];

        return $list;
    }
}
