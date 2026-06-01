<?php

namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Repositories\UserRepositoryCacheDecorator;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

use App\Modules\User\Services\RefreshTokenService;

/**
 * Service Provider para el módulo de Usuarios
 *
 * Registra las dependencias y configuraciones específicas del módulo User
 * en el contenedor de servicios de Laravel. También se encarga de cargar
 * las migraciones necesarias para el módulo.
 */
class UserServiceProvider extends ServiceProvider
{
  /**
   * Namespace base para los controladores del módulo
   * @var string
   */
  protected $namespace = 'App\Modules\User\Controllers';

  /**
   * Register any application services.
   *
   * Registra los bindings en el contenedor de dependencias para:
   * - UserRepositoryInterface con cache decorator
   * - Configuración de TTL para cache de usuarios
   *
   * @return void
   */
  public function register()
  {
    // Registrar RefreshTokenService como un singleton
    $this->app->singleton(RefreshTokenService::class, function ($app) {
      return new RefreshTokenService();
    });

    // Registrar binding del repositorio de usuario con cache decorator
    // El interface se vincula a un decorator de cache que envuelve el repositorio concreto.
    // Esto mantiene los casos de uso/controladores dependiendo del interface mientras agrega cache.
    $this->app->bind(UserRepositoryInterface::class, function ($app) {
      /** @var UserRepository $repo Repositorio concreto de usuarios */
      $repo = $app->make(UserRepository::class);

      /** @var CacheRepository $cache Instancia del sistema de cache de Laravel */
      $cache = $app->make(CacheRepository::class);

      // TTL para entradas en cache en segundos. Puede ser sobreescrito con config('cache.user_ttl')
      $ttl = (int) config('cache.cache_ttl', 300); // 300 segundos = 5 minutos por defecto

      // Retornar el decorator de cache que envuelve el repositorio concreto
      return new UserRepositoryCacheDecorator($repo, $cache, $ttl);
    });
  }

  /**
   * Bootstrap any application services.
   *
   * Carga las migraciones del módulo User y realiza otras configuraciones
   * necesarias para el funcionamiento del módulo.
   *
   * @return void
   */
  public function boot()
  {
    // Cargar migraciones desde el directorio del módulo
    // Esto permite tener migraciones específicas del módulo de usuarios
    $this->loadMigrationsFrom(__DIR__ . '/../Migrations');

    // Nota: Futuras extensiones podrían incluir:
    // - $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    // - $this->loadViewsFrom(__DIR__ . '/../Views', 'user');
    // - $this->loadTranslationsFrom(__DIR__ . '/../Lang', 'user');
  }
}
