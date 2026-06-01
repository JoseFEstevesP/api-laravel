<?php

namespace App\Modules\Rol\UseCases;

use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class FindAllRol
{
  private RolRepositoryInterface $repository;

  public function __construct(RolRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(
    ?string $status = null,
    int $perPage = 10,
    int $page = 1,
    ?string $orderProperty = 'id',
    string $order = 'ASC',
    ?string $search = null,
  ): JsonResponse {
    try {
      $filters = [];

      if ($status !== null) {
        $filters['status'] = $status;
      }

      if ($orderProperty !== null) {
        $filters['orderProperty'] = $orderProperty;
      }

      if ($order !== null) {
        $filters['order'] = $order;
      }

      if ($search !== null) {
        $filters['search'] = $search;
      }

      /** @var LengthAwarePaginator $paginator */
      $paginator = $this->repository->findAllWithFilters($filters, $perPage);

      $data = [
        'rows' => $paginator->items(),
        'count' => $paginator->total(),
        'currentPage' => $paginator->currentPage(),
        'nextPage' => $paginator->hasMorePages()
          ? $paginator->currentPage() + 1
          : null,
        'previousPage' =>
          $paginator->currentPage() > 1 ? $paginator->currentPage() - 1 : null,
        'limit' => $paginator->perPage(),
        'pages' => $paginator->lastPage(),
      ];

      return response()->json($data);
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
