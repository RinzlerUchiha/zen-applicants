<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        | Applicant documents (résumé, 2x2 picture, cover letter). Private.
        |
        | Separate from the shared 's3' disk on purpose: profile photos,
        | licences and certificates keep their existing behaviour untouched.
        |
        | Local development stores files on this machine. Production uses the
        | company bucket under the zenhub/ prefix, with credentials that belong
        | to this application alone — never zen-admin's AWS_* keys. Leave the
        | key and secret empty to use the server's IAM role instead.
        */
        'applicant_documents' => env('APPLICANT_DOCUMENTS_DRIVER', 'local') === 's3'
            ? [
                'driver' => 's3',
                'key' => env('APPLICANT_DOCUMENTS_AWS_ACCESS_KEY_ID'),
                'secret' => env('APPLICANT_DOCUMENTS_AWS_SECRET_ACCESS_KEY'),
                'region' => env('APPLICANT_DOCUMENTS_AWS_REGION', 'ap-southeast-1'),
                'bucket' => env('APPLICANT_DOCUMENTS_AWS_BUCKET', 'e-classtngcacademy'),
                'root' => env('APPLICANT_DOCUMENTS_AWS_ROOT', 'zenhub'),
                // S3 keys always use "/". Without this Laravel joins the root
                // with the host OS separator, giving "zenhub\applicant/…" on
                // Windows.
                'directory_separator' => '/',
                // Uploads carry the S3 driver's default "private" ACL, exactly as
                // zen-admin's existing uploads do — so the IAM policy for this
                // app needs s3:PutObjectAcl alongside s3:PutObject.
                'throw' => true,
                'report' => false,
            ]
            : [
                'driver' => 'local',
                'root' => env('APPLICANT_DOCUMENTS_LOCAL_ROOT', storage_path('app/private')),
                'throw' => true,
                'report' => false,
            ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
