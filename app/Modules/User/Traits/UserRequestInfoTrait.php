<?php

namespace App\Modules\User\Traits;

/**
 * Trait para compartir lógica de obtención de información del request
 * entre diferentes use cases relacionados con usuarios
 */
trait UserRequestInfoTrait
{
  /**
   * Detectar plataforma del usuario según User Agent
   */
  private function getUserPlatform(string $userAgent): string
  {
    if (strpos($userAgent, 'Windows') !== false) {
      return 'Windows';
    } elseif (
      strpos($userAgent, 'Macintosh') !== false ||
      strpos($userAgent, 'Mac OS X') !== false
    ) {
      return 'macOS';
    } elseif (strpos($userAgent, 'Android') !== false) {
      return 'Android';
    } elseif (
      strpos($userAgent, 'iPhone') !== false ||
      strpos($userAgent, 'iPad') !== false
    ) {
      return 'iOS';
    } elseif (strpos($userAgent, 'Linux') !== false) {
      return 'Linux';
    } else {
      return 'Unknown';
    }
  }

  /**
   * Obtener IP real del usuario considerando proxies
   */
  private function getUserIp($request): string
  {
    $xForwardedFor = $request->header('X-Forwarded-For');

    if ($xForwardedFor) {
      $ips = explode(',', $xForwardedFor);
      $ips = array_map('trim', $ips);

      $publicIp = $this->findPublicIp($ips);
      return $publicIp ?: $ips[0];
    } elseif ($request->header('X-Real-IP')) {
      return $request->header('X-Real-IP');
    } else {
      return $request->ip();
    }
  }

  /**
   * Buscar IP pública en lista de IPs
   */
  private function findPublicIp(array $ips): ?string
  {
    foreach ($ips as $ip) {
      $ip = trim($ip);
      if (!$this->isPrivateIP($ip)) {
        return $ip;
      }
    }
    return null;
  }

  /**
   * Verificar si una IP es privada
   */
  private function isPrivateIP(string $ip): bool
  {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
      return true;
    }

    return filter_var(
      $ip,
      FILTER_VALIDATE_IP,
      FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
    ) === false;
  }
}
