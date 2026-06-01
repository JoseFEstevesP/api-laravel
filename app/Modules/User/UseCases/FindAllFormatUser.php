<?php

namespace App\Modules\User\UseCases;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\msg\useMsg;
use Illuminate\Http\JsonResponse;

class FindAllFormatUser
{
  private UserRepositoryInterface $repository;

  public function __construct(UserRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(): JsonResponse
  {
    try {
      $entity = $this->repository->getActiveUsersIdsAndNames();

      return response()->json($entity);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('User.retrieval_error'),
          ],
        ],
        500,
      );
    }
  }
}
