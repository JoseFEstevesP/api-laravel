<?php

namespace App\Modules\Rol\Repositories;

use App\Modules\Rol\Models\Rol;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class RolRepositoryCacheDecorator implements RolRepositoryInterface
{
  protected RolRepositoryInterface $repo;

  protected CacheRepository $cache;

  protected int $ttl;

  public function __construct(
    RolRepositoryInterface $repo,
    CacheRepository $cache,
    int $ttl = 300,
  ) {
    $this->repo = $repo;
    $this->cache = $cache;
    $this->ttl = $ttl;
  }

  public function create(array $data): Rol
  {
    $rol = $this->repo->create($data);
    $this->flushRolCaches($rol);
    return $rol;
  }

  public function update(Rol $rol, array $data): Rol
  {
    $updated = $this->repo->update($rol, $data);
    $this->flushRolCaches($updated);
    return $updated;
  }

  public function delete(Rol $rol): bool
  {
    $deleted = $this->repo->delete($rol);

    if ($deleted) {
      $this->flushRolCaches($rol);
    }

    return $deleted;
  }

  public function findByUid(string $uid): ?Rol
  {
    $key = "rol:uid:{$uid}";

    return $this->cache->remember($key, $this->ttl, function () use ($uid) {
      return $this->repo->findByUid($uid);
    });
  }

  public function findByName(string $name): ?Rol
  {
    $key = 'rol:name:' . md5(strtolower($name));

    return $this->cache->remember($key, $this->ttl, function () use ($name) {
      return $this->repo->findByName($name);
    });
  }

  public function paginate(int $perPage = 20, ?string $search = null)
  {
    $cacheKey = 'roles:paginate:' . md5($perPage . ($search ?? ''));

    return $this->cache->remember($cacheKey, $this->ttl, function () use (
      $perPage,
      $search,
    ) {
      return $this->repo->paginate($perPage, $search);
    });
  }

  public function getActiveRoles()
  {
    return $this->cache->remember('roles:active', $this->ttl, function () {
      return $this->repo->getActiveRoles();
    });
  }

  private function flushRolCaches(Rol $rol): void
  {
    $keys = [
      "rol:uid:{$rol->uid}",
      'rol:name:' . md5(strtolower($rol->name)),
      "role_permissions_{$rol->uid}",
      'roles:active',
    ];

    foreach ($keys as $key) {
      $this->cache->forget($key);
    }

    $this->cache->forget('roles:paginate:*');
  }
}
