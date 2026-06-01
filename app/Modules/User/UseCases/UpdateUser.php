<?php

namespace App\Modules\User\UseCases;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\msg\useMsg;
use Illuminate\Http\JsonResponse;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\Log;

class UpdateUser
{
  private UserRepositoryInterface $repository;

  public function __construct(UserRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function execute(string $uid, array $data): array|JsonResponse
  {
    Log::info('=== UpdateUser USE CASE START ===', [
      'uid' => $uid,
      'data' => $data,
    ]);

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

    if (isset($data['email'])) {
      $existingUserWithEmail = $this->repository->findByEmail($data['email']);
      if (
        $existingUserWithEmail &&
        $existingUserWithEmail->uid !== $uid
      ) {
        return response()->json(
          [
            'email' => [
              'message' => useMsg::get('validation.email.unique'),
            ],
          ],
          409,
        );
      }
    }

    try {
      Log::info('UpdateUser - Before repository update. User UID: ' . $uid, $data);

      $updatedUser = $this->repository->update($user, $data);

      Log::info(
        'UpdateUser - After repository update. Updated user:',
        $updatedUser->toArray(),
      );

      AuditLogger::log(
        'usuario_actualizado',
        [
          'updated_by' => auth()->user()
            ? auth()->user()->uid
            : 'system',
          'changes' => $data,
          'ip_address' => request()->ip(),
          'user_agent' => request()->userAgent(),
          'url' => request()->fullUrl(),
        ],
        auth()->user() ? auth()->user()->uid : 'system',
        'usuario',
        $updatedUser->uid,
        'Usuario actualizado correctamente',
      );

      return response()->json([
        'message' => useMsg::get('User.updated'),
        'data' => $updatedUser,
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'all' => [
            'message' => useMsg::get('User.update_error'),
          ],
        ],
        500,
      );
    }
  }
}
