# Filament API Login

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hakimace/filament-api-login.svg?style=flat-square)](https://packagist.org/packages/hakimace/filament-api-login)
[![Total Downloads](https://img.shields.io/packagist/dt/hakimace/filament-api-login.svg?style=flat-square)](https://packagist.org/packages/hakimace/filament-api-login)

Token-based authentication for FilamentPHP that authenticates against external APIs without requiring local database users.
based on [filament-api-login](https://github.com/kristiansnts/filament-api-login)

## Features

- 🔐 **External API Authentication** - Authenticate users against your existing API
- 🚫 **No Local Users** - No need for local database user records
- 🎫 **Token-Based** - Secure session management with API tokens
- 🔧 **Easy Setup** - Simple configuration and installation
- 📝 **Fully Customizable** - Customize API requests, user mapping, and access control

## Installation

You can install the package via Composer:

```bash
composer require hakimace/filament-api-login
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-api-login-config"
```

## Configuration

### 1. Environment Variables

Add these variables to your `.env` file:

```env
FILAMENT_API_LOGIN_URL=https://your-api.com/api/auth
FILAMENT_API_LOGIN_TIMEOUT=30
FILAMENT_API_LOGIN_LOG_FAILURES=true
```

### 2. Authentication Guard

Add the external guard to your `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'external' => [
        'driver' => 'external_session',
    ],
],
```

### 3. Filament Panel Configuration

Update your Filament Panel Provider to use the plugin:

```php
<?php

namespace App\Providers\Filament;

use Hakimace\FilamentApiLogin\FilamentApiLoginPlugin;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(FilamentApiLoginPlugin::make()) // Easy setup
            ->colors([
                'primary' => Color::Amber,
            ])
            // ... rest of your configuration
    }
}
```

### Advanced Usage

You can customize the plugin configuration:

```php
->plugin(
    FilamentApiLoginPlugin::make()
        ->authGuard('external') // Default is 'external'
        ->userMapping([
            'id' => 'operator_id',   // API field for ID
            'name' => 'display_name', // API field for Name
            'email' => 'login_email', // API field for Email
        ])
)
```

## Usage

### Basic Authentication Flow

1. User enters credentials on the Filament login page
2. Package sends credentials to your external API
3. API validates and returns token + user data
4. Package stores token and user data in session
5. User is authenticated and can access Filament

### API Response Format

Your external API should return a response in this format:

```json
{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "data": {
        "id": "123",
        "email": "user@example.com",
        "username": "john_doe",
        "role": "admin"
    }
}
```

### Customizing API Requests

You can customize the API request by extending the `ExternalAuthService`:

```php
<?php

namespace App\Services;

use Hakimace\FilamentApiLogin\Services\ExternalAuthService as BaseService;

class CustomExternalAuthService extends BaseService
{
    public function authenticate(string $email, string $password): ?array
    {
        // Add custom headers, modify request format, etc.
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-API-Key' => config('app.api_key'),
            ])
            ->post($this->apiUrl, [
                'email' => $email, // or 'username' => $email
                'password' => $password,
                'client_id' => config('app.client_id'),
            ]);

        // Custom response handling
        if ($response->successful()) {
            $userData = $response->json();

            if (isset($userData['token']) && isset($userData['data'])) {
                return $userData;
            }
        }

        return null;
    }
}
```

Then bind your custom service in a service provider:

```php
$this->app->bind(
    \Hakimace\FilamentApiLogin\Services\ExternalAuthService::class,
    \App\Services\CustomExternalAuthService::class
);
```

### Customizing User Access Control

Override the `canAccessPanel` method in your panel configuration:

```php
use Hakimace\FilamentApiLogin\Auth\SessionUser;

// In your Panel Provider
->authGuard('external')
->middleware([
    // ... other middleware
    function ($request, $next) {
        $user = auth('external')->user();
        if ($user && !in_array($user->role, ['admin', 'moderator'])) {
            abort(403, 'Access denied');
        }
        return $next($request);
    }
])
```

## Configuration Options

The package configuration file includes these options:

- `api_url` - Your external authentication API endpoint (env: `FILAMENT_API_LOGIN_URL`)
- `timeout` - API request timeout in seconds (env: `FILAMENT_API_LOGIN_TIMEOUT`)
- `log_failures` - Enable/disable logging of authentication failures (env: `FILAMENT_API_LOGIN_LOG_FAILURES`)

## Security Considerations

- ✅ API URL stored securely in environment variables
- ✅ No passwords stored locally
- ✅ Secure session management with Laravel's built-in security
- ✅ Token-based authentication
- ✅ Session regeneration on successful login
- ✅ Configurable request timeouts
- ✅ Failed attempt logging for monitoring

## Troubleshooting

### Common Issues

1. **API Connection Issues**: Check your `FILAMENT_API_LOGIN_URL` and network connectivity
2. **Authentication Failures**: Verify your API response format matches the expected structure
3. **Session Issues**: Ensure your session driver is properly configured

### Debug Logging

Enable logging in the configuration to debug authentication issues:

```php
'log_failures' => true,
```

Or via environment variable:

```env
FILAMENT_API_LOGIN_LOG_FAILURES=true
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [hakimace](https://github.com/hakimace)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
