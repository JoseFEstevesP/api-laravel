<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Refresh Token Secret
    |--------------------------------------------------------------------------
    |
    | This key is used to sign the refresh tokens.
    |
    */
  'secret' => env('JWT_REFRESH_SECRET'),

  /*
    |--------------------------------------------------------------------------
    | Refresh Token Issuer
    |--------------------------------------------------------------------------
    |
    | The issuer of the token.
    |
    */
  'issuer' => env('APP_URL'),

  /*
    |--------------------------------------------------------------------------
    | Refresh time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the refresh token
    | will be valid for.
    |
    */
  'ttl' => env('JWT_REFRESH_TTL', 20160), // Defaults to 2 weeks
];
