<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditAuthorization
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Process the request first
    $response = $next($request);

    // Check for authorization actions in the response or after request processing
    $this->auditAuthorizationEvents($request, $response);

    return $response;
  }

  /**
   * Audit authorization events that occurred during the request
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Symfony\Component\HttpFoundation\Response  $response
   * @return void
   */
  protected function auditAuthorizationEvents(
    Request $request,
    Response $response,
  ): void {
    try {
      $user = Auth::user();

      // This is a simplified approach - in a real application you'd want to
      // track authorization checks throughout the request lifecycle
      // For now, we'll check if the route required authorization

      $route = $request->route();
      if (!$route) {
        return;
      }

      // Check if this route has authorization middleware applied
      $middleware = $route->gatherMiddleware();
      $hasAuthMiddleware = $this->hasAuthorizationMiddleware($middleware);

      if ($hasAuthMiddleware) {
        // Log the authorization check event to a file
        $auditData = [
          'timestamp' => now()->toIso8601String(),
          'user_type' => $user ? get_class($user) : null,
          'user_id' => $user ? $user->getAuthIdentifier() : null,
          'event' => 'authorization_check',
          'auditable_type' => 'route_authorization',
          'auditable_id' => null,
          'old_values' => null,
          'new_values' => [
            'route' => $route->getName() ?? $request->path(),
            'middleware' => $middleware,
            'result' =>
              $response->getStatusCode() === 403 ? 'denied' : 'granted',
            'status_code' => $response->getStatusCode(),
          ],
          'url' => $request->fullUrl(),
          'ip_address' => $this->getIpAddress($request),
          'user_agent' => $request->userAgent(),
          'tags' =>
            'security,authorization,' .
            ($response->getStatusCode() === 403 ? 'denied' : 'granted'),
          'request_context' => [
            'referer' => $request->header('referer'),
            'locale' => app()->getLocale(),
            'route' => $route->getName() ?? null,
            'method' => $request->method(),
            'path' => $request->path(),
          ],
          'action_context' => [
            'controller' => $route->getController()
              ? get_class($route->getController())
              : null,
            'action' => $route->getActionMethod() ?? null,
            'parameters' => $route->parameters(),
          ],
          'severity' => $response->getStatusCode() === 403 ? 'high' : 'low',
          'session_id' => session()->getId() ?? null,
        ];

        \App\Modules\Audit\Services\AuditLogger::log(
          'authorization_check',
          [
            'route' => $route->getName() ?? $request->path(),
            'middleware' => $middleware,
            'result' =>
              $response->getStatusCode() === 403 ? 'denied' : 'granted',
            'status_code' => $response->getStatusCode(),
            'ip_address' => $this->getIpAddress($request),
            'user_agent' => $request->userAgent(),
            'request_context' => [
              'referer' => $request->header('referer'),
              'locale' => app()->getLocale(),
              'route' => $route->getName() ?? null,
              'method' => $request->method(),
              'path' => $request->path(),
            ],
            'action_context' => [
              'controller' => $route->getController()
                ? get_class($route->getController())
                : null,
              'action' => $route->getActionMethod() ?? null,
              'parameters' => $route->parameters(),
            ],
            'session_id' => session()->getId() ?? null,
          ],
          $user ? $user->getAuthIdentifier() : null,
          'route_authorization',
          null,
          'Authorization check event',
        );
      }
    } catch (\Exception $e) {
      // Log the error but don't fail the request
      Log::error('Failed to log authorization audit: ' . $e->getMessage());
    }
  }

  /**
   * Check if the middleware list contains authorization middleware
   *
   * @param  array  $middleware
   * @return bool
   */
  protected function hasAuthorizationMiddleware(array $middleware): bool
  {
    foreach ($middleware as $middlewareItem) {
      if (is_string($middlewareItem)) {
        // Check for common authorization middleware names
        if (
          str_contains($middlewareItem, 'auth') ||
          str_contains($middlewareItem, 'can') ||
          str_contains($middlewareItem, 'permission') ||
          str_contains($middlewareItem, 'role')
        ) {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Get the IP address from the request, considering proxies.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return string|null
   */
  protected function getIpAddress(Request $request): ?string
  {
    $keys = [
      'X-Forwarded-For',
      'X-Real-IP',
      'CF-Connecting-IP',
      'True-Client-IP',
      'X-Cluster-Client-IP',
    ];

    foreach ($keys as $key) {
      if ($request->header($key)) {
        $ip = $request->header($key);
        if ($key === 'X-Forwarded-For') {
          $ips = explode(',', $ip);
          $ip = trim($ips[0]);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
          return $ip;
        }
      }
    }

    return $request->ip();
  }
}
