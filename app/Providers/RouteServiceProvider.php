<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
  public const HOME = '/home';

  public function boot(): void
  {
    // Auth: strict limits for login/refresh
    RateLimiter::for('auth', function (Request $request) {
      return Limit::perMinute(5)
        ->by($request->input('email') ?: $request->ip())
        ->response(function () {
          return response()->json([
            'success' => false,
            'error' => [
              'code' => 429,
              'name' => 'Too Many Requests',
              'message' => 'Demasiados intentos de autenticación. Intente nuevamente en 1 minuto.',
            ],
          ], 429);
        });
    });

    // Auth refresh: slightly more permissive
    RateLimiter::for('auth-refresh', function (Request $request) {
      return Limit::perMinute(10)->by($request->ip());
    });

    // General API
    RateLimiter::for('api', function (Request $request) {
      return Limit::perMinute(60)
        ->by($request->user()?->uid ?: $request->ip())
        ->response(function () {
          return response()->json([
            'success' => false,
            'error' => [
              'code' => 429,
              'name' => 'Too Many Requests',
              'message' => 'Demasiadas solicitudes. Intente nuevamente en 1 minuto.',
            ],
          ], 429);
        });
    });

    // Session: check endpoint can be called more often
    RateLimiter::for('session', function (Request $request) {
      return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
    });

    // Audit: read-only, generous
    RateLimiter::for('audit', function (Request $request) {
      return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
    });

    $this->routes(function () {
      Route::middleware('api')->group(base_path('routes/modules.php'));
    });
  }
}
