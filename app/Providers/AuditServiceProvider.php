<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\DispatchingAudit;
use App\Modules\Audit\Listeners\AuditSecurityListener;

class AuditServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // Register the audit event listener
    Event::listen(DispatchingAudit::class, AuditSecurityListener::class);

    // Bind custom resolvers to the container for easy access
    $this->app->bind('audit.resolver.request_context', function () {
      return config(
        'audit.resolvers.request_context',
        \App\Modules\Audit\Resolvers\RequestContextResolver::class,
      );
    });

    $this->app->bind('audit.resolver.action_context', function () {
      return config(
        'audit.resolvers.action_context',
        \App\Modules\Audit\Resolvers\ActionContextResolver::class,
      );
    });
  }
}
