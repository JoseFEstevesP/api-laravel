<?php

namespace App\Modules\User\UseCases;

use App\Modules\User\Models\User;
use App\Modules\User\Services\RefreshTokenService;
use App\Modules\User\Traits\UserRequestInfoTrait;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreateToken
{
  use UserRequestInfoTrait;

  private RefreshTokenService $refreshTokenService;

  public function __construct(RefreshTokenService $refreshTokenService)
  {
    $this->refreshTokenService = $refreshTokenService;
  }

  public function createAccessToken(User $user): string
  {
    $request = request();
    $userAgent = $request->userAgent() ?? 'Unknown';
    $userPlatform = $this->getUserPlatform($userAgent);
    $userIp = $this->getUserIp($request);

    $role = $user->role()->active()->first();
    $permissions = $role && $role->status
      ? (is_string($role->permissions) ? json_decode($role->permissions, true) : $role->permissions)
      : [];

    $accessClaims = [
      'uid' => $user->uid,
      'email' => $user->email,
      'provider' => $user->provider,
      'uidRol' => $user->uidRol,
      'userAgent' => $userAgent,
      'userPlatform' => $userPlatform,
      'userIp' => $userIp,
      'permissions' => $permissions,
    ];

    return JWTAuth::claims($accessClaims)->fromUser($user);
  }

  public function createRefreshToken(User $user): string
  {
    return $this->refreshTokenService->generateToken($user);
  }

  public function createAccessAndRefreshTokens(User $user): array
  {
    $accessToken = $this->createAccessToken($user);
    $refreshToken = $this->createRefreshToken($user);

    $accessTokenExpiration = config('jwt.ttl', 60);
    $refreshTokenExpiration = config('refreshtoken.ttl', 20160) / 1440;

    return [
      'accessToken' => $accessToken,
      'refreshToken' => $refreshToken,
      'accessExpires' => $accessTokenExpiration,
      'refreshExpiresDays' => (int) $refreshTokenExpiration,
    ];
  }
}
