<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class LoggerService
{
  private string $channel;

  public function __construct(string $channel = 'stack')
  {
    $this->channel = $channel;
  }

  public function info(string $message, array $context = []): void
  {
    Log::channel($this->channel)->info($message, $this->enrich($context));
  }

  public function error(string $message, array $context = []): void
  {
    Log::channel($this->channel)->error($message, $this->enrich($context));
  }

  public function warning(string $message, array $context = []): void
  {
    Log::channel($this->channel)->warning($message, $this->enrich($context));
  }

  public function debug(string $message, array $context = []): void
  {
    Log::channel($this->channel)->debug($message, $this->enrich($context));
  }

  private function enrich(array $context): array
  {
    return array_merge([
      'correlation_id' => request()->input('correlation_id'),
      'ip' => request()->ip(),
      'user_agent' => request()->userAgent(),
      'method' => request()->method(),
      'path' => request()->path(),
    ], $context);
  }
}
