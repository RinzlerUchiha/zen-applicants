<?php

/*
|--------------------------------------------------------------------------
| Application Form — completeness rules
|--------------------------------------------------------------------------
|
| The finalised two-tier classification (HireFlow Phase 2.5).
|
|   'apply'  — does this block the applicant from completing their
|              initial application?
|   'for201' — is this expected on the employee 201 record after hiring?
|
| A field being optional to apply is NOT a statement that it is unimportant.
| The Applicant Profile is persistent and becomes the foundation of the 201
| file, so 'for201' fields are still collected and still stored — they just
| are not the candidate's obstacle today.
|
| Only 'apply' is used by the completeness calculation. 'for201' is recorded
| here so the pre-employment requirements step (Phase 4) has a definition to
| work from rather than re-deriving one.
|
| Values for 'apply':
|   required     — must be filled
|   conditional  — required unless the stated condition exempts it
|   optional     — never blocks
|   system       — not applicant-editable
|   hidden       — not shown to applicants (column retained)
|
*/

return [

    /*
    | Sections whose completion blocks the application. Order is the order
    | the applicant walks through them.
    */
    'sections' => [

        /* ---------------------------------------------------------------- */
        'personal' => [
            'label'    => 'Personal details',
            'route'    => 'personal.show',
            'type'     => 'single',
            'blocking' => true,
            'fields' => [
                'app_lname'       => ['label' => 'Last name',      'apply' => 'required', 'for201' => true],
                'app_fname'       => ['label' => 'First name',     'apply' => 'required', 'for201' => true],
                'app_bdate'       => ['label' => 'Birth date',     'apply' => 'required', 'for201' => true],
                'app_sex'         => ['label' => 'Sex',            'apply' => 'required', 'for201' => true],
                'app_cstatus'     => ['label' => 'Civil status',   'apply' => 'required', 'for201' => true],
                'app_nationality' => ['label' => 'Nationality',    'apply' => 'required', 'for201' => true],
                'app_mobile'      => ['label' => 'Mobile number',  'apply' => 'required', 'for201' => true],
                'app_email'       => ['label' => 'Email',          'apply' => 'required', 'for201' => true],

                'app_mname'       => ['label' => 'Middle name',    'apply' => 'optional', 'for201' => true],
                'app_btype'       => ['label' => 'Blood type',     'apply' => 'optional', 'for201' => true],
                'app_sss'         => ['label' => 'SSS #',          'apply' => 'optional', 'for201' => true],
                'app_philhealth'  => ['label' => 'PhilHealth #',   'apply' => 'optional', 'for201' => true],
                'app_pagibig'     => ['label' => 'Pag-IBIG #',     'apply' => 'optional', 'for201' => true],
                'app_tin'         => ['label' => 'TIN #',          'apply' => 'optional', 'for201' => true],
                'app_img'         => ['label' => 'Profile photo',  'apply' => 'optional', 'for201' => true],

                'app_suffix'      => ['label' => 'Suffix',         'apply' => 'optional', 'for201' => false],
                'app_religion'    => ['label' => 'Religion',       'apply' => 'optional', 'for201' => false],
                'app_dialect'     => ['label' => 'Dialect',        'apply' => 'optional', 'for201' => false],
                'app_height'      => ['label' => 'Height',         'apply' => 'optional', 'for201' => false],
                'app_weight'      => ['label' => 'Weight',         'apply' => 'optional', 'for201' => false],
                'app_telephone'   => ['label' => 'Telephone',      'apply' => 'optional', 'for201' => false],

                'app_posapplied'  => ['label' => 'Position applied', 'apply' => 'system', 'for201' => true],
                'app_age'         => ['label' => 'Age',             'apply' => 'system', 'for201' => true],

                // Hidden from the applicant form. Column deliberately retained —
                // whether to drop it is a separate decision.
                'app_bmark'       => ['label' => 'Birthmark',       'apply' => 'hidden', 'for201' => false],
            ],

            /*
            | Address lives in tblapp_address, one row per applicant.
            | Permanent and current are required to apply; place of birth is
            | needed for statutory forms at hiring, not to assess a candidate.
            */
            'address' => [
                'required' => ['add_perm_prov', 'add_perm_city', 'add_perm_brngy', 'add_perm_location',
                               'add_cur_prov',  'add_cur_city',  'add_cur_brngy',  'add_cur_location'],
                'for201'   => ['add_birth_prov', 'add_birth_city', 'add_birth_brngy'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /*
        | Family is optional to APPLY. Nobody is stopped from applying for not
        | listing a relative, so an empty section is complete. A family member
        | who is added must still be a complete record — that is enforced on
        | save in FamilyController, not here.
        |
        | This is stage-specific: family information becomes mandatory at the
        | probation stage, as part of the employee information requirements.
        | That belongs to the probation stage's own rules, not to this file,
        | which describes the application only.
        */
        'family' => [
            'label'    => 'Family background',
            'route'    => 'family.index',
            'type'     => 'repeating',
            'blocking' => false,
            'min_rows' => 0,
            'fields' => [
                'fam_relationship' => ['label' => 'Relationship', 'apply' => 'required', 'for201' => true],
                'fam_lastname'     => ['label' => 'Last name',    'apply' => 'required', 'for201' => true],
                'fam_firstname'    => ['label' => 'First name',   'apply' => 'required', 'for201' => true],
                'fam_sex'          => ['label' => 'Sex',          'apply' => 'required', 'for201' => true],

                'fam_birthdate'    => ['label' => 'Birth date',     'apply' => 'optional', 'for201' => true],
                'fam_contact'      => ['label' => 'Contact number', 'apply' => 'optional', 'for201' => true],

                'fam_midname'      => ['label' => 'Middle name', 'apply' => 'optional', 'for201' => false],
                'fam_maidenname'   => ['label' => 'Maiden name', 'apply' => 'optional', 'for201' => false],
                'fam_suffix'       => ['label' => 'Suffix',      'apply' => 'optional', 'for201' => false],
                'fam_occupation'   => ['label' => 'Occupation',  'apply' => 'optional', 'for201' => false],
                'fam_workplace'    => ['label' => 'Workplace',   'apply' => 'optional', 'for201' => false],
                'fam_add'          => ['label' => 'Address',     'apply' => 'optional', 'for201' => false],
            ],

            /*
            | Confirmed rule: a spouse row is required when civil status is
            | Married. Widowed and Separated are deliberately excluded — forcing
            | a spouse row there would compel an entry the applicant may have no
            | way to complete.
            |
            | Now that the section is optional, this applies to a family list
            | that has been started: a married applicant who lists relatives is
            | asked for the spouse among them. An applicant who lists nobody is
            | not asked for anything.
            */
            'requires_spouse_when_civil_status_in' => ['married'],
        ],

        /* ---------------------------------------------------------------- */
        'education' => [
            'label'    => 'Education',
            'route'    => 'education.index',
            'type'     => 'repeating',
            'blocking' => true,
            'min_rows' => 1,
            'fields' => [
                'educ_level'      => ['label' => 'Level',          'apply' => 'required', 'for201' => true],
                'educ_currStatus' => ['label' => 'Current status', 'apply' => 'required', 'for201' => true],
                'educ_school'     => ['label' => 'School',         'apply' => 'required', 'for201' => true],

                'educ_degreetitle' => ['label' => 'Degree / title',  'apply' => 'conditional', 'for201' => true],
                'educ_yeargrad'    => ['label' => 'Year graduated', 'apply' => 'conditional', 'for201' => true],

                'educ_major'      => ['label' => 'Major',          'apply' => 'optional', 'for201' => false],
                'educ_schooladd'  => ['label' => 'School address', 'apply' => 'optional', 'for201' => false],
            ],
            'conditions' => [
                // A degree title is meaningless on an elementary or secondary row.
                'educ_degreetitle' => ['exempt_when' => ['educ_level' => ['elementary', 'secondary', 'primary', 'high school', 'junior high', 'senior high']]],
                // Nothing to state until the applicant has actually graduated.
                'educ_yeargrad'    => ['exempt_when' => ['educ_currStatus' => ['ongoing', 'undergraduate', 'currently enrolled']]],
            ],
        ],

        /* ---------------------------------------------------------------- */
        'employment' => [
            'label'    => 'Employment record',
            'route'    => 'employment.index',
            'type'     => 'repeating',
            'blocking' => true,
            'min_rows' => 1,
            'fields' => [
                'empl_company'  => ['label' => 'Company',   'apply' => 'required', 'for201' => true],
                'empl_position' => ['label' => 'Position',  'apply' => 'required', 'for201' => true],
                'empl_from'     => ['label' => 'Date from', 'apply' => 'required', 'for201' => true],

                'empl_to'     => ['label' => 'Date to',            'apply' => 'conditional', 'for201' => true],
                'empl_reason' => ['label' => 'Reason for leaving', 'apply' => 'conditional', 'for201' => true],

                'empl_address'    => ['label' => 'Company address',    'apply' => 'optional', 'for201' => false],
                'empl_supervisor' => ['label' => 'Supervisor',         'apply' => 'optional', 'for201' => false],
                'empl_contact'    => ['label' => 'Supervisor contact', 'apply' => 'optional', 'for201' => false],
            ],
            'conditions' => [
                // Neither question has an answer for a job the applicant still holds.
                'empl_to'     => ['exempt_when_flag' => 'empl_is_current'],
                'empl_reason' => ['exempt_when_flag' => 'empl_is_current'],
            ],

            /*
            | A fresh graduate has no employment history. Ticking this satisfies
            | the section without loosening the rule for anyone who does have one.
            */
            'skip_flag'       => 'app_no_work_experience',
            'skip_flag_label' => 'This is my first job',
        ],

        /* ---------------------------------------------------------------- */
        'skills' => [
            'label'    => 'Special skills',
            'route'    => 'skill.index',
            'type'     => 'repeating',
            'blocking' => false,     // optional section — zero rows is complete
            'min_rows' => 0,
            'fields' => [
                'skill_category' => ['label' => 'Category', 'apply' => 'required', 'for201' => false],
                'skill_type'     => ['label' => 'Type',     'apply' => 'optional', 'for201' => false],
                'skill_others'   => ['label' => 'Other',    'apply' => 'optional', 'for201' => false],
            ],

            /*
            | skill_type is an integer FK into zen.tbl_skill_type, and the form
            | offers skill_others as a free-text alternative for anything not on
            | that list — SkillController validates skill-type as nullable for
            | exactly this reason. So the rule is not "type required, other
            | conditional"; it is: pick a category, then EITHER a listed type OR
            | describe your own. Each group below must have at least one answer.
            */
            'any_of' => [
                ['fields' => ['skill_type', 'skill_others'], 'label' => 'A skill type or description'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        'eligibility' => [
            'label'    => 'Eligibility / Licenses',
            'route'    => 'license.index',
            'type'     => 'repeating',
            'blocking' => false,
            'min_rows' => 0,
            'fields' => [
                'el_type'       => ['label' => 'Type',              'apply' => 'required',    'for201' => true],
                'el_profession' => ['label' => 'Profession',        'apply' => 'required',    'for201' => true],
                'el_regdate'    => ['label' => 'Registration date', 'apply' => 'required',    'for201' => true],
                'el_expdate'    => ['label' => 'Valid until',       'apply' => 'conditional', 'for201' => true],
                'el_file'       => ['label' => 'Attachment',        'apply' => 'optional',    'for201' => false],
            ],
            'conditions' => [
                // Civil service eligibility, among others, never expires.
                'el_expdate' => ['exempt_always' => true],
            ],
        ],

        /* ---------------------------------------------------------------- */
        'certificate' => [
            'label'    => 'Certificates / Trainings',
            'route'    => 'certificate.index',
            'type'     => 'repeating',
            'blocking' => false,
            'min_rows' => 0,
            'fields' => [
                'cert_title'   => ['label' => 'Title',           'apply' => 'required', 'for201' => true],
                'cert_date'    => ['label' => 'Completion date', 'apply' => 'required', 'for201' => true],
                'cert_address' => ['label' => 'Location',        'apply' => 'optional', 'for201' => false],
                'cert_speaker' => ['label' => 'Speaker',         'apply' => 'optional', 'for201' => false],
                'cert_file'    => ['label' => 'Attachment',      'apply' => 'optional', 'for201' => false],
            ],
        ],

        /* ---------------------------------------------------------------- */
        'reference' => [
            'label'    => 'Character references',
            'route'    => 'characterref.index',
            'type'     => 'repeating',
            'blocking' => false,
            'min_rows' => 0,
            'fields' => [
                'ref_fullname'     => ['label' => 'Full name',      'apply' => 'required', 'for201' => true],
                'ref_contact'      => ['label' => 'Contact number', 'apply' => 'required', 'for201' => true],
                'ref_relationship' => ['label' => 'Relationship',   'apply' => 'required', 'for201' => true],
                'ref_position'     => ['label' => 'Position',       'apply' => 'optional', 'for201' => false],
                'ref_company'      => ['label' => 'Company',        'apply' => 'optional', 'for201' => false],
                'ref_address'      => ['label' => 'Address',        'apply' => 'optional', 'for201' => false],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Assessments
    |--------------------------------------------------------------------------
    | The eleven existing assessments. Their questions, result tables and answer
    | formats are unchanged; this block adds how each one is RUN.
    |
    |   kind      aptitude       graded by HR against zen-admin's answer key
    |             questionnaire  a personality/preference inventory — no right
    |                            or wrong answers
    |   minutes   the time limit, enforced by the server
    |   typical   roughly how long most people take (shown to the applicant)
    |   size      what the applicant will face, in plain words
    |   shuffle   questions / choices shown in a per-attempt random order.
    |             The submitted values stay the original keys, so HR's marking
    |             is unaffected.
    |   table     the existing result table ("completed" = a row exists)
    |
    | How the durations were decided (HireFlow 2.5):
    |   - The three aptitude tests keep the limit their own pages already stated
    |     and ran: Abstract Reasoning "10 Questions; 10mins exam", Basic Math
    |     "12 Questions: 12mins exam", Maya 30 minutes (60 progressive
    |     matrices in five sets of twelve). The old "About N minutes" figures
    |     (12, 10 and 8) contradicted them and are replaced.
    |   - The questionnaires were never timed. Their limit is a generous cap
    |     that bounds the session without making it a speed test: roughly twice
    |     the typical time for their length, rounded up to 5, minimum 10.
    |       Enneagram       20 groups of 9 statements (180)   ~10  -> 20
    |       TAPT            28 word pairs                     ~5   -> 10
    |       DISC            12 sets of 4 words to rank        ~8   -> 15
    |       Multiple Int.   80 statements                     ~8   -> 15
    |       Colour          15 items, 4 choices               ~4   -> 10
    |       VAK             30 items                          ~5   -> 10
    |       Why I Work      12 outcomes to rank               ~5   -> 10
    |       Career Anchors  40 statements rated 1-6, pick 3   ~8   -> 15
    |
    | Shuffling: Basic Math and Abstract Reasoning shuffle both questions and
    | choices (independent items). Maya keeps its order — progressive matrices
    | run easy to hard, and its answers 1-6 name numbered pieces in the image.
    | Questionnaires keep their order: several are built as ordered sets, and
    | there is no answer to copy.
    */
    'assessments' => [
        // The Assessments page's note when the applicant has no access yet.
        'gate_message' => 'HR gives you an access code after your initial interview. Enter it here to open the assessments.',

        'list' => [
            'enneagram'          => ['label' => 'Enneagram',             'route' => 'enneagram.show',          'size' => '20 groups of statements', 'kind' => 'questionnaire', 'minutes' => 20, 'typical' => 10, 'table' => 'tblapp_enneagramtest'],
            'tapt'               => ['label' => 'TAPT',                  'route' => 'tapt.show',               'size' => '28 word pairs', 'kind' => 'questionnaire', 'minutes' => 10, 'typical' => 5,  'table' => 'tblapp_tapt'],
            'disc'               => ['label' => 'DISC',                  'route' => 'disc.show',               'size' => '12 sets of words to rank', 'kind' => 'questionnaire', 'minutes' => 15, 'typical' => 8,  'table' => 'tblapp_disc'],
            'miq'                => ['label' => 'Multiple Intelligence', 'route' => 'miq.show',                'size' => '80 statements', 'kind' => 'questionnaire', 'minutes' => 15, 'typical' => 8,  'table' => 'tblapp_miq'],
            'color'              => ['label' => 'What colour are you?',  'route' => 'color.show',              'size' => '15 items', 'kind' => 'questionnaire', 'minutes' => 10, 'typical' => 4,  'table' => 'tblapp_whatcolorareyou'],
            'vak'                => ['label' => 'VAK',                   'route' => 'vak.show',                'size' => '30 questions', 'kind' => 'questionnaire', 'minutes' => 10, 'typical' => 5,  'table' => 'tblapp_vak'],
            'why_i_work'         => ['label' => 'Why I Work',            'route' => 'why_i_work.show',         'size' => '12 outcomes to rank', 'kind' => 'questionnaire', 'minutes' => 10, 'typical' => 5,  'table' => 'tblapp_whyiwork'],
            'career_anchors'     => ['label' => 'Career Anchors',        'route' => 'career_anchors.show',     'size' => '40 statements to rate', 'kind' => 'questionnaire', 'minutes' => 15, 'typical' => 8,  'table' => 'tblapp_careeranchors'],
            'abstract_reasoning' => ['label' => 'Abstract Reasoning',    'route' => 'abstract_reasoning.show', 'size' => '10 picture questions', 'kind' => 'aptitude',      'minutes' => 10, 'typical' => 10, 'table' => 'tblapp_basicabstract', 'shuffle' => ['questions' => true, 'choices' => true]],
            'basic_math'         => ['label' => 'Basic Math',            'route' => 'basic_math.show',         'size' => '12 questions', 'kind' => 'aptitude',      'minutes' => 12, 'typical' => 12, 'table' => 'tblapp_basicmath',     'shuffle' => ['questions' => true, 'choices' => true]],
            'maya'               => ['label' => 'Maya',                  'route' => 'maya.show',               'size' => '60 picture puzzles', 'kind' => 'aptitude',      'minutes' => 30, 'typical' => 30, 'table' => 'tblapp_maya'],
        ],

        /*
        | Running an attempt (the server's clock).
        |
        |   heartbeat_seconds  how often an open exam page checks in (and
        |                      autosaves)
        |   grace_seconds      a NEW page load (refresh, reopened tab) within
        |                      this long of the last check-in simply carries on.
        |                      Later than this — the browser was closed or the
        |                      power went — the attempt is INTERRUPTED: the clock
        |                      stops where the page was last seen, and a new HR
        |                      code resumes it with the time that was left.
        |                      The same open window reconnecting after any gap
        |                      (a dropped connection) always carries on, with
        |                      the gap counted as exam time.
        */
        'attempts' => [
            'heartbeat_seconds' => 20,
            'grace_seconds'     => 180,
        ],

        /*
        | Access codes (the gate). HR issues one from the applicant's profile in
        | zen-admin; it opens the assessments in the browser session it is
        | entered in. zen-admin's config/applicant_assessments.php mirrors
        | code_minutes and code_length.
        |
        |   code_minutes   the code must be used within this long
        |   unlock_hours   once entered, assessments may be started for this long
        |   max_failures   wrong entries before the code is revoked
        */
        'access' => [
            'code_length'  => 6,
            'code_minutes' => 30,
            'unlock_hours' => 4,
            'max_failures' => 5,
        ],
    ],
];
