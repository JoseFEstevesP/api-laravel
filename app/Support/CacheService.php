<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Redis;

class CacheService
{
  private CacheRepository $cache;
  private int $ttl;

  public function __construct(CacheRepository $cache, int $ttl = 300)
  {
    $this->cache = $cache;
    $this->ttl = $ttl;
  }

  public function remember(string $key, callable $callback, ?int $ttl = null): mixed
  {
    return $this->cache->remember($key, $ttl ?? $this->ttl, $callback);
  }

  public function forget(string $key): bool
  {
    return $this->cache->forget($key);
  }

  public function delPattern(string $pattern): int
  {
    $connection = Redis::connection();
    $cursor = 0;
    $deleted = 0;

    do {
      $result = $connection->command('SCAN', [$cursor, 'MATCH', $pattern, 'COUNT', 100]);
      $cursor = $result[0];
      $keys = $result[1];

      if (!empty($keys)) {
        $connection->command('DEL', $keys);
        $deleted += count($keys);
      }
    } while ($cursor != 0);

    return $deleted;
  }

  public function setTtl(int $ttl): void
  {
    $this->ttl = $ttl;
  }
}
