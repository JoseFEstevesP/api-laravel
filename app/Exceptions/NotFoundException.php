<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
  public function __construct(
    string $message = 'Recurso no encontrado',
    ?\Throwable $previous = null,
    int $code = 0,
  ) {
    parent::__construct($message, $previous, $code);
  }
}
