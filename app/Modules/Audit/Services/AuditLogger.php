<?php

namespace App\Modules\Audit\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class AuditLogger
{
  /**
   * Log an audit event with consistent structure
   *
   * @param string $event The audit event name
   * @param array $context Additional context data
   * @param string|null $userId The ID of the user performing the action
   * @param string|null $resourceType Type of resource being audited
   * @param string|null $resourceId ID of the resource being audited
   * @param string|null $description Optional description of the event
   * @return void
   */
  public static function log(
    string $event,
    array $context = [],
    ?string $userId = null,
    ?string $resourceType = null,
    ?string $resourceId = null,
    ?string $description = null,
  ): void {
    $auditData = [
      'timestamp' => now()->toISOString(),
      'event' => $event,
      'user_id' => $userId,
      'resource_type' => $resourceType,
      'resource_id' => $resourceId,
      'description' => $description,
      'context' => $context,
      'ip_address' => request()->ip() ?? null,
      'user_agent' => request()->userAgent() ?? null,
      'url' => request()->fullUrl() ?? null,
      'method' => request()->method() ?? null,
    ];

    // Log to the audit channel
    Log::channel('audit')->info('Audit Event', $auditData);
  }

  /**
   * Log a security-related event
   *
   * @param string $event The security event name
   * @param array $context Additional context data
   * @param string|null $userId The ID of the user involved
   * @param string|null $description Optional description
   * @return void
   */
  public static function logSecurity(
    string $event,
    array $context = [],
    ?string $userId = null,
    ?string $description = null,
  ): void {
    $securityData = [
      'timestamp' => now()->toISOString(),
      'event' => $event,
      'user_id' => $userId,
      'description' => $description,
      'context' => $context,
      'ip_address' => request()->ip() ?? null,
      'user_agent' => request()->userAgent() ?? null,
      'url' => request()->fullUrl() ?? null,
      'method' => request()->method() ?? null,
    ];

    // Log to the security channel for high-priority events
    Log::channel('security')->warning('Security Event', $securityData);
  }

  /**
   * Log a user action
   *
   * @param string $action The action performed
   * @param string|null $userId The ID of the user performing the action
   * @param string|null $resourceType Type of resource being acted upon
   * @param string|null $resourceId ID of the resource being acted upon
   * @param array $details Additional details about the action
   * @return void
   */
  public static function logUserAction(
    string $action,
    ?string $userId,
    ?string $resourceType = null,
    ?string $resourceId = null,
    array $details = [],
  ): void {
    static::log(
      $action,
      $details,
      $userId,
      $resourceType,
      $resourceId,
      "User performed action: {$action}",
    );
  }

  /**
   * Log authentication events
   *
   * @param string $eventType Type of auth event (login, logout, failed_login, etc.)
   * @param string|null $userId The ID of the user involved
   * @param array $details Additional details about the auth event
   * @return void
   */
  public static function logAuthentication(
    string $eventType,
    ?string $userId = null,
    array $details = [],
  ): void {
    static::log(
      "auth.{$eventType}",
      $details,
      $userId,
      'auth',
      null,
      "Authentication event: {$eventType}",
    );
  }
}
