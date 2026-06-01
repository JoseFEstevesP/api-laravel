<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
  protected $dontFlash = [
    'current_password',
    'password',
    'password_confirmation',
  ];

  public function register(): void
  {
    $this->reportable(function (Throwable $e) {
      //
    });
  }

  public function render($request, Throwable $exception)
  {
    if ($request->is('api/*')) {
      return $this->handleApiException($request, $exception);
    }

    return parent::render($request, $exception);
  }

  private function handleApiException($request, Throwable $exception): JsonResponse
  {
    if ($exception instanceof ValidationException) {
      return $this->convertValidationExceptionToResponse($exception, $request);
    }

    if ($exception instanceof UnauthorizedHttpException) {
      return ApiResponse::error(
        $exception->getMessage() ?: 'No autorizado',
        401,
      );
    }

    if ($exception instanceof NotFoundHttpException) {
      return ApiResponse::error(
        $exception->getMessage() ?: 'Recurso no encontrado',
        404,
      );
    }

    if ($exception instanceof MethodNotAllowedHttpException) {
      return ApiResponse::error(
        'Método no permitido',
        405,
      );
    }

    if ($exception instanceof HttpException) {
      return ApiResponse::error(
        $exception->getMessage() ?: 'Error de solicitud',
        $exception->getStatusCode(),
      );
    }

    if ($exception instanceof \App\Exceptions\NotFoundException) {
      return ApiResponse::error($exception->getMessage(), 404);
    }

    if ($exception instanceof \App\Exceptions\UnauthorizedException) {
      return ApiResponse::error($exception->getMessage(), 401);
    }

    if ($exception instanceof \App\Exceptions\BadRequestException) {
      return ApiResponse::error($exception->getMessage(), 400);
    }

    if ($exception instanceof \App\Exceptions\ConflictException) {
      return ApiResponse::error($exception->getMessage(), 409);
    }

    return ApiResponse::error(
      'Error interno del servidor',
      500,
    );
  }

  protected function convertValidationExceptionToResponse(
    ValidationException $exception,
    $request,
  ): JsonResponse {
    $errors = $exception->validator->errors()->getMessages();

    $formattedErrors = [];
    foreach ($errors as $field => $messages) {
      $formattedErrors[] = [
        'field' => $field,
        'message' => $messages[0],
      ];
    }

    return ApiResponse::error(
      'Error de validación',
      400,
      $formattedErrors,
    );
  }
}
