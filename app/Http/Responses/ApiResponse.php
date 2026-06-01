<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
  public static function success(
    mixed $data = null,
    string $message = 'Operación exitosa',
    int $statusCode = 200,
  ): JsonResponse {
    return response()->json(
      array_filter(
        [
          'statusCode' => $statusCode,
          'message' => $message,
          'data' => $data,
          'timestamp' => now()->toIso8601String(),
          'path' => request()->fullUrl(),
        ],
        fn($v) => !is_null($v),
      ),
      $statusCode,
    );
  }

  public static function error(
    string $message = 'Error interno del servidor',
    int $statusCode = 500,
    ?array $errors = null,
  ): JsonResponse {
    $body = [
      'statusCode' => $statusCode,
      'message' => $message,
      'timestamp' => now()->toIso8601String(),
      'path' => request()->fullUrl(),
    ];

    if ($errors !== null) {
      $body['errors'] = $errors;
    }

    return response()->json($body, $statusCode);
  }

  public static function paginated(
    LengthAwarePaginator $paginator,
    string $message = 'Operación exitosa',
  ): JsonResponse {
    return response()->json(
      [
        'statusCode' => 200,
        'message' => $message,
        'data' => $paginator->items(),
        'meta' => [
          'currentPage' => $paginator->currentPage(),
          'lastPage' => $paginator->lastPage(),
          'perPage' => $paginator->perPage(),
          'total' => $paginator->total(),
        ],
        'timestamp' => now()->toIso8601String(),
        'path' => request()->fullUrl(),
      ],
      200,
    );
  }
}
