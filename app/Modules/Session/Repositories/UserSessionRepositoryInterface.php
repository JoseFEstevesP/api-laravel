<?php

namespace App\Modules\Session\Repositories;

use App\Modules\Session\Models\UserSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserSessionRepositoryInterface
{
  public function getAll(int $perPage = 15): LengthAwarePaginator;

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
  ): LengthAwarePaginator;

  public function findById(int $id): ?UserSession;

  public function delete(int $id): bool;

  public function deleteByUserId(string $userId): int;

  public function findActiveByUserId(string $userId): ?UserSession;
}
