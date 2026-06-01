<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
  public function create(array $data): User;

  public function findByUid(string $uid): ?User;

  public function findByEmail(string $email): ?User;

  public function update(User $user, array $data): User;

  public function delete(User $user): bool;

  public function paginate(int $perPage = 20): LengthAwarePaginator;

  public function findAllWithFilters(
    array $filters = [],
    int $perPage = 20,
  ): LengthAwarePaginator;

  public function createToken(
    User $user,
    string $name,
    array $abilities = ['*'],
    ?\DateTimeInterface $expiresAt = null,
  ): string;

  public function revokeCurrentAccessToken(User $user): void;

  public function authenticateByCredentials(
    string $email,
    string $password,
  ): ?User;

  public function getActiveUsersIdsAndNames();
}
