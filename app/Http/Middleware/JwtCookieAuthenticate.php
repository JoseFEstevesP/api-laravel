<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtCookieAuthenticate
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    try {
      // Try to authenticate the user. The JWTAuth::parseToken() will try the header by default.
      // The authenticate() method will set the user on the auth guard.
      if (JWTAuth::parseToken()->authenticate()) {
        return $next($request);
      }
    } catch (JWTException $e) {
      // If the token is not in the header, or is invalid, we'll try the cookie.
    }

    // If token wasn't found in header, try to get it from the 'accessToken' cookie
    $cookieToken = $request->cookie('accessToken');

    if ($cookieToken) {
      try {
        // Set the token for this request and authenticate
        JWTAuth::setToken($cookieToken);
        JWTAuth::authenticate();

        // If we got here, the user is authenticated.
        return $next($request);
      } catch (JWTException $e) {
        // Token from cookie is invalid. Fall through to the final error response.
      }
    }

    // If all attempts fail, return a 401 Unauthorized response.
    return response()->json(
      [
        'statusCode' => 401,
        'timestamp' => now()->toIso8601String(),
        'path' => $request->fullUrl(),
        'message' => 'No autorizado.',
      ],
      401,
    );
  }
}
