<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepositoryCacheDecorator implements UserRepositoryInterface
{
  protected UserRepositoryInterface $repo;

  protected CacheRepository $cache;

  protected int $ttl;

  public function __construct(
    UserRepositoryInterface $repo,
    CacheRepository $cache,
    int $ttl = 300,
  ) {
    $this->repo = $repo;
    $this->cache = $cache;
    $this->ttl = $ttl;
  }

  public function create(array $data): User
  {
    $user = $this->repo->create($data);
    $this->flushUserCaches($user);
    return $user;
  }

  public function findByUid(string $uid): ?User
  {
    $key = "user:uid:{$uid}";

    return $this->cache->remember($key, $this->ttl, function () use ($uid) {
      return $this->repo->findByUid($uid);
    });
  }

  public function findByEmail(string $email): ?User
  {
    $key = 'user:email:' . sha1(strtolower($email));

    return $this->cache->remember($key, $this->ttl, function () use ($email) {
      return $this->repo->findByEmail($email);
    });
  }

  public function update(User $user, array $data): User
  {
    $updated = $this->repo->update($user, $data);
    $this->flushUserCaches($updated);
    return $updated;
  }

  public function delete(User $user): bool
  {
    $deleted = $this->repo->delete($user);

    if ($deleted) {
      $this->flushUserCaches($user);
    }

    return $deleted;
  }

  public function paginate(int $perPage = 20): LengthAwarePaginator
  {
    return $this->repo->paginate($perPage);
  }

  public function findAllWithFilters(
    array $filters = [],
    int $perPage = 20,
  ): LengthAwarePaginator {
    $cacheKey = 'users:filters:' . md5(serialize($filters) . $perPage);

    return $this->cache->remember($cacheKey, $this->ttl, function () use (
      $filters,
      $perPage,
    ) {
      return $this->repo->findAllWithFilters($filters, $perPage);
    });
  }

  public function createToken(
    User $user,
    string $name,
    array $abilities = ['*'],
    ?\DateTimeInterface $expiresAt = null,
  ): string {
    return $this->repo->createToken($user, $name, $abilities, $expiresAt);
  }

  public function revokeCurrentAccessToken(User $user): void
  {
    $this->repo->revokeCurrentAccessToken($user);
  }

  public function authenticateByCredentials(
    string $email,
    string $password,
  ): ?User {
    return $this->repo->authenticateByCredentials($email, $password);
  }

  public function getActiveUsersIdsAndNames()
  {
    return $this->repo->getActiveUsersIdsAndNames();
  }

  private function flushUserCaches(User $user): void
  {
    $keys = [
      "user:uid:{$user->uid}",
      'user:email:' . sha1(strtolower($user->email)),
    ];

    foreach ($keys as $key) {
      $this->cache->forget($key);
    }

    $this->cache->forget('users:filters:*');
  }
}
