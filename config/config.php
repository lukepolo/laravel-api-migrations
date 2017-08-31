<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | You can customize your headers that you want to send in with each request
    |
    */

    'headers'         => [
        'api-version'  => 'x-api-request-version',
        'current-version'  => 'x-api-current-version',
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

    'current_version' => '',

];