<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    /*
    |--------------------------------------------------------------------------
    | Tenant & Account Status Lines
    |--------------------------------------------------------------------------
    |
    | Used by AuthenticatedSessionController. Follows the tenancy contract in
    | docs/backend-foundation.md: inactive users and inactive tenants
    | must not obtain a session.
    |
    */

    'inactive' => 'This account is currently inactive. Please contact your network administrator.',
    'tenant_inactive' => 'The ISP code is invalid or currently inactive.',

];
