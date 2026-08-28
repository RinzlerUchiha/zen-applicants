<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enneagram extends Model
{
    protected $table = 'tblapp_enneagramtest';
    protected $primaryKey = 'enneagram_id';
    public $timestamps = false;

    protected $guarded = ['enneagram_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            1 => [
                1 => 'I like to be organized and orderly.',
                2 => 'I want people to feel comfortable coming to me for guidance and advice.',
                3 => 'I’m almost always busy.',
                4 => 'Being understood is very important to me.',
                5 => 'I learn from observing or reading as opposed to doing.',
                6 => 'I am nervous around certain authority figures.',
                7 => 'I enjoy life. I am generally uninhibited (outgoing) and optimistic.',
                8 => 'I can be assertive (self-confident) and aggressive when I need to be.',
                9 => 'Sometimes I feel shy and unsure of myself.'
            ],
            2 => [
                1 => 'It is difficult for me to be spontaneous.',
                2 => 'Relationships are more important to me than almost anything.',
                3 => 'I like to make to-do lists, progress charts, and schedules for myself.',
                4 => 'My friends say they enjoy my warmth and my different way of looking at life.',
                5 => 'It’s hard to express my feelings in the moment.',
                6 => 'I am often plagued by doubt.',
                7 => 'I don’t like being made to feel obligated or beholden.',
                8 => 'I can’t stand being used or manipulated.',
                9 => 'I often feel in union with nature and people.'
            ],
            3 => [
                1 => 'I often feel guilty about not getting enough accomplished.',
                2 => 'Sometimes I feel overburdened by the people’s dependence on me.',
                3 => 'I don’t mind being asked to work overtime.',
                4 => 'I can become nonfunctional for hours, days, or weeks when I’m depressed.',
                5 => 'I get lost in my interests and like to be alone with them for hours.',
                6 => 'I like to have clear-cut guidelines and to know where I stand.',
                7 => 'I am busy and energetic. I seldom get bored if left to do what I want.',
                8 => 'I value being direct and honest; I put my cards on the table.',
                9 => 'Making choices can be very difficult. I can see the advantages and disadvantages of every option.'
            ],
            4 => [
                1 => 'I don’t like it when people break rules.',
                2 => 'I have trouble asking for what I need.',
                3 => 'I have an optimistic attitude.',
                4 => 'I am very sensitive to critical remarks and feel hurt at the tiniest slight.',
                5 => 'I usually experience my feelings more deeply when I’m by myself.',
                6 => 'I am always on the alert for danger.',
                7 => 'I often take verbal or physical risks.',
                8 => 'I am an individualist and a nonconformist.',
                9 => 'It is sometimes hard for me to know what I want when I’m with other people.'
            ],
            5 => [
                1 => 'Incorrect grammar and spelling bother me lot.',
                2 => 'I crave, yet sometimes fear, intimacy.',
                3 => 'I go full force until I get the job done.',
                4 => 'It really affects me emotionally when I read upsetting stories in the newspaper.',
                5 => 'Sometimes I feel guilty that I’m not generous enough.',
                6 => 'I take things too seriously.',
                7 => 'I usually pick upbeat friends who have similar goals.',
                8 => 'I respect people who stand up for themselves.',
                9 => 'Others see me as peaceful, but inside I often feel anxious.'
            ],
            6 => [
                1 => 'I am idealistic. I want to make the world a better place.',
                2 => 'I am more comfortable giving than receiving.',
                3 => 'I believe in doing things as expediently as possible.',
                4 => 'My ideals are very important to me.',
                5 => 'I try to conceal my sensitivity to criticism and judgment.',
                6 => 'I constantly question myself about what might go wrong.',
                7 => 'I’m not an expert in any one thing, but I can do many things well.',
                8 => 'I will go to any lengths to protect those I love.',
                9 => 'Instead of tackling what I really need to do, I sometimes do little, unimportant things.'
            ],
            7 => [
                1 => 'I am almost always on time.',
                2 => 'I am very sensitive to criticism.',
                3 => 'It is important for people to better themselves and live up to their potential.',
                4 => 'I cry easily. Beauty, love, sorrow, and pain really touch me.',
                5 => 'Brash, loud people offend me.',
                6 => 'I often experience criticism as an attack.',
                7 => 'My style I to go back and forth from one task to another. I like to keep moving.',
                8 => 'I fight for what is right.',
                9 => 'When there is unpleasantness going on around me, I just try to think about something else for a while.'
            ],
            8 => [
                1 => 'I hold on to resentment (anger/bitterness) for a long time.',
                2 => 'I work hard to overcome all obstacles in a relationship.',
                3 => 'I’m not interested in talking a lot about my personal life.',
                4 => 'My melancholy (sad) moods are real and important. I don’t necessarily want to get out of them.',
                5 => 'Conforming is distasteful to me.',
                6 => 'I often obsess about what my partner is thinking.',
                7 => 'I seem to let go of grievances and recover loss faster than most people I know.',
                8 => 'I support the underdog (loser).',
                9 => 'I usually prefer walking away from a disagreement to confronting someone.'
            ],
            9 => [
                1 => 'I think of myself as being practical, reasonable, and realistic.',
                2 => 'I try to be as sensitive and tactful as possible.',
                3 => 'I try not to let illness stop me from doing anything.',
                4 => 'I often long for what others have.',
                5 => 'I like to associate with others who have expertise in my field.',
                6 => 'I can be a very hard worker.',
                7 => 'I like myself and I’m good to myself.',
                8 => 'Making decisions is not difficult for me.',
                9 => 'If I don’t have some routine and structure in my day, I get almost nothing done.'
            ],
            10 => [
                1 => 'When jealous, I become fearful and competitive.',
                2 => 'When I am alone I know what I want, but when I am with others I am not sure.',
                3 => 'I hate to see jobs undone.',
                4 => 'I try to support my friends, especially when they are in crisis.',
                5 => 'l like having title (doctor, professor, administrator) to feel proud of.',
                6 => 'My friends think of me as loyal, supportive, and compassionate.',
                7 => 'I like people and they usually like me.',
                8 => 'Self-reliance and independence are important.',
                9 => 'I tend to put things off until the last minute, but I almost always get them done.'
            ],
            11 => [
                1 => 'Either I don’t have enough time to relax or I think I shouldn’t relax.',
                2 => 'It is very important that others feel comfortable and welcome in my home.',
                3 => 'I tend to put work before other things.',
                4 => 'I live in the past and in the future more than in present-day reality.',
                5 => 'I have been accused of being negative, cynical, and suspicious.',
                6 => 'I’ve been told I have a good sense of humor.',
                7 => 'I usually manage to get I want.',
                8 => 'I have overindulged in food or drugs.',
                9 => 'I like to be calm and unhurried, but sometimes I overextend myself.'
            ],
            12 => [
                1 => 'I tend to see things in terms of right and wrong, good or bad.',
                2 => 'I don’t want my dependence to show.',
                3 => 'I can’t understand people who are bored. I never run out of things to do',
                4 => 'I place great importance on my intuition.',
                5 => 'When I feel socially uncomfortable, I often wish I could disappear.',
                6 => 'I follow rules closely (a phobic trait); or often break rules (a counterphobic trait).',
                7 => 'I value quick wit.',
                8 => 'Some people take offense at my bluntness (frankness).',
                9 => 'When people try to tell me what to do or try to control me, I get stubborn.'
            ],
            13 => [
                1 => 'I analyze major purchases very thoroughly before I make them.',
                2 => 'Watching violence on television and seeing people suffer is unbearable.',
                3 => 'It is sometimes difficult for me to get in touch with my feelings.',
                4 => 'I try to control people at times.',
                5 => 'I am often reluctant to be assertive or aggressive.',
                6 => 'The more vulnerable I am in my intimate relationship, the more anxious and testy I become.',
                7 => 'I am idealistic. I want to contribute something to the world.',
                8 => 'When I enter a new group, I know immediately who the most powerful person is.',
                9 => 'I like to be sure to have time in my day of relaxing.'
            ],
            14 => [
                1 => 'I dread (fear) being criticized or judged by others.',
                2 => 'Sometimes I feel a deep sense of loneliness.',
                3 => 'I work very hard to take care of and provide for my family.',
                4 => 'I hate insincerity and lack of integrity in others.',
                5 => 'I dislike most social events. I’d rather be alone or with few people I know well.',
                6 => 'I tend to either procrastinate or plunge headlong, even into dangerous situations.',
                7 => 'I vacillate (hesitate) between feeling committed and wanting my freedom and independence.',
                8 => 'I work hard and I know how to get thing done.',
                9 => 'I enjoy just hanging out with my partner or friends.'
            ],
            15 => [
                1 => 'I often compare myself with others.',
                2 => 'If I don’t get the closeness I need, I feel sad, hurt, and unimportant.',
                3 => 'I like identifying with competent groups or important people.',
                4 => 'I have spent years longing for the great love of my life to come along.',
                5 => 'I sometimes feel shy or awkward.',
                6 => 'I am very aware of people trying to manipulate me with flattery.',
                7 => 'I am often at ease in groups.',
                8 => 'In a group I am sometimes an observer rather than a participant.',
                9 => 'Supportive and harmonious relationships are very important to me.'
            ],
            16 => [
                1 => 'Truth and justice are very important to me.',
                2 => 'Sometimes I get physically ill and emotionally drained from taking care of everyone else.',
                3 => 'I try to present myself well and make a good first impression.',
                4 => 'I focus on what is wrong with me rather that what is right.',
                5 => 'I get tired when I’m with people for too long.',
                6 => 'I like predictability.',
                7 => 'When people are unhappy, I usually try to get them lighten up and see the bright side.',
                8 => 'I like excitement and stimulation.',
                9 => 'I am very sensitive about being judged and take criticism personally.'
            ],
            17 => [
                1 => 'I often feel that time is running out and there is too much left to do.',
                2 => 'I often figure out what others would like in a person, then act that way.',
                3 => 'Financial security is extremely important to me.',
                4 => 'I like to be seen as one of a kind',
                5 => 'I feel different from most people.',
                6 => 'I have sabotaged my own success.',
                7 => 'I love excitement and travel.',
                8 => 'Sometimes I like to spar (fight, argue) with people, especially when I feel safe.',
                9 => 'I like to listen and give people support.'
            ],
            18 => [
                1 => 'I almost always do what I say I will do.',
                2 => 'I enjoy giving compliments and telling people that they are special to me.',
                3 => 'I generally feel pretty good about myself.',
                4 => 'I am always searching for my true self.',
                5 => 'I feel invisible. It surprises me when anyone notices anything about me.',
                6 => 'I can support people through think and thin.',
                7 => 'Sometimes I feel inferior and sometimes I feel superior to others.',
                8 => 'I am vulnerable and loving when I really trust someone.',
                9 => 'I focus more on the positive than the negative.'
            ],
            19 => [
                1 => 'I worry almost constantly.',
                2 => 'I am attracted to being with important or powerful people.',
                3 => 'People often look to me to run the show.',
                4 => 'Sometimes I feel very uncomfortable and different, like an isolated outsider, even when I’m with my friends.',
                5 => 'I don’t look for material possessions to make me unhappy.',
                6 => 'Being neat and orderly helps me feel more in control of my life.',
                7 => 'I usually say whatever is on my mind. Sometimes it gets me into trouble.',
                8 => 'Overly nice or flattering people bother me.',
                9 => 'I have trouble getting rid of things.'
            ],
            20 => [
                1 => 'I love making every detail perfect.',
                2 => 'People have said I exaggerate too much and am overly emotional.',
                3 => 'I like to stand out in some way.',
                4 => 'When people tell me what to do, I often become rebellious and do, or wish I could do, the opposite.',
                5 => 'Acting calm is a defense. It makes me feel stronger.',
                6 => 'I dislike pretension in people.',
                7 => 'I can make great sacrifices to help people.',
                8 => 'Pretense is particularly distasteful to me.',
                9 => 'I operate under the principle of inertia: If I’m going, it’s easy to keep going but I sometimes have hard time getting started.'
            ]
        ];

        return $list;
    }
}
