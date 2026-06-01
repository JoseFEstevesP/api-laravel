<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
  public function check(): JsonResponse
  {
    $checks = [
      'database' => $this->checkDatabase(),
      'redis' => $this->checkRedis(),
      'cache' => $this->checkCache(),
    ];

    $allHealthy = collect($checks)->every(fn($c) => $c['healthy']);

    return response()->json([
      'status' => $allHealthy ? 'healthy' : 'degraded',
      'timestamp' => now()->toIso8601String(),
      'checks' => $checks,
    ], $allHealthy ? 200 : 503);
  }

  private function checkDatabase(): array
  {
    try {
      DB::connection()->getPdo();
      return ['healthy' => true, 'message' => 'Conexión establecida'];
    } catch (\Exception $e) {
      return ['healthy' => false, 'message' => $e->getMessage()];
    }
  }

  private function checkRedis(): array
  {
    try {
      Redis::connection()->ping();
      return ['healthy' => true, 'message' => 'Conexión establecida'];
    } catch (\Exception $e) {
      return ['healthy' => false, 'message' => $e->getMessage()];
    }
  }

  private function checkCache(): array
  {
    try {
      Cache::store('redis')->set('health_check', true, 10);
      Cache::store('redis')->forget('health_check');
      return ['healthy' => true, 'message' => 'Cache operativo'];
    } catch (\Exception $e) {
      return ['healthy' => false, 'message' => $e->getMessage()];
    }
  }
}
