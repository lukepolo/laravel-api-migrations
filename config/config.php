<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Migrations Path
    |--------------------------------------------------------------------------
    |
    | You can change the location of the path
    |
    */

    'path' => 'Http/ApiMigrations',

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | You can customize your headers that you want to send in with each request
    |
    */

    'headers' => [
        'current-version'  => 'x-api-current-version',
        'request-version'  => 'x-api-request-version',
        'response-version' => 'x-api-response-version',
    ],

    /*
    |--------------------------------------------------------------------------
    | Current Version
    |--------------------------------------------------------------------------
    |
    | This is the version users will be defaulted to. If you do not set
    | a version the latest version will be used.
    |
    */

    'current_versions' => [
//        'V1' => '2017-01-31',
    ],

    /*
    |--------------------------------------------------------------------------
    | Version Pinning
    |--------------------------------------------------------------------------
    |
    | When a user does not have a version set , we will set the latest version
    |
    */

    'version_pinning' => false,

];
