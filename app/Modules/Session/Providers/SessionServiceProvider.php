<?php

namespace App\Modules\Session\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Session\Repositories\UserSessionRepositoryInterface;
use App\Modules\Session\Repositories\UserSessionRepository;

class SessionServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->bind(
      UserSessionRepositoryInterface::class,
      UserSessionRepository::class,
    );
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    // Cargar migraciones desde el directorio del módulo
    $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
  }
}
