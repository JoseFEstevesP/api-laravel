<?php

namespace App\Modules\Rol\Services;

use App\Modules\Rol\Enums\Permission;

class PermissionService
{
  public function validate(
    array $userPermissions,
    string $requiredPermission,
  ): bool {
    if (in_array(Permission::SUPER->value, $userPermissions)) {
      return true;
    }
    return in_array($requiredPermission, $userPermissions);
  }
}
