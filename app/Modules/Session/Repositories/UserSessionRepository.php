<?php

namespace App\Modules\Session\Repositories;

use App\Modules\Session\Models\UserSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserSessionRepository implements UserSessionRepositoryInterface
{
  public function getAll(int $perPage = 15): LengthAwarePaginator
  {
    return UserSession::orderBy('login_at', 'desc')->paginate($perPage);
  }

  /**
   * Buscar y filtrar sesiones con paginación
   *
   * @param array $filters Filtros para búsqueda y ordenamiento
   * @param int $perPage Número de sesiones por página
   * @return LengthAwarePaginator Resultados paginados de sesiones
   */
  public function findAllWithFilters(
    array $filters = [],
    int $perPage = 15,
  ): LengthAwarePaginator {
    $query = UserSession::query();

    // Filtrar por estado activo
    if (isset($filters['status']) && $filters['status'] !== '') {
      // Convertir el valor de status a booleano para comparar con el campo 'is_active' en Oracle ('1'/'0')
      if ($filters['status'] === '0') {
        $query->where('is_active', '0'); // Sesiones inactivas
      } else {
        $query->where('is_active', '1'); // Sesiones activas
      }
    }

    // Filtrar por búsqueda (en campos de sesión)
    if (isset($filters['search']) && !empty($filters['search'])) {
      $searchTerm = $filters['search'];
      $query->where(function ($q) use ($searchTerm) {
        $q->where('user_id', 'LIKE', "%{$searchTerm}%")
          ->orWhere('ip_address', 'LIKE', "%{$searchTerm}%")
          ->orWhere('user_agent', 'LIKE', "%{$searchTerm}%");
      });
    }

    // Ordenar resultados
    $orderProperty = $filters['orderProperty'] ?? 'login_at';
    $orderDirection = strtoupper($filters['order'] ?? 'DESC');

    // Asegurar que solo se usen direcciones de ordenamiento válidas
    if (!in_array($orderDirection, ['ASC', 'DESC'])) {
      $orderDirection = 'DESC';
    }

    $query->orderBy($orderProperty, $orderDirection);

    return $query->paginate($perPage);
  }

  public function findById(int $id): ?UserSession
  {
    return UserSession::find($id);
  }

  public function delete(int $id): bool
  {
    $session = $this->findById($id);

    if (!$session) {
      return false;
    }

    return $session->delete();
  }

  public function deleteByUserId(string $userId): int
  {
    return UserSession::where('user_id', $userId)->delete();
  }

  public function findActiveByUserId(string $userId): ?UserSession
  {
    return UserSession::active()->byUser($userId)->first();
  }
}
