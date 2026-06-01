<?php

namespace App\Modules\Session\UseCases;

use App\Modules\Session\Repositories\UserSessionRepositoryInterface;
use App\Modules\Session\msg\useMsg;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CheckActiveSession
{
  private UserSessionRepositoryInterface $repository;

  public function __construct(UserSessionRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(): JsonResponse
  {
    try {
      $user = Auth::user();

      if (!$user) {
        return response()
          ->json(
            [
              'active_session' => false,
              'message' => 'Usuario no autenticado.',
            ],
            401,
          )
          ->withCookie(Cookie::forget('accessToken'))
          ->withCookie(Cookie::forget('refreshToken'));
      }

      $userId = $user->uid;
      $activeSession = $this->repository->findActiveByUserId($userId);

      if ($activeSession) {
        return response()->json(['active_session' => true]);
      }

      return response()
        ->json(['active_session' => false])
        ->withCookie(Cookie::forget('accessToken'))
        ->withCookie(Cookie::forget('refreshToken'));
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('Session.check_error'),
          ],
        ],
        500,
      );
    }
  }
}
