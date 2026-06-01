<?php

namespace App\Modules\Session\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Session\UseCases\FindAllUserSessions;
use App\Modules\Session\UseCases\DeleteUserSession;
use App\Modules\Session\UseCases\CheckActiveSession;

class SessionController extends Controller
{
  private FindAllUserSessions $findAllUseCase;
  private DeleteUserSession $deleteUseCase;
  private CheckActiveSession $checkActiveSessionUseCase;

  public function __construct(
    FindAllUserSessions $findAllUseCase,
    DeleteUserSession $deleteUseCase,
    CheckActiveSession $checkActiveSessionUseCase,
  ) {
    $this->findAllUseCase = $findAllUseCase;
    $this->deleteUseCase = $deleteUseCase;
    $this->checkActiveSessionUseCase = $checkActiveSessionUseCase;
  }

  public function index()
  {
    $status = request()->query('status');
    $limit = request()->query('limit', 15);
    $page = request()->query('page', 1);
    $orderProperty = request()->query('orderProperty', 'login_at');
    $order = request()->query('order', 'DESC');
    $search = request()->query('search');

    return $this->findAllUseCase->execute(
      $status,
      (int) $limit,
      (int) $page,
      $orderProperty,
      $order,
      $search,
    );
  }

  public function destroy($id)
  {
    return $this->deleteUseCase->execute((int) $id);
  }

  public function check()
  {
    return $this->checkActiveSessionUseCase->execute();
  }
}
