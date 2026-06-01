<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use DateTimeInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserRepository implements UserRepositoryInterface
{
  public function create(array $data): User
  {
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }
    return User::create($data);
  }

  public function findByUid(string $uid): ?User
  {
    return User::with('role')->where('uid', $uid)->first();
  }

  public function findByEmail(string $email): ?User
  {
    return User::where('email', $email)->first();
  }

  public function update(User $user, array $data): User
  {
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    $user->fill($data);
    $user->save();

    return $user;
  }

  public function delete(User $user): bool
  {
    return (bool) $user->delete();
  }

  public function paginate(int $perPage = 20): LengthAwarePaginator
  {
    return User::paginate($perPage);
  }

  public function findAllWithFilters(
    array $filters = [],
    int $perPage = 20,
  ): LengthAwarePaginator {
    if (isset($filters['search']) && !empty($filters['search'])) {
      $query = User::with('role');
    } else {
      $query = User::query();
    }

    if (isset($filters['status']) && $filters['status'] !== '') {
      $status = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
      if ($status !== null) {
        $query->where('status', $status);
      }
    }

    if (isset($filters['search']) && !empty($filters['search'])) {
      $searchTerm = $filters['search'];
      $query->where(function ($q) use ($searchTerm) {
        $q->where('names', 'ILIKE', "%{$searchTerm}%")
          ->orWhere('surnames', 'ILIKE', "%{$searchTerm}%")
          ->orWhere('email', 'ILIKE', "%{$searchTerm}%")
          ->orWhere('phone', 'ILIKE', "%{$searchTerm}%")
          ->orWhereHas('role', function ($roleQuery) use ($searchTerm) {
            $roleQuery->where('name', 'ILIKE', "%{$searchTerm}%");
          });
      });
    }

    $orderProperty = $filters['orderProperty'] ?? 'created_at';
    $orderDirection = strtoupper($filters['order'] ?? 'DESC');

    if (!in_array($orderDirection, ['ASC', 'DESC'])) {
      $orderDirection = 'DESC';
    }

    $query->orderBy($orderProperty, $orderDirection);

    return $query->paginate($perPage);
  }

  public function createToken(
    User $user,
    string $name,
    array $abilities = ['*'],
    ?DateTimeInterface $expiresAt = null,
  ): string {
    return JWTAuth::fromUser($user);
  }

  public function revokeCurrentAccessToken(User $user): void
  {
    JWTAuth::invalidate(JWTAuth::getToken());
  }

  public function authenticateByCredentials(
    string $email,
    string $password,
  ): ?User {
    $user = $this->findByEmail($email);

    if (!$user) {
      return null;
    }

    if (!Hash::check($password, $user->password)) {
      return null;
    }

    return $user;
  }

  public function getActiveUsersIdsAndNames()
  {
    return User::where('status', true)
      ->select('uid', 'names', 'surnames')
      ->get()
      ->map(function ($user) {
        return [
          'uid' => $user->uid,
          'name' => trim($user->names . ' ' . $user->surnames),
        ];
      });
  }
}
