<?php

namespace App\Modules\Rol\Middleware;

use App\Modules\Rol\Enums\Permission;
use App\Modules\User\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissionMiddleware
{
  /**
   * Handle an incoming request.
   *
   * Verifica que el usuario autenticado tenga el permiso requerido.
   * Si el permiso es SUPER, permite el acceso sin restricciones.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string  $permission  Permiso requerido (formato: "module.action")
   * @return mixed
   */
  public function handle(Request $request, Closure $next, string $permission)
  {
    try {
      $token = JWTAuth::parseToken();
      $payload = $token->getPayload();
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => ['message' => 'Token no proporcionado o inválido.'],
        ],
        401,
      );
    }

    $permissions = $payload->get('permissions', []);

    // Si tiene permiso SUPER, permitir acceso sin restricciones
    if (in_array(Permission::SUPER->value, $permissions, true)) {
      return $next($request);
    }

    // Verificar permiso específico
    if (!in_array($permission, $permissions, true)) {
      return response()->json(
        [
          'all' => ['message' => "No tienes permiso para: {$permission}"],
        ],
        403,
      );
    }

    return $next($request);
  }

  /**
   * Register the middleware alias in the Kernel.
   *
   * @param  \Illuminate\Routing\Router  $router
   * @return void
   */
  public static function register($router)
  {
    $router->aliasMiddleware('permission', self::class);
  }
}
