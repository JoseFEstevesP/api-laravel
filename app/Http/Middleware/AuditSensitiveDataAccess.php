<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditSensitiveDataAccess
{
  /**
   * List of sensitive routes/endpoints that should be audited
   * This can be configured based on your specific application needs
   */
  protected $sensitiveRoutes = [
    'api/users*', // User data access
    'api/configs*', // Configuration data
    'api/permissions*', // Permission data
    'api/roles*', // Role data
    'api/profile*', // Profile data
    'api/admin*', // Admin functions
    'api/financial*', // Financial data
    'api/personal*', // Personal information
    'api/sensitive*', // General sensitive endpoints
  ];

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);

    // Check if this request is for a sensitive route
    if ($this->isSensitiveRoute($request)) {
      $this->logSensitiveDataAccess($request, $response);
    }

    return $response;
  }

  /**
   * Check if the current route is considered sensitive
   *
   * @param  \Illuminate\Http\Request  $request
   * @return bool
   */
  protected function isSensitiveRoute(Request $request): bool
  {
    $currentPath = $request->path();

    foreach ($this->sensitiveRoutes as $route) {
      // Convert wildcard routes to regex patterns
      $pattern = str_replace('*', '.*', $route);
      $pattern = str_replace('/', '\/', $pattern); // Escape forward slashes
      $pattern = '/^' . $pattern . '$/';

      if (preg_match($pattern, $currentPath)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Log the sensitive data access
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Symfony\Component\HttpFoundation\Response  $response
   * @return void
   */
  protected function logSensitiveDataAccess(
    Request $request,
    Response $response,
  ): void {
    try {
      $user = Auth::user();

      // Determine if this is a data retrieval or modification
      $eventType = $this->determineEventType($request);

      // Using the new AuditLogger service for consistent audit logging
      \App\Modules\Audit\Services\AuditLogger::log(
        $eventType,
        [
          'path' => $request->path(),
          'method' => $request->method(),
          'status_code' => $response->getStatusCode(),
          'response_size' => strlen($response->getContent()),
          'parameters' => $request->except(['password', 'token', 'api_token']),
          'ip_address' => $this->getIpAddress($request),
          'user_agent' => $request->userAgent(),
          'url' => $request->fullUrl(),
          'request_context' => [
            'referer' => $request->header('referer'),
            'locale' => app()->getLocale(),
            'route' => $request->route() ? $request->route()->getName() : null,
            'method' => $request->method(),
            'path' => $request->path(),
          ],
          'action_context' => [
            'controller' => $request->route()
              ? get_class($request->route()->getController())
              : null,
            'action' => $request->route()
              ? $request->route()->getActionMethod()
              : null,
            'parameters' => $request->route()
              ? $request->route()->parameters()
              : [],
          ],
          'severity' => $this->determineSeverity($request),
          'session_id' => session()->getId() ?? null,
        ],
        $user ? $user->getAuthIdentifier() : null,
        'sensitive_data_access',
        null,
        'Sensitive data access event',
      );
    } catch (\Exception $e) {
      // Log the error but don't fail the request
      Log::error(
        'Failed to log sensitive data access audit: ' . $e->getMessage(),
      );
    }
  }

  /**
   * Determine the event type based on the HTTP method
   *
   * @param  \Illuminate\Http\Request  $request
   * @return string
   */
  protected function determineEventType(Request $request): string
  {
    $method = strtoupper($request->method());

    switch ($method) {
      case 'GET':
        return 'data_retrieval';
      case 'POST':
        return 'data_creation';
      case 'PUT':
      case 'PATCH':
        return 'data_modification';
      case 'DELETE':
        return 'data_deletion';
      default:
        return 'data_access';
    }
  }

  /**
   * Determine the severity of the access based on the action and data type
   *
   * @param  \Illuminate\Http\Request  $request
   * @return string
   */
  protected function determineSeverity(Request $request): string
  {
    $path = $request->path();
    $method = strtoupper($request->method());

    // Critical for financial data modifications
    if (
      str_contains($path, 'financial') &&
      in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])
    ) {
      return 'critical';
    }

    // High for user/profile modifications
    if (
      (str_contains($path, 'users') || str_contains($path, 'profile')) &&
      in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])
    ) {
      return 'high';
    }

    // Medium for most other sensitive data access
    return 'medium';
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
