<?php

namespace App\Modules\Rol\UseCases;

use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use Illuminate\Http\JsonResponse;

class FindAllFormatRol
{
  private RolRepositoryInterface $repository;

  public function __construct(RolRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(): JsonResponse
  {
    try {
      $entity = $this->repository->getActiveRoleIdsAndNames();

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
