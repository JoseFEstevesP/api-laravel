<?php

namespace App\Modules\User\UseCases;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\msg\useMsg;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\Log;
use App\Utils\ErrorResponse;
use Illuminate\Http\JsonResponse;

class DeleteUser
{
  private UserRepositoryInterface $repository;

  public function __construct(UserRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(string $uid): array|JsonResponse
  {
    try {
      $user = $this->repository->findByUid($uid);

      if (!$user) {
        return response()->json(
          [
            'all' => [
              'message' => useMsg::get('User.not_found_one'),
            ],
          ],
          404,
        );
      }

      $this->repository->delete($user);

      AuditLogger::log(
        'usuario_eliminado',
        [
          'deleted_by' => auth()->user()
            ? auth()->user()->uid
            : 'system',
          'ip_address' => request()->ip(),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
        ],
        auth()->user() ? auth()->user()->uid : 'system',
        'usuario',
        $uid,
        'Usuario eliminado correctamente',
      );

      return response()->json(['message' => useMsg::get('User.deleted')], 200);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('User.deletion_error'),
          ],
        ],
        500,
      );
    }
  }
}
