<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerAnchor extends Model
{
    protected $table = 'tblapp_careeranchors';
    protected $primaryKey = 'career_id';
    public $timestamps = false;

    protected $guarded = ['career_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            '1' => "1. I dream of being so good at what I do that my expert advice will be sought continually.",
            '2' => "2. I most fulfilled in my work when I have been able to integrate and manage the efforts of others.",
            '3' => "3. I dream of having a career that will allow me the freedom to do a job in my own way and on my own schedule.",
            '4' => "4. Security and stability are more important to me than freedom and autonomy.",
            '5' => "5. I am always on the look out for ideas that would permit me to start my own enterprise.",
            '6' => "6. I will feel successful in my career only if I have a feeling of having made a real contribution to the welfare of society.",
            '7' => "7. I dream of a career in which I can solve problems or win out in situations that are extremely challenging.",
            '8' => "8. I would rather leave my organization that be put into a job that would compromise my ability to pursue personal and family concerns.",
            '9' => "9. I will feel successful in my career only if I can develop my technical or functional skills to a very high level of competence.",
            '10' => "10. I dream of being in charge of a complex organization and making decisions that affect many people.",
            '11' => "11. I am most fulfilled in my work when I am completely free to define my own tasks, schedules, and procedures.",
            '12' => "12. I would rather leave my organization altogether than accept an assignment that would jeopardize my security in that organization.",
            '13' => "13. Building my own business is more important to me than achieving a high-level managerial position in someone else’s organization.",
            '14' => "14. I am most fulfilled in my career when I have been able to use my talents in the service of others.",
            '15' => "15. I will feel successful in my career only if I face and overcome very difficult challenges.",
            '16' => "16. I dream of a career that will permit me to integrate my personal, family, and work needs.",
            '17' => "17. Becoming a senior functional manager in my area of expertise is more attractive to me than becoming a general manager.",
            '18' => "18. I will feel successful in my career only if I become a general manager in some organization.",
            '19' => "19. I will feel successful in my career only if I achieve autonomy and freedom.",
            '20' => "20. I seek jobs in organizations that will give me a sense of security and stability.",
            '21' => "21. I am most fulfilled in my career when I have been able to build something that is entirely the result of my own ideas and efforts.",
            '22' => "22. Using my skills to make the world a better place to live and work is more important to me than achieving a high-level managerial position.",
            '23' => "23. I have been most fulfilled in my career when I have solved seemingly unsolvable problems or won out over seemingly impossible odds.",
            '24' => "24. I feel successful in life only if I have been able to balance my personal, family, and career requirements.",
            '25' => "25. I would rather leave my organization than accept a rotational assignment that would take me out of my area of expertise.",
            '26' => "26. Becoming a general manager is more attractive to me than becoming a senior functional manager in my area of expertise.",
            '27' => "27. The chance to do a job on my own way, free of rules and constraints is more important to me than security.",
            '28' => "28. I am most fulfilled in my work when I feel I have financial and employment security.",
            '29' => "29. I will feel successful in my career only if I have succeeded in creating or building something that is entirely my own product or idea.",
            '30' => "30. I dream of having a career that will make a real contribution to humanity and society.",
            '31' => "31. I seek out work opportunities that strongly challenge my problem-solving and/or competitive skills.",
            '32' => "32. Balancing the demands of personal and professional life is more important to me than achieving a high-level managerial position.",
            '33' => "33. I am most fulfilled in my work when I have been able to use my special skills and talents. ",
            '34' => "34. I would rather leave my organization than accept a job that would take me away from the general managerial track.",
            '35' => "35. I would rather leave my organization than accept a job that would reduce my autonomy and freedom.",
            '36' => "36. I dream of having a career that will allow me to have a sense of security and stability.",
            '37' => "37. I dream of starting up and building my own business.",
            '38' => "38. I would rather leave my organization than accept an assignment that would undermine my ability to be of service to others.",
            '39' => "39. Working on problems that are almost unsolvable is more important to me than achieving a high-level managerial position.",
            '40' => "40. I have always sought out work opportunities that would minimize interference with personal or family concerns.",
        ];

        return $list;
    }
}
