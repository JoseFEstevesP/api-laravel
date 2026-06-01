<?php

namespace App\Modules\Rol\UseCases;

use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use Illuminate\Http\JsonResponse;

class FindByIdRol
{
  private RolRepositoryInterface $repository;

  public function __construct(RolRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(int $id): JsonResponse
  {
    try {
      $entity = $this->repository->findById($id);

      if (!$entity) {
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('Rol.not_found'),
            ],
          ],
          404,
        );
      }

      return response()->json($entity);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('Rol.retrieval_error'),
          ],
        ],
        500,
      );
    }
  }
}
