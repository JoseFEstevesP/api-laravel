<?php

namespace App\Modules\Session\UseCases;

use App\Modules\Session\Repositories\UserSessionRepositoryInterface;
use App\Modules\Session\msg\useMsg;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Http\JsonResponse;

class FindAllUserSessions
{
  private UserSessionRepositoryInterface $repository;

  public function __construct(UserSessionRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(
    ?string $status = null,
    int $perPage = 15,
    int $page = 1,
    ?string $orderProperty = 'login_at',
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

      $paginator = $this->repository->findAllWithFilters($filters, $perPage);

      $transformedRows = collect($paginator->items())->map(function ($session) {
        return [
          'id' => $session->id,
          'user_id' => $session->user_id,
          'ip_address' => $session->ip_address,
          'user_agent' => $session->user_agent,
          'login_at' => $session->login_at,
          'last_activity' => $session->last_activity,
          'expires_at' => $session->expires_at,
          'is_active' => $session->is_active,
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

      AuditLogger::log(
        'sesiones_visualizadas',
        [
          'user_id' => auth()->user() ? auth()->user()->uid : null,
          'limit' => $perPage,
          'total_sesiones' => $paginator->total(),
          'ip_address' => request()->ip(),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
        ],
        auth()->user() ? auth()->user()->uid : null,
        'sesion',
        null,
        'Sesiones de usuario visualizadas correctamente',
      );

      return response()->json($data);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('Session.retrieval_error'),
          ],
        ],
        500,
      );
    }
  }
}
