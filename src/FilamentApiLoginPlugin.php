<?php

declare(strict_types=1);

namespace Hakimace\FilamentApiLogin;

use Hakimace\FilamentApiLogin\Pages\Auth\Login;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentApiLoginPlugin implements Plugin
{
  protected string $authGuard = 'external';

  protected array $userMapping = [
    'id' => 'id',
    'name' => 'name',
    'email' => 'email',
    'role' => 'role',
  ];

  public function getId(): string
  {
    return 'filament-api-login';
  }

  public static function make(): static
  {
    return app(static::class);
  }

  public static function get(): static
  {
    try {
      /** @var static $plugin */
      $plugin = filament(app(static::class)->getId());
    } catch (\Exception $e) {
      /** @var static $plugin */
      $plugin = app(static::class);
    }

    return $plugin;
  }

  public function authGuard(string $guard): static
  {
    $this->authGuard = $guard;

    return $this;
  }

  public function getAuthGuard(): string
  {
    return $this->authGuard;
  }

  public function userMapping(array $mapping): static
  {
    $this->userMapping = array_merge($this->userMapping, $mapping);

    return $this;
  }

  public function getUserMapping(): array
  {
    return $this->userMapping;
  }

  public function register(Panel $panel): void
  {
    $panel
      ->login(Login::class)
      ->authGuard($this->getAuthGuard());
  }

  public function boot(Panel $panel): void
  {
    //
  }
}
