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
    | Available options: x-www-form-urlencoded (default), form-data, json
    */

    'request_body' => env('NEWSLETTER_BANNER_REQUEST_BODY', 'x-www-form-urlencoded'),

    /*
    |--------------------------------------------------------------------------
    | REQUEST HEADERS
    |--------------------------------------------------------------------------
    |
    | Add any HTTP header you need (JSON)
    | Not applicable if used with form-data `request_body`, use `params_extra` instead.
    */

    'request_headers' => json_decode(env('NEWSLETTER_BANNER_REQUEST_HEADERS', /** @lang JSON */ '
        {
        }
    '), null, 512, JSON_THROW_ON_ERROR),

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
    '), null, 512, JSON_THROW_ON_ERROR),

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
    '), null, 512, JSON_THROW_ON_ERROR),

    /*
    |--------------------------------------------------------------------------
    | RESPONSE SUCCESS
    |--------------------------------------------------------------------------
    |
    | JSON with following items:
    | `status_param` - name of param in response JSON containing status
    | `status_param_value` - value that denotes failed request
    | `message_param` - name of param in response JSON containing status message
    |
    */

    'response_failure' => json_decode(env('NEWSLETTER_BANNER_RESPONSE_FAILURE', /** @lang JSON */ '
        {
            "status_param": "status",
            "status_param_value": "error",
            "message_param": "message"
        } 
    '), null, 512, JSON_THROW_ON_ERROR),

];
