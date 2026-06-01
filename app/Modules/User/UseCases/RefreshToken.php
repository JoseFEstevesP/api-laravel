<?php

namespace App\Modules\User\UseCases;

use App\Modules\Session\Models\UserSession;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\Services\RefreshTokenService;
use App\Modules\User\msg\useMsg;
use App\Modules\User\Traits\UserRequestInfoTrait;
use App\Modules\User\UseCases\CreateToken;
use DomainException;
use Illuminate\Http\JsonResponse;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\Cookie;

class RefreshToken
{
  use UserRequestInfoTrait;

  private UserRepositoryInterface $userRepository;
  private CreateToken $createTokenUseCase;
  private RefreshTokenService $refreshTokenService;

  public function __construct(
    UserRepositoryInterface $userRepository,
    CreateToken $createTokenUseCase,
    RefreshTokenService $refreshTokenService,
  ) {
    $this->userRepository = $userRepository;
    $this->createTokenUseCase = $createTokenUseCase;
    $this->refreshTokenService = $refreshTokenService;
  }

  public function execute(?string $refreshToken = null): array|JsonResponse
  {
    if (!$refreshToken) {
      return response()
        ->json(
          [
            'all' => [
              'message' => useMsg::get('token.refresh_missing'),
            ],
          ],
          401,
        )
        ->withCookie(Cookie::forget('accessToken'))
        ->withCookie(Cookie::forget('refreshToken'));
    }

    try {
      $payload = $this->refreshTokenService->validateToken($refreshToken);

      $user = $this->userRepository->findByUid($payload->sub);

      if (!$user) {
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('token.invalid_or_expired'),
            ],
          ],
          401,
        );
      }

      $request = request();
      $userIp = $this->getUserIp($request);

      $activeSession = UserSession::active()
        ->byUser($user->uid)
        ->byIpAddress($userIp)
        ->byUserAgent($request->userAgent())
        ->byRefreshToken($refreshToken)
        ->first();

      if ($activeSession && $activeSession->isExpired()) {
        $activeSession->deleteSession();
        $activeSession = null;
      }

      if (!$activeSession) {
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('token.invalid_or_expired'),
            ],
          ],
          401,
        );
      }

      $newTokens = $this->createTokenUseCase->createAccessAndRefreshTokens(
        $user,
      );

      if ($activeSession) {
        $activeSession->refresh_token = $newTokens['refreshToken'];
        $activeSession->updateLastActivity();
        $activeSession->save();
      }

      AuditLogger::log(
        'token_actualizado',
        [
          'ip_address' => $userIp,
          'user_agent' => $request->userAgent(),
          'url' => $request->fullUrl(),
        ],
        $user->uid,
        'autenticacion',
        null,
        'Token actualizado correctamente',
      );

      $isProduction = app()->environment('production');

      return response()
        ->json(['message' => useMsg::get('token.access_refreshed')])
        ->withCookie(
          cookie(
            'accessToken',
            $newTokens['accessToken'],
            $newTokens['accessExpires'],
            null,
            null,
            $isProduction,
            true,
          ),
        )
        ->withCookie(
          cookie(
            'refreshToken',
            $newTokens['refreshToken'],
            ($newTokens['refreshExpiresDays'] ?? 7) * 1440,
            null,
            null,
            $isProduction,
            true,
          ),
        );
    } catch (DomainException $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('token.invalid_or_expired'),
          ],
        ],
        401,
      );
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('token.refresh_error'),
          ],
        ],
        500,
      );
    }
  }
}
