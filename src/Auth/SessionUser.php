<?php

declare(strict_types=1);

namespace Hakimace\FilamentApiLogin\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Hakimace\FilamentApiLogin\FilamentApiLoginPlugin;
use Illuminate\Contracts\Auth\Authenticatable;

class SessionUser implements Authenticatable, FilamentUser
{
  protected array $attributes;

  public function __construct(array $attributes)
  {
    $this->attributes = $attributes;
  }

  public function getAuthIdentifierName(): string
  {
    return 'id';
  }

  public function getAuthIdentifier()
  {
    return $this->attributes['id'] ?? null;
  }

  public function getAuthPassword(): string
  {
    return '';
  }

  public function getAuthPasswordName(): string
  {
    return 'password';
  }

  public function getRememberToken(): string
  {
    return '';
  }

  public function setRememberToken($value): void
  {
    // Not implemented for session-based auth
  }

  public function getRememberTokenName(): string
  {
    return '';
  }

  public function canAccessPanel(Panel $panel): bool
  {
    return true; // Allow access for all authenticated users
  }

  public function __get($key)
  {
    return $this->getAttributeValue($key);
  }

  public function __isset($key)
  {
    return isset($this->attributes[$key]);
  }

  public function getAttributeValue($key)
  {
    $mapping = FilamentApiLoginPlugin::get()->getUserMapping();

    // Map common attribute names based on plugin configuration
    if ($key === 'id') {
      return $this->attributes[$mapping['id'] ?? 'id'] ?? null;
    }

    if ($key === 'name') {
      return $this->attributes[$mapping['name'] ?? 'name'] ??
        $this->attributes[$mapping['username'] ?? 'username'] ??
        null;
    }

    if ($key === 'email') {
      return $this->attributes[$mapping['email'] ?? 'email'] ?? null;
    }

    return $this->attributes[$key] ?? null;
  }

  public function getAttribute($key)
  {
    return $this->getAttributeValue($key);
  }

  public function toArray(): array
  {
    return $this->attributes;
  }

  public function getEmail(): string
  {
    return (string) $this->getAttributeValue('email');
  }

  public function getName(): string
  {
    return (string) $this->getAttributeValue('name');
  }

  public function getFilamentName(): string
  {
    return $this->getName();
  }

  public function getKey()
  {
    return $this->getAuthIdentifier();
  }
}