<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Mode of injection
    |--------------------------------------------------------------------------
    |
    | Specify which injection to use in your application. By default Larapoke
    | will look into all your Responses for CSRF tokens and add the script
    | but you can change it so you can have more control where it goes.
    |
    | Supported: "auto", "middleware", "manual",
    |
    */

    'mode' => env('LARAPOKE_MODE', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Times
    |--------------------------------------------------------------------------
    |
    | You can set by how much times in the session lifetime the poking will be
    | made to your application. For example, the default 120 minutes session
    | lifetime, divided by 4 times, means poking at a 30 minutes intervals.
    |
    */

    'times' => 4,

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | You can force a page reload if the session lifetime exceeds the last
    | successful poke. This hides the `TokenMismatchException` from the
    | User, and instead allows to retake the session transparently.
    |
    */

    'timeout' => true,

    /*
    |--------------------------------------------------------------------------
    | Route for Poking
    |--------------------------------------------------------------------------
    |
    | Here you may specify how the poking route will live in your application.
    | You can set a specific route to be hit, a custom name to identify it,
    | and a custom subdomain if you don't want to be available app-wide.
    |
    */

    'poking' => [
        'route' => 'poke',
        'name' => 'larapoke',
        'domain' => null,
        'middleware' => ['web'],
    ]


];