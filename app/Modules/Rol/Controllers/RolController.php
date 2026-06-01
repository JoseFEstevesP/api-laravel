<?php

namespace App\Modules\Rol\Controllers;

use App\Modules\Rol\UseCases\CreateRol;
use App\Modules\Rol\UseCases\UpdateRol;
use App\Modules\Rol\Requests\CreateRolRequest;
use App\Modules\Rol\Requests\UpdateRolRequest;
use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\msg\useMsg;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolController extends Controller
{
  private CreateRol $createRolUseCase;
  private UpdateRol $updateRolUseCase;
  private RolRepositoryInterface $rolRepository;

  public function __construct(
    CreateRol $createRolUseCase,
    UpdateRol $updateRolUseCase,
    RolRepositoryInterface $rolRepository,
  ) {
    $this->createRolUseCase = $createRolUseCase;
    $this->updateRolUseCase = $updateRolUseCase;
    $this->rolRepository = $rolRepository;
  }

  public function index(Request $request): JsonResponse
  {
    $search = $request->get('search');
    $perPage = (int) $request->get('perPage', 20);

    $roles = $this->rolRepository->paginate($perPage, $search);

    return response()->json($roles);
  }

  public function show(string $uid): JsonResponse
  {
    $rol = $this->rolRepository->findByUid($uid);

    if (!$rol) {
      return response()->json(
        [
          'all' => ['message' => useMsg::get('Rol.not_found')],
        ],
        404,
      );
    }

    return response()->json(['data' => $rol]);
  }

  public function store(CreateRolRequest $request): JsonResponse
  {
    return $this->createRolUseCase->execute($request->validated());
  }

  public function update(UpdateRolRequest $request, string $uid): JsonResponse
  {
    return $this->updateRolUseCase->execute($uid, $request->validated());
  }

  public function destroy(string $uid): JsonResponse
  {
    $rol = $this->rolRepository->findByUid($uid);

    if (!$rol) {
      return response()->json(
        [
          'all' => ['message' => useMsg::get('Rol.not_found')],
        ],
        404,
      );
    }

    $this->rolRepository->delete($rol);

    return response()->json(
      ['message' => useMsg::get('Rol.deleted')],
      200,
    );
  }
}
