<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Middleware para auditar todas las acciones de los usuarios
 *
 * Este middleware registra todas las acciones de los usuarios en el sistema de auditoría
 * para mantener un registro detallado de lo que cada usuario hace en la aplicación.
 */
class AuditUserActions
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {
    $response = $next($request);

    try {
      // Solo auditar si el usuario está autenticado
      if (Auth::check()) {
        $user = Auth::user();

        // Registrar la acción del usuario en archivo de log de auditoría
        $auditEntry = [
          'timestamp' => now()->toISOString(),
          'user_type' => get_class($user),
          'user_id' => $user->getAuthIdentifier(),
          'event' => 'action_performed', // Evento genérico para acciones de usuario
          'auditable_type' => 'action',
          'auditable_id' => null, // No apunta a un modelo específico
          'old_values' => null, // Para acciones generales, no hay valores antiguos
          'new_values' => [
            'action' => $request->method() . ' ' . $request->path(),
            'action_at' => now()->toISOString(),
            'input_data' => $this->sanitizeInput($request->all()), // Datos enviados en la solicitud
          ],
          'url' => $request->fullUrl(),
          'ip_address' => $this->getIpAddress($request),
          'user_agent' => $request->userAgent(),
          'tags' => 'security,action,audit',
          'request_context' => [
            'referer' => $request->header('referer'),
            'locale' => app()->getLocale(),
            'method' => $request->method(),
            'path' => $request->path(),
            'session_id' => session()->getId() ?? null,
          ],
          'action_context' => [
            'controller' => $request->route()
              ? get_class($request->route()->getController())
              : null,
            'action' => $request->route()
              ? $request->route()->getActionMethod()
              : null,
          ],
          'severity' => 'low', // La mayoría de las acciones son de baja severidad
          'session_id' => session()->getId() ?? null,
        ];

        // Using the new AuditLogger service for consistent audit logging
        \App\Modules\Audit\Services\AuditLogger::log(
          'action_performed',
          [
            'action' => $request->method() . ' ' . $request->path(),
            'input_data' => $this->sanitizeInput($request->all()),
            'url' => $request->fullUrl(),
            'ip_address' => $this->getIpAddress($request),
            'user_agent' => $request->userAgent(),
            'request_context' => [
              'referer' => $request->header('referer'),
              'locale' => app()->getLocale(),
              'method' => $request->method(),
              'path' => $request->path(),
              'session_id' => session()->getId() ?? null,
            ],
            'action_context' => [
              'controller' => $request->route()
                ? get_class($request->route()->getController())
                : null,
              'action' => $request->route()
                ? $request->route()->getActionMethod()
                : null,
            ],
            'session_id' => session()->getId() ?? null,
          ],
          $user->getAuthIdentifier(),
          'action',
          null,
          'User performed an action',
        );
      }
    } catch (\Exception $e) {
      // Registrar errores de auditoría pero no interrumpir la solicitud
      Log::error('Failed to audit user action: ' . $e->getMessage());
    }

    return $response;
  }

  /**
   * Sanitize input data to remove sensitive information
   *
   * @param array $input
   * @return array
   */
  private function sanitizeInput(array $input): array
  {
    $sensitiveFields = [
      'password',
      'clave',
      'token',
      'secret',
      'key',
      'auth',
      'pwd',
      'pass',
    ];
    $sanitized = [];

    foreach ($input as $key => $value) {
      if (is_array($value)) {
        // Recursively sanitize nested arrays
        $sanitized[$key] = $this->sanitizeInput($value);
      } elseif (in_array(strtolower($key), $sensitiveFields)) {
        // Replace sensitive values with a placeholder
        $sanitized[$key] = '[REDACTED]';
      } else {
        $sanitized[$key] = $value;
      }
    }

    return $sanitized;
  }

  /**
   * Get the IP address from the request, considering proxies
   *
   * @param Request $request
   * @return string|null
   */
  private function getIpAddress(Request $request): ?string
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
