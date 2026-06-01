<?php

namespace App\Modules\Rol\UseCases;

use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use Illuminate\Http\JsonResponse;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;

class DeleteRol
{
  private RolRepositoryInterface $repository;

  public function __construct(RolRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(int $id): JsonResponse
  {
    try {
      $rol = $this->repository->findById($id);

      if (!$rol) {
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('Rol.not_found'),
            ],
          ],
          404,
        );
      }

      $rolData = $rol->toArray();
      $result = $this->repository->delete($id);

      if (!$result) {
        // Esto no debería suceder si findById tuvo éxito, pero es una buena práctica de defensa.
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('Rol.deletion_error'),
            ],
          ],
          500,
        );
      }

      AuditLogger::logUserAction('rol_eliminado', Auth::id(), 'rol', $id, [
        'deleted_role' => $rolData,
      ]);

      return response()->json([
        'message' => useMsg::get('Rol.deleted'),
      ]);
    } catch (\Exception $e) {
      AuditLogger::log('error_eliminar_rol', ['error' => $e->getMessage()]);
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('Rol.deletion_error'),
          ],
        ],
        500,
      );
    }
  }
}
