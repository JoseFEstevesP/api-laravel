<?php

namespace App\Modules\Audit\Listeners\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SecurityAuthListener
{
  public function handleLogin(Login $event)
  {
    try {
      \App\Modules\Audit\Services\AuditLogger::logAuthentication(
        'login',
        $event->user->getAuthIdentifier(),
        [
          'provider' => $event->guard,
          'remember' => $event->remember ? 'true' : 'false',
          'ip_address' => $this->getIpAddress(request()),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
          'request_context' => [
            'referer' => request()->header('referer'),
            'locale' => app()->getLocale(),
            'method' => request()->method(),
            'path' => request()->path(),
          ],
          'session_id' => session()->getId() ?? null,
        ],
      );
    } catch (\Exception $e) {
      Log::error('Failed to log login audit: ' . $e->getMessage());
    }
  }

  public function handleLogout(Logout $event)
  {
    try {
      if ($event->user) {
        \App\Modules\Audit\Services\AuditLogger::logAuthentication(
          'logout',
          $event->user->getAuthIdentifier(),
          [
            'provider' => $event->guard,
            'ip_address' => $this->getIpAddress(request()),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'request_context' => [
              'referer' => request()->header('referer'),
              'locale' => app()->getLocale(),
              'method' => request()->method(),
              'path' => request()->path(),
            ],
            'session_duration' => $this->calculateSessionDuration(
              session('login_time'),
            ),
          ],
        );
      }
    } catch (\Exception $e) {
      Log::error('Failed to log logout audit: ' . $e->getMessage());
    }
  }

  public function handleFailed(Failed $event)
  {
    try {
      $userId = null;
      if (isset($event->credentials['email'])) {
        $userId = $event->credentials['email'];
      }

      \App\Modules\Audit\Services\AuditLogger::logSecurity(
        'login_failed',
        [
          'credentials' => [
            'username' => $event->credentials['email'] ?? 'unknown',
          ],
          'provider' => $event->guard,
          'ip_address' => $this->getIpAddress(request()),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
          'request_context' => [
            'referer' => request()->header('referer'),
            'locale' => app()->getLocale(),
            'method' => request()->method(),
            'path' => request()->path(),
          ],
        ],
        $userId,
        'Failed login attempt',
      );
    } catch (\Exception $e) {
      Log::error('Failed to log failed login audit: ' . $e->getMessage());
    }
  }

  public function handleLockout(Lockout $event)
  {
    try {
      $userId = $event->request->input('email') ?? null;

      \App\Modules\Audit\Services\AuditLogger::logSecurity(
        'account_lockout',
        [
          'username' => $event->request->input('email') ?? 'unknown',
          'attempts' => session('login_attempts') ?? 'unknown',
          'ip_address' => $this->getIpAddress(request()),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
          'request_context' => [
            'referer' => request()->header('referer'),
            'locale' => app()->getLocale(),
            'method' => request()->method(),
            'path' => request()->path(),
          ],
        ],
        $userId,
        'Account lockout event',
      );
    } catch (\Exception $e) {
      Log::error('Failed to log lockout audit: ' . $e->getMessage());
    }
  }

  public function handle($event)
  {
    $this->handleLogin($event);
  }

  public function logout($event)
  {
    $this->handleLogout($event);
  }

  public function failed($event)
  {
    $this->handleFailed($event);
  }

  public function lockout($event)
  {
    $this->handleLockout($event);
  }

  protected function getIpAddress($request): ?string
  {
    $keys = [
      'X-Forwarded-For',
      'X-Real-IP',
      'CF-Connecting-IP',
      'True-Client-IP',
      'X-Cluster-Client-IP',
    ];

    foreach ($keys as $key) {
      if ($request->header($key)) {
        $ip = $request->header($key);
        if ($key === 'X-Forwarded-For') {
          $ips = explode(',', $ip);
          $ip = trim($ips[0]);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
          return $ip;
        }
      }
    }

    return $request->ip();
  }

  protected function calculateSessionDuration($loginTime)
  {
    if (!$loginTime) {
      return null;
    }

    try {
      $loginAt = \Carbon\Carbon::parse($loginTime);
      $now = now();
      $diff = $loginAt->diff($now);

      return $diff->format('%H:%I:%S');
    } catch (\Exception $e) {
      return null;
    }
  }
}
