<?php

namespace App\Modules\Rol\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use App\Modules\Rol\Repositories\RolRepositoryInterface;
use App\Modules\Rol\Repositories\RolRepository;
use App\Modules\Rol\Repositories\RolRepositoryCacheDecorator;
use App\Modules\Rol\Middleware\PermissionMiddleware;
use App\Modules\Rol\Services\PermissionService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Service Provider para el módulo Rol
 *
 * Registra las dependencias y configuraciones específicas del módulo Rol
 * en el contenedor de servicios de Laravel. También se encarga de cargar
 * las migraciones necesarias para el módulo.
 */
class RolServiceProvider extends ServiceProvider
{
  /**
   * Namespace base para los controladores del módulo
   * @var string
   */
  protected $namespace = 'App\\Modules\\Rol\\Controllers';

  /**
   * Register any application services.
   *
   * Registra los bindings en el contenedor de dependencias para:
   * - RolRepositoryInterface con cache decorator
   * - Configuración de TTL para cache de Rol
   *
   * @return void
   */
  public function register()
  {
    // Registrar binding del repositorio de Rol con cache decorator
    // El interface se vincula a un decorator de cache que envuelve el repositorio concreto.
    // Esto mantiene los casos de uso/controladores dependiendo del interface mientras agrega cache.
    $this->app->bind(RolRepositoryInterface::class, function ($app) {
      /** @var RolRepository $repo Repositorio concreto de Rol */
      $repo = $app->make(RolRepository::class);

      /** @var CacheRepository $cache Instancia del sistema de cache de Laravel */
      $cache = $app->make(CacheRepository::class);

      // TTL para entradas en cache en segundos. Puede ser sobreescrito con config('cache.Rol_ttl')
      $ttl = (int) config('cache.Rol_ttl', 300); // 300 segundos = 5 minutos por defecto

      // Retornar el decorator de cache que envuelve el repositorio concreto
      return new RolRepositoryCacheDecorator($repo, $cache, $ttl);
    });

    $this->app->singleton(PermissionService::class, PermissionService::class);
  }

  /**
   * Bootstrap any application services.
   *
   * Carga las migraciones del módulo Rol y realiza otras configuraciones
   * necesarias para el funcionamiento del módulo.
   *
   * @return void
   */
  public function boot(Router $router)
  {
    // Registrar middleware de permisos
    $router->aliasMiddleware('permission', PermissionMiddleware::class);

    // Cargar migraciones desde el directorio del módulo
    // Esto permite tener migraciones específicas del módulo de Rol
    $this->loadMigrationsFrom(__DIR__ . '/../Migrations');

    // Nota: Futuras extensiones podrían incluir:
    // - $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    // - $this->loadViewsFrom(__DIR__ . '/../Views', 'Rol');
    // - $this->loadTranslationsFrom(__DIR__ . '/../Lang', 'Rol');
  }
}
