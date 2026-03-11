<?php

declare(strict_types=1);

namespace Hakimace\FilamentApiLogin;

use Hakimace\FilamentApiLogin\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class FilamentApiLoginServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->mergeConfigFrom(__DIR__ . '/../config/filament-api-login.php', 'filament-api-login');

    $this->app->singleton(Services\ExternalAuthService::class, function ($app) {
      return new Services\ExternalAuthService(
        apiUrl: config('filament-api-login.api_url'),
        timeout: config('filament-api-login.timeout', 30)
      );
    });

    // Register the plugin
    $this->app->singleton(FilamentApiLoginPlugin::class, function () {
      return new FilamentApiLoginPlugin();
    });
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Register the custom authentication guard
    Auth::extend('external_session', function ($app, $name, $config) {
      return new SessionGuard(
        $app['request'],
        $app['session.store']
      );
    });

    // Publish the config file
    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__ . '/../config/filament-api-login.php' => config_path('filament-api-login.php'),
      ], 'filament-api-login-config');
    }
  }
}