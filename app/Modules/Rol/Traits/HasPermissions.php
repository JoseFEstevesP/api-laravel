<?php

namespace App\Modules\Rol\Traits;

use App\Modules\Rol\Enums\Permission;
use App\Modules\Rol\Models\Rol;

trait HasPermissions
{
  public function role()
  {
    return $this->belongsTo(Rol::class, 'uidRol', 'uid');
  }

  public function hasPermission(string $permission): bool
  {
    if (app()->runningInConsole()) {
      return true;
    }

    try {
      $payload = \Tymon\JWTAuth\Facades\JWTAuth::payload();
      if (isset($payload['permissions']) && is_array($payload['permissions'])) {
        $permissions = $payload['permissions'];

        return in_array(
          Permission::SUPER->value,
          $permissions,
        ) || in_array($permission, $permissions);
      }
    } catch (\Exception $e) {
    }

    $role = $this->role()->active()->first();
    if (!$role) {
      return false;
    }

    $permissions = cache()->remember(
      "role_permissions_{$role->uid}",
      now()->addMinutes(60),
      fn() => $role->permissions ?: [],
    );

    return in_array(
      Permission::SUPER->value,
      $permissions,
    ) || in_array($permission, $permissions);
  }

  public function hasAnyPermission(array $permissions): bool
  {
    foreach ($permissions as $perm) {
      if ($this->hasPermission($perm)) {
        return true;
      }
    }
    return false;
  }
}
