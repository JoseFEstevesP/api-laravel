<?php

namespace App\Modules\User\Controllers;

use App\Modules\User\UseCases\CreateUser;
use App\Modules\User\UseCases\DeleteUser;
use App\Modules\User\UseCases\FindAllUser;
use App\Modules\User\UseCases\LoginUser;
use App\Modules\User\UseCases\LogoutUser;
use App\Modules\User\UseCases\RefreshToken;
use App\Modules\User\UseCases\UpdateUser;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
  private LoginUser $loginUserUseCase;
  private LogoutUser $logoutUserUseCase;
  private RefreshToken $refreshTokenUseCase;
  private CreateUser $createUserUseCase;
  private UpdateUser $updateUserUseCase;
  private DeleteUser $deleteUserUseCase;
  private FindAllUser $findAllUserUseCase;

  public function __construct(
    LoginUser $loginUserUseCase,
    LogoutUser $logoutUserUseCase,
    RefreshToken $refreshTokenUseCase,
    CreateUser $createUserUseCase,
    UpdateUser $updateUserUseCase,
    DeleteUser $deleteUserUseCase,
    FindAllUser $findAllUserUseCase,
  ) {
    $this->loginUserUseCase = $loginUserUseCase;
    $this->logoutUserUseCase = $logoutUserUseCase;
    $this->refreshTokenUseCase = $refreshTokenUseCase;
    $this->createUserUseCase = $createUserUseCase;
    $this->updateUserUseCase = $updateUserUseCase;
    $this->deleteUserUseCase = $deleteUserUseCase;
    $this->findAllUserUseCase = $findAllUserUseCase;
  }

  public function login(LoginRequest $request): JsonResponse
  {
    return $this->loginUserUseCase->execute($request);
  }

  public function logout(): JsonResponse
  {
    return $this->logoutUserUseCase->execute(auth()->user());
  }

  public function refresh(Request $request): JsonResponse
  {
    $refreshToken = $request->cookie('refreshToken');
    return $this->refreshTokenUseCase->execute($refreshToken);
  }

  public function register(RegisterRequest $request): JsonResponse
  {
    return $this->createUserUseCase->execute($request->validated());
  }

  public function index(Request $request): JsonResponse
  {
    return $this->findAllUserUseCase->execute(
      status: $request->get('status'),
      perPage: (int) $request->get('perPage', 20),
      page: (int) $request->get('page', 1),
      orderProperty: $request->get('orderProperty', 'created_at'),
      order: $request->get('order', 'DESC'),
      search: $request->get('search'),
    );
  }

  public function update(UpdateUserRequest $request, string $uid): JsonResponse
  {
    return $this->updateUserUseCase->execute($uid, $request->validated());
  }

  public function destroy(string $uid): JsonResponse
  {
    return $this->deleteUserUseCase->execute($uid);
  }
}
