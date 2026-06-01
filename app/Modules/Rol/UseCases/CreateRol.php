<?php

namespace App\Modules\Rol\UseCases;

use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CreateRol
{
  private RolRepositoryInterface $rolRepository;

  public function __construct(RolRepositoryInterface $rolRepository)
  {
    $this->rolRepository = $rolRepository;
  }

  public function execute(array $data): array|JsonResponse
  {
    if (!isset($data['uid'])) {
      $data['uid'] = (string) Str::uuid();
    }

    $rol = $this->rolRepository->create($data);

    AuditLogger::log(
      'rol_creado',
      [
        'name' => $rol->name,
        'permissions' => $rol->permissions,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'url' => request()->fullUrl(),
      ],
      auth()->user()?->uid ?? 'system',
      'rol',
      $rol->uid,
      'Rol creado correctamente',
    );

    return response()->json(
      [
        'message' => useMsg::get('Rol.created'),
        'data' => $rol,
      ],
      201,
    );
  }
}
