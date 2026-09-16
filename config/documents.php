<?php

/*
|--------------------------------------------------------------------------
| Applicant Documents
|--------------------------------------------------------------------------
|
| HireFlow Phase 2.5 — Milestone 1 (upload) and Milestone 2 (HR completeness
| check and missing-document flow).
|
| zen-admin keeps a mirror of the parts HR needs in
| zen-admin/config/applicant_documents.php. The keys below — document types
| and review reasons — are stored in the database and must match in both.
|
*/

return [

    /*
    | Every type a stored row may carry, keyed by tblapp_documents.doc_type.
    | Keys are stable identifiers — change a label freely, but changing a key
    | orphans existing rows.
    |
    | There is deliberately no generic "Other" type: what an applicant can send
    | is controlled here, not typed in by them.
    */
    'types' => [
        'resume_cv'              => 'Résumé / CV',
        'picture_2x2'            => '2x2 Picture',
        'cover_letter'           => 'Cover Letter',
        'psa_birth_certificate'  => 'PSA Birth Certificate',
        'nbi_police_clearance'   => 'NBI / Police Clearance',
        'tor_diploma'            => 'TOR / Diploma',
        'certificate_employment' => 'Certificate of Employment',
        'government_ids'         => 'Government IDs (SSS / PhilHealth / Pag-IBIG / TIN)',
    ],

    /*
    | The application stage: an applicant submits a résumé and a 2x2 picture,
    | with a cover letter optional. These three are the only types an applicant
    | can upload and the only types HR reviews at this stage.
    */
    'required' => ['resume_cv', 'picture_2x2'],
    'optional' => ['cover_letter'],

    /*
    | Pre-employment requirements. Defined so the types exist for a later stage
    | and for the eventual 201 file, but not accepted or requested now.
    */
    'later_stage' => [
        'psa_birth_certificate',
        'nbi_police_clearance',
        'tor_diploma',
        'certificate_employment',
        'government_ids',
    ],

    /*
    | HR's check of the document currently on file. One state per document:
    | replacing a document resets it to pending, so an earlier decision never
    | carries over to a file HR has not looked at.
    */
    'review_statuses' => [
        'pending'  => 'Waiting for HR to check',
        'accepted' => 'Accepted',
        'rejected' => 'Needs replacement',
    ],

    /*
    | Why a document needs replacing. The wording is what the applicant reads,
    | so each one says what to fix. Mirrored in zen-admin, which also records
    | which document types each reason applies to.
    */
    'review_reasons' => [
        'unreadable'         => 'The file is unclear or unreadable — it may be blurry, too dark, cut off, or not opening.',
        'wrong_document'     => 'This isn\'t the document we asked for.',
        'incomplete'         => 'Part of the document is missing, such as pages or key details.',
        'outdated'           => 'The document isn\'t up to date.',
        'photo_requirements' => 'The picture doesn\'t meet the 2x2 ID photo requirements.',
        'details_mismatch'   => 'The details don\'t match your application, such as your name or the position or company it\'s addressed to.',
        'other'              => 'HR has asked for a replacement.',
    ],

    /*
    | Accepted uploads.
    |
    | 'extensions' drives Laravel's mimes: rule, which validates against the
    | file's actual content rather than its name. 'mimes' is the allow-list
    | for the independent server-side recheck in FileService::storeDocument()
    | and also decides the stored extension — a file's own name never does.
    |
    | Deliberately excluded: SVG (can carry script), and all archive and
    | office formats.
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

    /*
    | Where documents live. The disk is defined in config/filesystems.php —
    | local on a development machine, the company S3 bucket in production — and
    | is private either way. Keys are "{path}/{app_id}/{random}.{ext}" relative
    | to the disk root, so on S3 they land under zenhub/applicant/documents/.
    */
    'disk' => 'applicant_documents',
    'path' => 'applicant/documents',

];
