<?php

/*
|--------------------------------------------------------------------------
| Applicant help — the floating help bubble on the public job pages
|--------------------------------------------------------------------------
|
| Deliberately small: a short FAQ and how to reach HR. There is no messaging
| system behind it. A direct HR contact channel (chat, a request form) can be
| added later as another entry in 'hr' — the bubble lists whatever is here.
|
| Only what is set is shown. Set the HR contact details in .env.
*/

return [

    'hr' => [
        'email' => env('HR_CONTACT_EMAIL'),
        'phone' => env('HR_CONTACT_PHONE'),
        'hours' => env('HR_CONTACT_HOURS'),
    ],

    /* Question => answer. Keep answers true to how the portal works. */
    'faq' => [
        'How do I apply?' =>
            'Open a position and press Apply. New here? Create your account first. Then add your CV and a 2x2 picture and submit — a cover letter is optional.',

        'What documents do I need to apply?' =>
            'Only your CV or résumé and a 2x2 picture (PDF, JPG or PNG, up to 5 MB each). A cover letter is optional. Other documents, such as a PSA birth certificate or NBI clearance, are requested later by HR if you move forward.',

        'Can I apply for more than one position?' =>
            'Yes. Your profile and documents are kept, so applying for another position only takes a moment.',

        'Where can I see my application?' =>
            'Sign in and open My Applications. It shows every position you applied for and where each one stands.',

        'I have a problem with my account or a document' =>
            'Contact HR using the details under HR assistance, and include the email address you registered with.',
    ],

];
