<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UnauthorizedException extends UnauthorizedHttpException
{
  public function __construct(
    string $message = 'No autorizado',
    ?\Throwable $previous = null,
    int $code = 0,
  ) {
    parent::__construct('Bearer', $message, $previous, $code);
  }
}
