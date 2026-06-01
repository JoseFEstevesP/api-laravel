<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;
use DomainException;
use Carbon\Carbon;

class RefreshTokenService
{
  public function generateToken(User $user): string
  {
    $secret = Config::get('refreshtoken.secret');
    if (empty($secret)) {
      throw new DomainException(
        'El secreto de actualización de JWT no está configurado.',
      );
    }

    $issuedAt = Carbon::now();
    $expiresAt = $issuedAt->copy()->addMinutes(Config::get('refreshtoken.ttl'));

    $payload = [
      'iss' => Config::get('refreshtoken.issuer'),
      'iat' => $issuedAt->getTimestamp(),
      'nbf' => $issuedAt->getTimestamp(),
      'exp' => $expiresAt->getTimestamp(),
      'sub' => $user->uid,
    ];

    return JWT::encode($payload, $secret, 'HS256');
  }

  public function validateToken(string $token): object
  {
    $secret = Config::get('refreshtoken.secret');
    if (empty($secret)) {
      throw new DomainException(
        'El secreto de actualización de JWT no está configurado.',
      );
    }

    try {
      $payload = JWT::decode($token, new Key($secret, 'HS256'));
      return $payload;
    } catch (\Exception $e) {
      throw new DomainException(
        'Token de actualización no válido: ' . $e->getMessage(),
      );
    }
  }
}
