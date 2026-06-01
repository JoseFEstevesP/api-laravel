<?php

namespace App\Modules\User\UseCases;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\msg\useMsg;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Utils\ErrorResponse;
use Illuminate\Http\JsonResponse;

class FindAllUser
{
  private UserRepositoryInterface $repository;

  public function __construct(UserRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(
    ?string $status = null,
    int $perPage = 20,
    int $page = 1,
    ?string $orderProperty = 'created_at',
    string $order = 'DESC',
    ?string $search = null,
  ): array|JsonResponse {
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

      \Illuminate\Pagination\LengthAwarePaginator::currentPageResolver(
        function () use ($page) {
          return $page;
        },
      );

      /** @var LengthAwarePaginator $paginator */
      $paginator = $this->repository->findAllWithFilters($filters, $perPage);

      $transformedRows = collect($paginator->items())->map(function ($user) {
        return [
          'uid' => $user->uid,
          'names' => $user->names,
          'surnames' => $user->surnames,
          'email' => $user->email,
          'phone' => $user->phone,
          'status' => $user->status,
          'created_at' => $user->created_at,
          'uidRol' => $user->uidRol,
          'role' => $user->role
            ? [
              'name' => $user->role->name,
            ]
            : null,
        ];
      });

      $data = [
        'rows' => $transformedRows->toArray(),
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
            'message' => useMsg::get('User.retrieval_error'),
          ],
        ],
        500,
      );
    }
  }
}
