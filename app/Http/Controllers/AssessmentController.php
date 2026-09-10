<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/**
 * Index of the eleven assessments.
 *
 * Completion is read directly from each assessment's own table rather than
 * tracked separately, so the list cannot drift out of step with the pages.
 *
 * The gate that decides when these become available is a separate design item
 * (anti-cheating / OTP) and is deliberately not implemented here — this
 * controller neither enforces nor assumes one.
 */
class AssessmentController extends Controller
{
    /** Assessment route => the table that holds a completed answer. */
    private const TABLES = [
        'enneagram.show'           => 'tblapp_enneagramtest',
        'tapt.show'                => 'tblapp_tapt',
        'disc.show'                => 'tblapp_disc',
        'miq.show'                 => 'tblapp_miq',
        'color.show'               => 'tblapp_whatcolorareyou',
        'vak.show'                 => 'tblapp_vak',
        'why_i_work.show'          => 'tblapp_whyiwork',
        'career_anchors.show'      => 'tblapp_careeranchors',
        'abstract_reasoning.show'  => 'tblapp_basicabstract',
        'basic_math.show'          => 'tblapp_basicmath',
        'maya.show'                => 'tblapp_maya',
    ];

    public function index()
    {
        $appId = auth()->user()->app_id;

        $assessments = collect(config('application_form.assessments.list'))
            ->map(function ($item) use ($appId) {
                $table = self::TABLES[$item['route']] ?? null;

                $item['done'] = $table
                    ? DB::table($table)->where('app_id', $appId)->exists()
                    : false;

                return $item;
            })
            ->values()
            ->all();

        return view('pages.assessments', [
            'assessments' => $assessments,
            'completed'   => collect($assessments)->where('done', true)->count(),
        ]);
    }
}
