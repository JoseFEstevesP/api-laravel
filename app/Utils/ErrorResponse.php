<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class ErrorResponse
 *
 * Proporciona métodos estándar para formatear respuestas de error
 * consistentes en toda la aplicación.
 */
class ErrorResponse
{
  /**
   * Retorna una respuesta JSON de error estándar
   *
   * @param string $message Mensaje de error
   * @param int $statusCode Código de estado HTTP
   * @param array $additionalData Datos adicionales para incluir en la respuesta
   * @param Throwable|null $exception Excepción original (opcional)
   * @return JsonResponse
   */
  public static function make(
    string $message,
    int $statusCode = 500,
    array $additionalData = [],
    ?Throwable $exception = null,
  ): JsonResponse {
    $response = [
      'message' => $message,
      'statusCode' => $statusCode,
      'timestamp' => now()->toIso8601String(),
      'path' => request()->fullUrl(),
    ];

    // Agregar datos adicionales si existen
    if (!empty($additionalData)) {
      $response = array_merge($response, $additionalData);
    }

    // En entornos de desarrollo, incluir detalles del error
    if (app()->environment('local', 'development') && $exception) {
      $response['error_details'] = [
        'exception' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
      ];
    }

    return response()->json($response, $statusCode);
  }

  /**
   * Retorna una respuesta de error de validación
   *
   * @param array $errors Errores de validación
   * @param string $message Mensaje general (opcional)
   * @return JsonResponse
   */
  public static function validation(
    array $errors,
    string $message = 'Error de validación',
  ): JsonResponse {
    return response()->json(
      [
        'message' => $message,
        'statusCode' => 422,
        'errors' => $errors,
        'timestamp' => now()->toIso8601String(),
        'path' => request()->fullUrl(),
      ],
      422,
    );
  }

  /**
   * Retorna una respuesta de error de recurso no encontrado
   *
   * @param string $message Mensaje de error
   * @return JsonResponse
   */
  public static function notFound(
    string $message = 'Recurso no encontrado',
  ): JsonResponse {
    return self::make($message, 404);
  }

  /**
   * Retorna una respuesta de error de autenticación
   *
   * @param string $message Mensaje de error
   * @return JsonResponse
   */
  public static function unauthorized(
    string $message = 'No autorizado',
  ): JsonResponse {
    return self::make($message, 401);
  }

  /**
   * Retorna una respuesta de error de permisos
   *
   * @param string $message Mensaje de error
   * @return JsonResponse
   */
  public static function forbidden(
    string $message = 'Acceso prohibido',
  ): JsonResponse {
    return self::make($message, 403);
  }

  /**
   * Retorna una respuesta de error de conflicto
   *
   * @param string $message Mensaje de error
   * @return JsonResponse
   */
  public static function conflict(
    string $message = 'Conflicto en la operación',
  ): JsonResponse {
    return self::make($message, 409);
  }
}
