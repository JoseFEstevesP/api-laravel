<?php

namespace App\Modules\Rol\UseCases;

use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Http\JsonResponse;

class UpdateRol
{
  private RolRepositoryInterface $rolRepository;

  public function __construct(RolRepositoryInterface $rolRepository)
  {
    $this->rolRepository = $rolRepository;
  }

  public function execute(string $uid, array $data): array|JsonResponse
  {
    $rol = $this->rolRepository->findByUid($uid);

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

    $updatedRol = $this->rolRepository->update($rol, $data);

    AuditLogger::log(
      'rol_actualizado',
      [
        'changes' => $data,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'url' => request()->fullUrl(),
      ],
      auth()->user()?->uid ?? 'system',
      'rol',
      $uid,
      'Rol actualizado correctamente',
    );

    return response()->json([
      'message' => useMsg::get('Rol.updated'),
      'data' => $updatedRol,
    ]);
  }
}
