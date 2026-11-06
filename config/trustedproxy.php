<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Comma separated addresses of the reverse proxies in front of this
    | application, so client IPs and HTTPS are detected correctly. Leave empty
    | when the application is reached directly.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
