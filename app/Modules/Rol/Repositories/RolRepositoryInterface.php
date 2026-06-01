<?php

namespace App\Modules\Rol\Repositories;

use App\Modules\Rol\Models\Rol;

interface RolRepositoryInterface
{
  public function create(array $data): Rol;

  public function update(Rol $rol, array $data): Rol;

  public function delete(Rol $rol): bool;

  public function findByUid(string $uid): ?Rol;

  public function findByName(string $name): ?Rol;

  public function paginate(int $perPage = 20, ?string $search = null);

  public function getActiveRoles();
}
