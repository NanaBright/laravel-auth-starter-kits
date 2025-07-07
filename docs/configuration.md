# Configuration Guide

This guide covers all configuration options for Laravel Auth Starter Kits, allowing you to customize each authentication kit to fit your specific needs.

## 📋 Basic Configuration

### Environment Variables

The `.env` file is the primary place to configure your application. Here are the key settings:

```
# Application
APP_NAME="Laravel Auth"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_auth
DB_USERNAME=root
DB_PASSWORD=

# SMS Gateway
SMS_PROVIDER=vonage
VONAGE_KEY=your_key
VONAGE_SECRET=your_secret
VONAGE_FROM="Laravel Auth"
```

### Configuration Files

All authentication kits use the following configuration files:

- `config/auth.php` - Authentication settings
- `config/sms.php` - SMS provider settings (Phone Auth)
- `config/services.php` - Third-party service credentials

## 📱 Phone Authentication Configuration

### SMS Provider Selection

Configure your SMS gateway in `config/sms.php`:

```php
'default' => env('SMS_PROVIDER', 'vonage'),

'providers' => [
    'vonage' => [
        'key' => env('VONAGE_KEY'),
        'secret' => env('VONAGE_SECRET'),
        'from' => env('VONAGE_FROM', 'Laravel Auth'),
    ],
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],
    'sns' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'sender_id' => env('AWS_SNS_SENDER_ID', 'Laravel'),
    ],
    'custom' => [
        // Custom provider configuration
    ],
],
```

### OTP Configuration

Customize One-Time Password (OTP) settings:

```php
'otp' => [
    'length' => 6, // OTP code length
    'expiry' => 10, // Expiry time in minutes
    'type' => 'numeric', // 'numeric', 'alpha', 'alphanumeric'
    'case_sensitive' => false, // For alpha and alphanumeric types
],
```

### Rate Limiting

Configure rate limiting to prevent abuse:

```php
'rate_limiting' => [
    'enabled' => true,
    'max_attempts' => [
        'send' => 5, // Max SMS sending attempts
        'verify' => 10, // Max verification attempts
    ],
    'decay_minutes' => 10, // Time window in minutes
    'lockout_time' => 30, // Lockout time in minutes after max attempts
],
```

### Phone Number Formatting

Configure phone number handling:

```php
'phone' => [
    'format' => 'E164', // 'E164', 'INTERNATIONAL', 'NATIONAL'
    'regions' => ['US', 'CA'], // Allowed country codes, empty for all
    'require_plus' => true, // Require + prefix for international numbers
    'verify_region' => true, // Verify number belongs to allowed regions
],
```

### Message Templates

Customize SMS message templates:

```php
'messages' => [
    'verification' => 'Your verification code is: :otp. Valid for :minutes minutes.',
    'welcome' => 'Welcome to :app_name! Your account is now verified.',
    'reset' => 'Your password reset code is: :otp. Valid for :minutes minutes.',
],
```

## 🔧 Advanced Configuration

### Custom SMS Service Provider

To create your own SMS provider:

1. Create a service class that implements `App\Services\SmsServiceInterface`:

```php
namespace App\Services;

class CustomSmsService implements SmsServiceInterface
{
    public function send(string $to, string $message): bool
    {
        // Implementation for your SMS gateway
    }
}
```

2. Register the service in `config/sms.php`:

```php
'providers' => [
    // Other providers...
    'custom' => [
        'class' => App\Services\CustomSmsService::class,
        'api_key' => env('CUSTOM_SMS_API_KEY'),
        // Additional configuration...
    ],
],
```

3. Update `.env` to use your provider:

```
SMS_PROVIDER=custom
CUSTOM_SMS_API_KEY=your_api_key
```

### Fallback Providers

Configure fallback SMS providers:

```php
'fallbacks' => [
    'enabled' => true,
    'providers' => ['vonage', 'twilio', 'sns'],
    'max_attempts' => 3, // Max fallback attempts
],
```

### Authentication Guards

Customize authentication guards in `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

### User Providers

Configure user providers in `config/auth.php`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

### Session Configuration

Customize session behavior in `config/session.php`:

```php
'lifetime' => 120, // Session lifetime in minutes
'expire_on_close' => false, // Expire session on browser close
'secure' => env('SESSION_SECURE_COOKIE', false), // HTTPS only
```

## 🛠️ Environment-Specific Configuration

### Development

For local development:

```
APP_ENV=local
APP_DEBUG=true
DEBUGBAR_ENABLED=true
SMS_TEST_MODE=true
SMS_TEST_VERIFICATION_CODE=123456
```

### Testing

For testing environments:

```
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SMS_TEST_MODE=true
```

### Production

For production environments:

```
APP_ENV=production
APP_DEBUG=false
SMS_TEST_MODE=false
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=yourdomain.com
```

## 📊 Logging and Monitoring

### SMS Logging

Configure SMS logging:

```php
'logging' => [
    'enabled' => true,
    'channel' => env('SMS_LOG_CHANNEL', 'daily'),
    'level' => env('SMS_LOG_LEVEL', 'info'),
    'include_content' => env('SMS_LOG_CONTENT', false), // Log message content
],
```

### Analytics

Configure analytics:

```php
'analytics' => [
    'enabled' => true,
    'track_deliveries' => true,
    'track_failures' => true,
    'track_costs' => true,
    'storage' => 'database', // 'database', 'redis', or 'custom'
],
```

## 🔒 Security Configuration

### CORS Configuration

Configure CORS in `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // Restrict in production!
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

### API Rate Limiting

Configure API rate limiting in `app/Http/Kernel.php`:

```php
'api' => [
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

// Throttle middleware definition
protected $routeMiddleware = [
    // Other middleware...
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];
```

### Sanctum Configuration

Configure Sanctum in `config/sanctum.php`:

```php
'stateful' => [
    'localhost',
    'localhost:3000',
    'localhost:8000',
    '127.0.0.1',
    '127.0.0.1:8000',
    '::1',
    // Add your production domains
],

'expiration' => 60 * 24 * 7, // Token expiration in minutes (7 days)
```

## 🎨 Frontend Configuration

### Tailwind Configuration

Customize Tailwind CSS in `tailwind.config.js`:

```js
module.exports = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    // Other shades...
                    900: '#0c4a6e',
                },
                // Other color scales...
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
```

### Vue Configuration

Customize Vue.js in `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
```

## 🧩 Module Configuration

### Feature Flags

Configure feature flags:

```php
'features' => [
    'social_login' => false,
    'remember_me' => true,
    'password_reset' => true,
    'account_deletion' => true,
    'two_factor' => false,
],
```

### Localization

Configure localization:

```php
'localization' => [
    'enabled' => true,
    'default_locale' => 'en',
    'available_locales' => ['en', 'es', 'fr', 'de'],
    'detect_from_browser' => true,
],
```

## 🚀 Next Steps

- Explore the [API Documentation](api.md)
- Check out the [Frontend Guide](frontend.md)
- Read the [Security Guide](security.md)
- Learn about [Deployment Options](deployment.md)

---

**Need help?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
