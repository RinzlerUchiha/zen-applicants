<?php

/*
|--------------------------------------------------------------------------
| Applications — status table (HireFlow Phase 2.5, Milestone 3)
|--------------------------------------------------------------------------
|
| The one definition of what each application status means. My Applications,
| Home, withdrawal, re-application and the Candidate Pool all read this, so no
| part of the system keeps its own idea of which statuses are "closed".
|
| Keys are the exact values stored in tblapp_applications.status. They are
| stored data: renaming a key orphans existing rows.
|
| zen-admin keeps an identical copy in zen-admin/config/applications.php. The
| keys and both flags must match in the two files.
|
|   closed          the application has ended. Closed applications no longer
|                   block a new application to the same posting (the database
|                   enforces one OPEN application per posting).
|   candidate_pool  the candidate did not progress for THIS application and the
|                   record is retained. Candidate Pool is per application, never
|                   a status of the applicant as a whole.
|
| A status that is not listed here is treated as open and not in the pool, so
| an unexpected value can never close an application or pool a candidate.
|
*/

return [

    'statuses' => [
        'Applied' => [
            'closed' => false,
            'candidate_pool' => false,
        ],

        // Every document the application's process asked for was accepted.
        'Documents Complete' => [
            'closed' => false,
            'candidate_pool' => false,
        ],

        // The applicant, or HR on their behalf, ended this one application.
        'Withdrawn' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        // This application's document deadline passed with requests unresolved.
        'Non-Responsive' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        // HR decided not to proceed with this application.
        'Not Selected' => [
            'closed' => true,
            'candidate_pool' => true,
        ],
    ],

    /*
    | Re-application cooldowns, by the status an application closed with.
    |
    | A cooldown applies to the SAME job posting only. Being Not Selected for
    | Job A blocks a new application to Job A for the period; Job B and Job C
    | are unaffected. It is never applicant-wide.
    |
    | Only statuses listed here start a cooldown. Withdrawn and Non-Responsive
    | deliberately do not. The rule is applied by App\Services\ReapplicationPolicy.
    */
    'cooldowns' => [
        'Not Selected' => ['months' => 6],
    ],

    /*
    | How each status is shown to the APPLICANT: the pill's wording and colour.
    | Presentation only — what a status means is 'statuses' above. The three
    | ways an application can end are deliberately told apart:
    |
    |   Withdrawn       the applicant's own decision (grey)
    |   Non-Responsive  the document deadline passed (amber)
    |   Not Selected    HR's decision (red)
    |
    | Not mirrored in zen-admin, which shows HR the stored status itself.
    */
    'display' => [
        'Applied'            => ['label' => 'Applied',                 'pill' => 'zn-pill-acc'],
        'Documents Complete' => ['label' => 'Documents complete',      'pill' => 'zn-pill-ok'],
        'Withdrawn'          => ['label' => 'Withdrawn',               'pill' => 'zn-pill-opt'],
        'Non-Responsive'     => ['label' => 'No response by deadline', 'pill' => 'zn-pill-caution'],
        'Not Selected'       => ['label' => 'Not selected',            'pill' => 'zn-pill-req'],
    ],

];
