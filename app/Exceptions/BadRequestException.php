<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BadRequestException extends HttpException
{
  public function __construct(
    string $message = 'Solicitud incorrecta',
    ?\Throwable $previous = null,
    int $code = 0,
  ) {
    parent::__construct(400, $message, $previous, [], $code);
  }
}
