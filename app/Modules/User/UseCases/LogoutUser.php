<?php

namespace App\Modules\User\UseCases;

use App\Modules\Session\Models\UserSession;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\Models\User;
use App\Modules\User\msg\useMsg;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use App\Modules\Audit\Services\AuditLogger;

class LogoutUser
{
  private UserRepositoryInterface $userRepository;

  public function __construct(UserRepositoryInterface $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  public function execute(?User $user): array|JsonResponse
  {
    if (!$user) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('auth.not_authenticated'),
          ],
        ],
        401,
      );
    }

    $this->userRepository->revokeCurrentAccessToken($user);

    $activeSession = UserSession::getActiveSession($user->uid);
    if ($activeSession) {
      $activeSession->delete();
    }

    $request = request();
    AuditLogger::logAuthentication('logout', $user->uid, [
      'proveedor' => 'jwt',
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
      'url' => $request->fullUrl(),
      'request_context' => [
        'referer' => $request->header('referer'),
        'locale' => app()->getLocale(),
        'method' => $request->method(),
        'path' => $request->path(),
      ],
      'session_duration' => $activeSession
        ? $this->calculateSessionDuration($activeSession->login_at)
        : null,
    ]);

    return response()
      ->json(['message' => useMsg::get('auth.logout_success')], 200)
      ->withCookie(Cookie::forget('accessToken'))
      ->withCookie(Cookie::forget('refreshToken'));
  }

  protected function calculateSessionDuration($loginTime)
  {
    if (!$loginTime) {
      return null;
    }

    try {
      $loginAt = Carbon::parse($loginTime);
      $now = now();
      $diff = $loginAt->diff($now);

      return $diff->format('%H:%I:%S');
    } catch (\Exception $e) {
      return null;
    }
  }
}
