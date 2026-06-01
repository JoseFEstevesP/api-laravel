<?php

namespace App\Modules\Session\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para representar sesiones activas de usuario
 *
 * Este modelo representa las sesiones activas de usuarios en el sistema,
 * permitiendo verificar sesiones concurrentes y gestionar el cierre de sesiones.
 *
 * @property int $id Identificador único de la sesión
 * @property string $user_id Identificador del usuario
 * @property string $session_id Identificador de la sesión
 * @property string|null $ip_address Dirección IP desde la que se inició la sesión
 * @property string|null $user_agent Agente de usuario del cliente
 * @property \Carbon\Carbon $login_at Momento en que se inició la sesión
 * @property \Carbon\Carbon|null $last_activity Momento de la última actividad
 * @property \Carbon\Carbon|null $expires_at Momento en que expira la sesión
 * @property string $is_active Indicador de si la sesión está activa ('1' o '0')
 */
class UserSession extends Model
{
  /**
   * La tabla asociada con el modelo.
   *
   * @var string
   */
  protected $table = 'user_sessions';

  /**
   * Las claves primarias del modelo.
   *
   * @var string
   */
  protected $primaryKey = 'id';

  /**
   * Indica si los ID son auto-incrementables.
   *
   * @var bool
   */
  public $incrementing = true;

  /**
   * El tipo de datos de la clave primaria.
   *
   * @var string
   */
  protected $keyType = 'int';

  /**
   * Indica si el modelo debe ser marcado con timestamps.
   *
   * @var bool
   */
  public $timestamps = false;

  /**
   * Los atributos que son asignables masivamente.
   *
   * @var array
   */
  protected $fillable = [
    'user_id',
    'session_id',
    'refresh_token',
    'ip_address',
    'user_agent',
    'login_at',
    'last_activity',
    'expires_at',
    'is_active',
  ];

  /**
   * Los atributos que deben ser convertidos a tipos nativos.
   *
   * @var array
   */
  protected $casts = [
    'login_at' => 'datetime',
    'last_activity' => 'datetime',
    'expires_at' => 'datetime',
    'is_active' => 'boolean',
  ];

  /**
   * Scope para obtener solo las sesiones activas.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Scope para obtener las sesiones de un usuario específico.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param string $userId
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeByUser($query, $userId)
  {
    return $query->where('user_id', $userId);
  }

  /**
   * Scope para obtener la sesión actual basada en el ID de sesión.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param string $sessionId
   * @param string $sessionId
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeBySessionId($query, $sessionId)
  {
    return $query->where('session_id', $sessionId);
  }

  /**
   * Scope para filtrar por refresh token.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param string $refreshToken
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeByRefreshToken($query, $refreshToken)
  {
    return $query->where('refresh_token', $refreshToken);
  }

  /**
   * Scope para filtrar por dirección IP.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param string $ipAddress
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeByIpAddress($query, $ipAddress)
  {
    return $query->where('ip_address', $ipAddress);
  }

  /**
   * Scope para filtrar por agente de usuario.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param string $userAgent
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeByUserAgent($query, $userAgent)
  {
    return $query->where('user_agent', $userAgent);
  }

  /**
   * Método para crear un registro de sesión para un usuario.
   *
   * @param string $userId Identificador del usuario
   * @param string|null $ipAddress Dirección IP del cliente
   * @param string|null $userAgent Agente de usuario del cliente
   * @param \Carbon\Carbon|null $expiresAt Momento de expiración de la sesión (por defecto 24 horas desde el login)
   * @param string|null $refreshToken Token de refresco para la sesión
   * @return static
   */
  public static function createSession(
    $userId,
    $ipAddress = null,
    $userAgent = null,
    $expiresAt = null,
    $refreshToken = null,
  ) {
    // Si no se proporciona expiresAt, establecerlo a 24 horas desde ahora
    if ($expiresAt === null) {
      $expiresAt = now()->addHours(24);
    }

    return static::create([
      'user_id' => $userId,
      'session_id' => session()->getId(),
      'refresh_token' => $refreshToken,
      'ip_address' => $ipAddress,
      'user_agent' => $userAgent,
      'login_at' => now(),
      'last_activity' => now(),
      'expires_at' => $expiresAt,
      'is_active' => true,
    ]);
  }

  /**
   * Método para cerrar la sesión actual (marcar como inactiva).
   *
   * @return bool
   */
  public function closeSession()
  {
    $this->is_active = false;
    $this->last_activity = now();
    return $this->save();
  }

  /**
   * Método para eliminar la sesión actual.
   *
   * @return bool
   */
  public function deleteSession()
  {
    return $this->delete();
  }

  /**
   * Método para actualizar la última actividad de la sesión.
   *
   * @return bool
   */
  public function updateLastActivity()
  {
    $this->last_activity = now();
    return $this->save();
  }

  /**
   * Método para verificar si una sesión está expirada.
   *
   * @return bool
   */
  public function isExpired()
  {
    if ($this->expires_at && $this->expires_at->isPast()) {
      return true;
    }
    return false;
  }

  /**
   * Método para verificar si hay sesiones activas para un usuario.
   *
   * @param string $userId Identificador del usuario
   * @return bool
   */
  public static function hasActiveSession($userId)
  {
    return static::active()->byUser($userId)->exists();
  }

  /**
   * Método para obtener la sesión activa de un usuario.
   *
   * @param string $userId Identificador del usuario
   * @return static|null
   */
  public static function getActiveSession($userId)
  {
    return static::active()->byUser($userId)->first();
  }

  /**
   * Método para cerrar todas las sesiones activas de un usuario.
   *
   * @param string $userId Identificador del usuario
   * @return int Número de sesiones cerradas
   */
  public static function closeAllActiveSessions($userId)
  {
    return static::active()
      ->byUser($userId)
      ->update([
        'is_active' => false,
        'last_activity' => now(),
      ]);
  }

  /**
   * Método para limpiar sesiones expiradas o inactivas.
   *
   * @param int $hoursAfterExpiry Número de horas después de la expiración para eliminar
   * @return int Número de sesiones eliminadas
   */
  public static function cleanExpiredSessions($hoursAfterExpiry = 24)
  {
    $cutoffTime = now()->subHours($hoursAfterExpiry);

    // Eliminar sesiones inactivas que no han sido activas por más de $hoursAfterExpiry horas
    $inactiveDeleted = static::where('is_active', false)
      ->where('last_activity', '<', $cutoffTime)
      ->delete();

    // Eliminar sesiones activas que han expirado (no solo cerrarlas)
    $expiredActiveDeleted = static::active()
      ->whereNotNull('expires_at')
      ->where('expires_at', '<', now())
      ->delete();

    return $inactiveDeleted + $expiredActiveDeleted;
  }

  /**
   * Método para eliminar sesiones muy antiguas (independientemente de su estado).
   *
   * @param int $maxAgeInDays Número máximo de días de antigüedad permitidos
   * @return int Número de sesiones eliminadas
   */
  public static function deleteOldSessions($maxAgeInDays = 30)
  {
    $cutoffDate = now()->subDays($maxAgeInDays);

    return static::where('login_at', '<', $cutoffDate)->delete();
  }
}
