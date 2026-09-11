<?php

/*
|--------------------------------------------------------------------------
| Applicant Terms of Use
|--------------------------------------------------------------------------
|
| The registration acknowledgement now covers BOTH the Terms of Use and the
| Privacy Notice, so a Terms of Use document has to exist to be acknowledged.
| This is that document.
|
| ############################################################################
| # ITEMS BELOW MARKED "CONFIRM" ARE PLACEHOLDERS.                           #
| # They are legal determinations, not wording exercises, and must be        #
| # supplied by legal before this is published. Nothing here has been        #
| # invented to fill a gap.                                                  #
| ############################################################################
|
| Deliberately NOT included, and deliberately not drafted:
|   - Any waiver of the applicant's rights under RA 10173. Those rights are
|     statutory and cannot be signed away by a portal checkbox.
|   - Any blanket limitation of liability or indemnity in the company's
|     favour. The purpose of this acknowledgement is informed consent to
|     recruitment processing, not a liability waiver.
|   - Any term purporting to make the company exempt from responsibility for
|     a personal data breach.
|
| What IS stated below is limited to the operational rules of using this
| portal, which are factual and observable from the system itself.
|
*/

return [

    /*
    | CONFIRM: the exact registered legal entity these terms are between.
    | Shares the controller identity with the privacy notice.
    */
    'entity' => [
        'name'    => 'ZenHub',            // CONFIRM — registered entity name
        'confirm' => true,
    ],

    /*
    | CONFIRM with legal: governing law and venue for disputes. Left null on
    | purpose — picking a forum is a legal decision.
    */
    'governing_law' => [
        'statement' => null,              // CONFIRM
        'confirm'   => true,
    ],

    /*
    | Effective date / version of this document. Shown to the applicant so the
    | acknowledgement refers to something identifiable.
    |
    | NOTE: the acknowledgement currently records only a timestamp
    | (tblapp_persinfo.app_privacy_ack_at). It does not record WHICH version
    | was acknowledged. See the implementation notes — adding a version column
    | was out of scope for this change.
    */
    'version' => '2026-09-draft',

    /*
    | Operational rules of using the portal. Each of these is a statement of
    | how the system actually behaves, not a legal position.
    */
    'using_the_portal' => [
        'This portal is for applying to positions at the organisation and for maintaining your applicant profile. It is not a job board for third parties.',
        'You need one applicant profile. Creating several profiles for the same person makes your applications harder to process, not easier.',
        'You are responsible for keeping your sign-in details to yourself. Anything submitted from your profile is treated as submitted by you.',
        'You can sign in at any time to view, correct or update the information in your profile.',
    ],

    /*
    | Accuracy of information. This is the one obligation that genuinely runs
    | from the applicant to the employer, and it is standard in recruitment.
    */
    'your_information' => [
        'The information you provide must be truthful, accurate and your own.',
        'Documents you upload must be genuine and must relate to you.',
        'If something changes — your contact details, your employment status — please update your profile so we can reach you.',
        'Information found to be falsified may end consideration of your application, and may be grounds for ending employment if discovered after hiring.',
    ],

    /*
    | What submitting an application does and does not mean. Stated plainly
    | because applicants reasonably want to know.
    */
    'applications' => [
        'Submitting an application does not create an employment relationship and is not an offer or guarantee of employment.',
        'We are not able to respond individually to every application, and positions may be filled, changed or withdrawn.',
        'Assessments, where they form part of the process, are provided by HR at the appropriate stage. Answers must be your own work.',
        'Your profile is kept so you can apply to other positions without starting again, and becomes the basis of your employee record if you are hired.',
    ],

    /*
    | Availability. Factual, and phrased without an absolute claim in either
    | direction.
    */
    'availability' => 'We aim to keep this portal available and working, but it may be unavailable at times for maintenance or for reasons outside our control. If you cannot submit something, please contact HR rather than assume it was received.',

    /*
    | Changes to the terms.
    */
    'changes' => 'These terms may be updated. The version in force is the one published on this page, identified by the date shown at the top.',
];
