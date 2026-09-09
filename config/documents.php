<?php

/*
|--------------------------------------------------------------------------
| Applicant Document Upload
|--------------------------------------------------------------------------
|
| Configuration for the applicant-facing document upload feature
| (HireFlow Phase 2.5, Milestone 1).
|
| The document type list lives here rather than in a table because nothing
| in this milestone manages it — there is no HR-facing UI to add or remove
| types. When the completeness / missing-document workflow is built
| (Milestone 2) and HR needs to define which documents are *required*, that
| is the point to decide whether this becomes a maintained table.
|
*/

return [

    /*
    | Selectable document types, keyed by the value stored in
    | tblapp_documents.doc_type. Keys are stable identifiers — change a
    | label freely, but changing a key orphans existing rows.
    |
    | 'other' is special: it is the only type that requires the applicant
    | to supply their own label (see DocumentController::store()).
    */
    'types' => [
        'psa_birth_certificate' => 'PSA Birth Certificate',
        'nbi_police_clearance'  => 'NBI / Police Clearance',
        'tor_diploma'           => 'TOR / Diploma',
        'certificate_employment' => 'Certificate of Employment',
        'government_ids'        => 'Government IDs (SSS / PhilHealth / Pag-IBIG / TIN)',
        'id_photo'              => 'ID Photo',
        'resume_cv'             => 'Résumé / CV',
        'other'                 => 'Other',
    ],

    /*
    | The type whose label is supplied by the applicant instead of taken
    | from the list above.
    */
    'other_type' => 'other',

    /*
    | Accepted uploads.
    |
    | 'extensions' drives Laravel's mimes: rule, which validates against the
    | file's actual content rather than its name. 'mimes' is the allow-list
    | for the independent server-side recheck in FileService::storeDocument()
    | and also decides the stored extension — a file's own name never does.
    |
    | Deliberately excluded: SVG (can carry script), and all archive and
    | office formats (no reason to accept an executable container for a
    | birth certificate).
    */
    'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],

    'mimes' => [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
    ],

    /* Maximum upload size in kilobytes. Matches the clearance-attachment
       precedent in zen-admin (5 MB). */
    'max_size_kb' => 5120,

    /* Storage folder, relative to the disk root. The applicant's app_id is
       appended as a subfolder so a directory listing cannot enumerate
       across applicants. */
    'path' => 'applicant/documents',

];
