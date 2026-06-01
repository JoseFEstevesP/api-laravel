<?php

namespace App\Modules\User\UseCases;

use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Session\Models\UserSession;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\msg\useMsg;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Traits\UserRequestInfoTrait;
use App\Modules\User\UseCases\CreateToken;
use Illuminate\Http\JsonResponse;

class LoginUser
{
  use UserRequestInfoTrait;

  private UserRepositoryInterface $userRepository;
  private CreateToken $createTokenUseCase;

  public function __construct(
    UserRepositoryInterface $userRepository,
    CreateToken $createTokenUseCase,
  ) {
    $this->userRepository = $userRepository;
    $this->createTokenUseCase = $createTokenUseCase;
  }

  public function execute(LoginRequest $credentials): JsonResponse
  {
    $user = $this->userRepository->authenticateByCredentials(
      $credentials['email'],
      $credentials['password'],
    );

    if (!$user) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('auth.invalid_credentials'),
          ],
        ],
        401,
      );
    }

    if (!$user->status) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('auth.user_invalid_status'),
          ],
        ],
        401,
      );
    }

    $request = request();
    $userIp = $this->getUserIp($request);

    if (UserSession::hasActiveSession($user->uid)) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('auth.session_already_exists'),
          ],
        ],
        409,
      );
    }

    $tokens = $this->createTokenUseCase->createAccessAndRefreshTokens($user);

    $existingInactiveSession = UserSession::where('user_id', $user->uid)
      ->where('is_active', false)
      ->orderBy('login_at', 'desc')
      ->first();

    if ($existingInactiveSession) {
      $existingInactiveSession->update([
        'session_id' => session()->getId(),
        'refresh_token' => $tokens['refreshToken'],
        'ip_address' => $this->getUserIp($request),
        'user_agent' => $request->userAgent(),
        'login_at' => now(),
        'last_activity' => now(),
        'expires_at' => now()->addHours(24),
        'is_active' => true,
      ]);
    } else {
      UserSession::create([
        'user_id' => $user->uid,
        'session_id' => session()->getId(),
        'refresh_token' => $tokens['refreshToken'],
        'ip_address' => $this->getUserIp($request),
        'user_agent' => $request->userAgent(),
        'login_at' => now(),
        'last_activity' => now(),
        'expires_at' => now()->addHours(24),
        'is_active' => true,
      ]);
    }

    AuditLogger::logAuthentication('login', $user->uid, [
      'proveedor' => 'jwt',
      'ip_address' => $this->getUserIp($request),
      'user_agent' => $request->userAgent(),
      'url' => $request->fullUrl(),
      'request_context' => [
        'referer' => $request->header('referer'),
        'locale' => app()->getLocale(),
        'method' => $request->method(),
        'path' => $request->path(),
      ],
      'session_id' => session()->getId() ?? null,
    ]);

    $user->save();

    $isProduction = app()->environment('production');

    return response()
      ->json([
        'message' => useMsg::get('auth.logged_in'),
        'user' => [
          'uid' => $user->uid,
          'names' => $user->names,
          'surnames' => $user->surnames,
          'email' => $user->email,
          'phone' => $user->phone,
          'role' => $user->role
            ? encrypt([
              'name' => $user->role->name,
              'description' => $user->role->description,
              'permissions' => $user->role->permissions,
            ])
            : null,
        ],
      ])
      ->withCookie(
        cookie(
          'accessToken',
          $tokens['accessToken'],
          $tokens['accessExpires'],
          null,
          null,
          $isProduction,
          true,
        ),
      )
      ->withCookie(
        cookie(
          'refreshToken',
          $tokens['refreshToken'],
          ($tokens['refreshExpiresDays'] ?? 7) * 1440,
          null,
          null,
          $isProduction,
          true,
        ),
      );
  }
}
