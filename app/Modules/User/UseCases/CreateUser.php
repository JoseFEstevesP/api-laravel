<?php

namespace App\Modules\User\UseCases;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\msg\useMsg;
use App\Utils\ErrorResponse;
use Illuminate\Http\JsonResponse;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Support\Str;

class CreateUser
{
  private UserRepositoryInterface $userRepository;

  public function __construct(UserRepositoryInterface $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  public function execute(array $data): array|JsonResponse
  {
    if (!isset($data['uid'])) {
      $data['uid'] = (string) Str::uuid();
    }

    if ($this->userRepository->findByEmail($data['email'])) {
      return response()->json(
        [
          'email' => [
            'message' => useMsg::get('validation.email.unique'),
          ],
        ],
        409,
      );
    }

    $user = $this->userRepository->create($data);

    AuditLogger::log(
      'usuario_registrado',
      [
        'email' => $user->email,
        'uidRol' => $user->uidRol,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'url' => request()->fullUrl(),
      ],
      $user->uid,
      'usuario',
      $user->uid,
      'Usuario registrado correctamente',
    );

    return response()->json(
      [
        'message' => useMsg::get('register.success'),
      ],
      201,
    );
  }
}
