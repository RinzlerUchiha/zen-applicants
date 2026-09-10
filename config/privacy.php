<?php

/*
|--------------------------------------------------------------------------
| Applicant Privacy Notice
|--------------------------------------------------------------------------
|
| Content for the notice shown at the point of collection, as required by the
| Data Privacy Act of 2012 (RA 10173) and its IRR.
|
| ############################################################################
| # ITEMS BELOW MARKED "CONFIRM" ARE PLACEHOLDERS.                           #
| # They must be supplied by HR / legal / the Data Protection Officer before #
| # this goes live. They are not invented values and must not be treated as  #
| # approved copy.                                                           #
| ############################################################################
|
| Statutory reference for what a notice must contain (RA 10173 Sec. 16(b) and
| IRR Sec. 34(a)): the identity of the personal information controller, the
| purpose and legal basis of processing, the scope and method, the recipients,
| the retention period, the data subject's rights, and how to reach the DPO.
|
*/

return [

    /*
    | Identity of the personal information controller.
    | CONFIRM: the exact registered legal entity name that acts as PIC.
    */
    'controller' => [
        'name'    => 'ZenHub',            // CONFIRM — registered entity name
        'confirm' => true,
    ],

    /*
    | Data Protection Officer contact.
    | CONFIRM: RA 10173 requires a designated DPO and a working contact route
    | for data subjects. Do not publish this notice without real details.
    */
    'dpo' => [
        'name'    => null,                // CONFIRM
        'email'   => null,                // CONFIRM
        'phone'   => null,                // CONFIRM
        'address' => null,                // CONFIRM
        'confirm' => true,
    ],

    /*
    | Retention period.
    | CONFIRM: the organisation's actual retention schedule for unsuccessful
    | applicants and for hired employees. A period must be stated or be
    | determinable — "indefinitely" is not a lawful answer.
    */
    'retention' => [
        'statement' => null,              // CONFIRM
        'confirm'   => true,
    ],

    /*
    | Lawful basis for processing.
    | CONFIRM with legal which criterion is being relied on. The candidates
    | under RA 10173 Sec. 12 are typically consent, or steps taken at the data
    | subject's request prior to entering a contract, or legitimate interests.
    | Sensitive personal information (Sec. 13) has a separate, narrower set.
    */
    'lawful_basis' => [
        'statement' => null,              // CONFIRM
        'confirm'   => true,
    ],

    /* Plain-language purposes. Reviewed as policy, not configuration. */
    'purposes' => [
        'To receive and assess your application for the position you applied to.',
        'To contact you about your application, including scheduling interviews.',
        'To verify the information and credentials you provide.',
        'To keep your applicant profile so you can apply to other positions without starting again.',
        'To form the basis of your employee record if you are hired.',
    ],

    /* Categories of information collected through the portal. */
    'collected' => [
        'Your name, contact details, and other personal particulars.',
        'Your address and place of birth.',
        'Your educational background, work history, skills and references.',
        'Documents you upload, such as your résumé and 2x2 picture.',
        'Government identification numbers, where you choose to provide them.',
        'Your answers to assessments, if and when HR provides them to you.',
    ],

    /*
    | Data subject rights, RA 10173 Sec. 16 and 18. Wording follows the statute
    | rather than being paraphrased loosely.
    */
    'rights' => [
        'To be informed whether your personal information is being processed.',
        'To access the personal information we hold about you.',
        'To have inaccurate or incomplete information corrected.',
        'To object to processing, or withdraw consent where consent is the basis.',
        'To have your information erased or blocked where the law allows.',
        'To be indemnified for damages arising from inaccurate, unlawfully obtained or unauthorised use of your information.',
        'To data portability, where applicable.',
        'To lodge a complaint with the National Privacy Commission.',
    ],

    /* National Privacy Commission — the statutory complaints route. */
    'npc' => [
        'name'    => 'National Privacy Commission',
        'website' => 'https://privacy.gov.ph',
    ],
];
