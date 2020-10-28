<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API ENDPOINT
    |--------------------------------------------------------------------------
    |
    | Endpoint for newsletter subscriptions
    */

    'endpoint' => env('NEWSLETTER_BANNER_API_ENDPOINT', ''),

    /*
    |--------------------------------------------------------------------------
    | USE XHR
    |--------------------------------------------------------------------------
    |
    | API ENDPOINT can be requested by XHR (1) or regular form submission (2)
    */

    'use_xhr' => !!env('NEWSLETTER_BANNER_USE_XHR', true),

    /*
    |--------------------------------------------------------------------------
    | REQUEST METHOD
    |--------------------------------------------------------------------------
    |
    | POST or GET
    */

    'request_method' => env('NEWSLETTER_BANNER_REQUEST_METHOD', 'POST'),

    /*
    |--------------------------------------------------------------------------
    | REQUEST BODY
    |--------------------------------------------------------------------------
    |
    | available options: form-data, x-www-form-urlencoded, raw-json
    */

    'request_body' => env('NEWSLETTER_BANNER_REQUEST_BODY', 'form-data'),

    /*
    |--------------------------------------------------------------------------
    | REQUEST HEADERS
    |--------------------------------------------------------------------------
    |
    |
    */

    'request_headers' => json_decode(env('NEWSLETTER_BANNER_REQUEST_HEADERS', /** @lang JSON */ '
        {
            "Content-Type": "multipart/form-data"
        }
    ')),

    /*
    |--------------------------------------------------------------------------
    | PARAMS TRANSPOSITION
    |--------------------------------------------------------------------------
    |
    | Specify params transposition according to your endpoint implementation
    */

    'params_tr' => json_decode(env('NEWSLETTER_BANNER_PARAMS_TR', /** @lang JSON */ '
        {
            "email": "email",
            "newsletter_id": "newsletter_id",
            "source": "source"
        }
    ')),

    /*
    |--------------------------------------------------------------------------
    | EXTRA PARAMS
    |--------------------------------------------------------------------------
    |
    | These params will be added to every request.
    | Do not use any names from NEWSLETTER_BANNER_PARAMS_TR to avoid conflicts
    |
    */

    'params_extra' => json_decode(env('NEWSLETTER_BANNER_PARAMS_EXTRA', /** @lang JSON */ '
        {         
        }
    ')),

];
