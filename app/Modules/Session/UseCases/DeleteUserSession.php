<?php

namespace App\Modules\Session\UseCases;

use App\Modules\Session\Repositories\UserSessionRepositoryInterface;
use App\Modules\Session\msg\useMsg;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Http\JsonResponse;

class DeleteUserSession
{
  private UserSessionRepositoryInterface $repository;

  public function __construct(UserSessionRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(int $id): array|JsonResponse
  {
    try {
      $result = $this->repository->delete($id);

      if (!$result) {
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('Session.deletion_error'),
            ],
          ],
          404,
        );
      }

      AuditLogger::log(
        'sesion_eliminada',
        [
          'session_id' => $id,
          'deleted_by' => auth()->user() ? auth()->user()->uid : null,
          'ip_address' => request()->ip(),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
        ],
        auth()->user() ? auth()->user()->uid : null,
        'sesion',
        $id,
        'Sesión de usuario eliminada correctamente',
      );

      return response()->json([
        'message' => useMsg::get('Session.deleted'),
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('Session.deletion_error'),
          ],
        ],
        500,
      );
    }
  }
}
