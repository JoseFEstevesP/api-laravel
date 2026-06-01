<?php

namespace App\Http\Middleware;

use App\Modules\Session\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ValidateActiveSession
{
  /**
   * Handle an incoming request to validate that the authenticated user has an active session.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    try {
      // First, authenticate the user using JWT (similar to JwtCookieAuthenticate)
      $user = null;

      // Try to get the user from the header token first
      try {
        if (JWTAuth::parseToken()->authenticate()) {
          $user = JWTAuth::parseToken()->authenticate();
        }
      } catch (JWTException $e) {
        // If header token failed, try cookie token
        $cookieToken = $request->cookie('accessToken');
        if ($cookieToken) {
          try {
            JWTAuth::setToken($cookieToken);
            $user = JWTAuth::authenticate();
          } catch (JWTException $e) {
            // Both tokens failed, user is not authenticated
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
        } else {
          // No token found in header or cookie
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

      // If we have an authenticated user, check if they have an active session
      if ($user) {
        $userId = $user->getAuthIdentifier();

        // Check if the user has an active session in the database
        if (!UserSession::hasActiveSession($userId)) {
          return response()->json(
            [
              'statusCode' => 401,
              'timestamp' => now()->toIso8601String(),
              'path' => $request->fullUrl(),
              'message' =>
                'La sesión no está activa. Por favor inicie sesión nuevamente.',
            ],
            401,
          );
        }

        // Update the last activity for the user's session
        $activeSession = UserSession::getActiveSession($userId);
        if ($activeSession) {
          $activeSession->updateLastActivity();
        }
      }

      return $next($request);
    } catch (JWTException $e) {
      return response()->json(
        [
          'statusCode' => 401,
          'timestamp' => now()->toIso8601String(),
          'path' => $request->fullUrl(),
          'message' => 'Token inválido o expirado.',
        ],
        401,
      );
    }
  }
}
