<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Facebook app information
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Facebook app's information
    | refer to Facebook SDK page for more information
    |
    */
    'app_id' => env('FACEBOOK_APP_ID', null),
    'app_secret' => env('FACEBOOK_APP_SECRET', NULL),
    'default_graph_version' => env('FACEBOOK_DEFAULT_GRAPH_VERSION', 'v3.2'),
    'permission' => explode(',', env('FACEBOOK_PERMISSION', 
        'email,publish_pages,manage_pages,publish_pages,pages_messaging'))
];