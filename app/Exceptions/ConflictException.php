<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ConflictException extends HttpException
{
  public function __construct(
    string $message = 'Conflicto',
    ?\Throwable $previous = null,
    int $code = 0,
  ) {
    parent::__construct(409, $message, $previous, [], $code);
  }
}
