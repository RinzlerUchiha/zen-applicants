<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disc extends Model
{
    protected $table = 'tblapp_disc';
    protected $primaryKey = 'disc_id';
    public $timestamps = false;

    protected $guarded = ['disc_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            'A' => [
                'D' => 'FORCEFUL: a strong person and as the power to convince or impress others',
                'I' => 'LIVELY: alert, active/ fast and quick to respond',
                'S' => 'MODEST: simple/ humble/ shy',
                'C' => 'TACTFUL: very careful in what she/he is saying in order not to hurt others'
            ],
            'B' => [
                'D' => 'AGGRESSIVE: an assertive, is the one who always do the first move or attack',
                'I' => 'EMOTIONAL: easily be hurt emotionally/ too readily affected by the feelings (sensitive)',
                'S' => 'ACCOMODATING; being courteous/ willing to adapt and accept ideas from others',
                'C' => 'CONSISTENT: unchangeable/ same/ equal/ constant'
            ],
            'C' => [
                'D' => 'DIRECT: a frank person',
                'I' => 'ANIMATED: energetic, lively, full of life',
                'S' => 'AGGREABLE: open and willing to agree and accept ideas from others',
                'C' => 'ACCURATE: exact/ precise/ correct'
            ],
            'D' => [
                'D' => 'TOUGH: accepts all things as challenge/ person who endures hardship',
                'I' => 'PEOPLE ORIENTED: love to be with people',
                'S' => 'GENTLE: tender/ lenient',
                'C' => 'PERFECTIONIST: not contended with everything less than the very best'
            ],
            'E' => [
                'D' => 'DARING: a frank person',
                'I' => 'IMPULSIVE: sudden response (unexpected)',
                'S' => 'KIND: good-hearted/ considerate/ generous (willing to share w/ others what he/ she has)',
                'C' => 'CAUTIOUS: careful/ attentive to safety'
            ],
            'F' => [
                'D' => 'COMPETITIVE: willing to compete or oppose',
                'I' => 'EXPRESSIVE: revealed true feelings of what you think or feel',
                'S' => 'SUPPORTIVE: helpful/ give support/ affording',
                'C' => 'PRECISE: correct/ definite/ accurate in every detail'
            ],
            'G' => [
                'D' => 'RISK TAKER: a person who face danger',
                'I' => 'TALKATIVE: fond of talking/ chatty',
                'S' => 'RELAXED: mild/ rest/ less strict/ less tense',
                'C' => 'FACTUAL: concerned with facts'
            ],
            'H' => [
                'D' => 'ARGUMENTATIVE: raise discussions or objection (contrary and faultfinding)		',
                'I' => 'FUN LOVING: loves pleasure and enjoyment',
                'S' => 'PATIENT: uncomplaining even if in trouble, difficulties and hardships',
                'C' => 'LOGICAL: sensible, analytical/ consistent with correct reasoning'
            ],
            'I' => [
                'D' => 'BOLD: a wise and strongly assertive person',
                'I' => 'SPONTANEOUS: automatic/ continuity/ respond immediately/ reactive',
                'S' => 'STABLE: permanent/ steady/ stable personality',
                'C' => 'ORGANIZED: orderly/ systematic/ well-arranged'
            ],
            'J' => [
                'D' => 'TAKE CHARGE: willing to accept responsibility		',
                'I' => 'OPTIMISTIC: hopeful; positive thinker; always thinks the brighter side of life',
                'S' => 'PEACEFUL: quiet/ calm/ cool/ untroubled',
                'C' => 'CONSCIENTIOUS: careful/ governed by conscience'
            ],
            'K' => [
                'D' => 'CANDID: honest/ sincere/ open and frankly truthful even of the truth is unpleasant	',
                'I' => 'CHEERFUL: a happy person/ lively/ jolly',
                'S' => 'LOYAL: faithful and true',
                'C' => 'SERIOUS: solemn personality/ occupied with serious thought'
            ],
            'L' => [
                'D' => 'INDEPENDENT: can stand alone/ self-supporting/ not dependent on others',
                'I' => 'ENTHUSIASTIC: passionately interested in something- fun, admirer or a supporter',
                'S' => 'GOOD LISTENER: attentively listening when somebody is talking',
                'C' => 'HIGH STANDARD: high level of expectations'
            ]
        ];

        return $list;
    }
}
