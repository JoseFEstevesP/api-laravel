<?php

namespace App\Modules\Rol\Repositories;

use App\Modules\Rol\Models\Rol;

class RolRepository implements RolRepositoryInterface
{
  public function create(array $data): Rol
  {
    return Rol::create($data);
  }

  public function update(Rol $rol, array $data): Rol
  {
    $rol->fill($data);
    $rol->save();

    return $rol;
  }

  public function delete(Rol $rol): bool
  {
    return (bool) $rol->delete();
  }

  public function findByUid(string $uid): ?Rol
  {
    return Rol::where('uid', $uid)->first();
  }

  public function findByName(string $name): ?Rol
  {
    return Rol::where('name', $name)->first();
  }

  public function paginate(int $perPage = 20, ?string $search = null)
  {
    $query = Rol::query();

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'ILIKE', "%{$search}%")
          ->orWhere('description', 'ILIKE', "%{$search}%");
      });
    }

    return $query->orderBy('created_at', 'DESC')->paginate($perPage);
  }

  public function getActiveRoles()
  {
    return Rol::where('status', true)->get();
  }
}
