<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sample account passwords (local and staging only)
    |--------------------------------------------------------------------------
    |
    | Used by SampleAccountSeeder. Set these in .env so the sample accounts
    | keep the same password every time the seeder runs. Leave them empty to
    | get random passwords instead. The seeder never runs in production.
    |
    */

    'passwords' => [
        'admin' => env('SAMPLE_ADMIN_PASSWORD'),
        'editor' => env('SAMPLE_EDITOR_PASSWORD'),
        'reviewer' => env('SAMPLE_REVIEWER_PASSWORD'),
        'author' => env('SAMPLE_AUTHOR_PASSWORD'),
    ],

];
