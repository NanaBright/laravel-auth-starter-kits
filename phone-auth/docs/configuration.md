# Configuration Guide

This guide covers all configuration options for the Custom SMS Authentication System.

## Environment Variables

### Core Application Settings

```env
# Application
APP_NAME="Custom SMS Auth"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phone_auth
DB_USERNAME=root
DB_PASSWORD=
```

### SMS Configuration

```env
# SMS Method Selection
SMS_METHOD=logger  # Options: logger, http_api, email_gateway, smpp

# HTTP API Gateway Settings
SMS_GATEWAY_URL=https://api.your-sms-provider.com/send
SMS_USERNAME=your_username
SMS_PASSWORD=your_password
SMS_SENDER_ID=YourApp

# Email-to-SMS Gateway
SMS_EMAIL_GATEWAY_ENABLED=true
SMS_DEFAULT_CARRIER=verizon

# SMPP Protocol Settings
SMPP_HOST=your.smpp.host
SMPP_PORT=2775
SMPP_USERNAME=your_smpp_username
SMPP_PASSWORD=your_smpp_password

# Logging
SMS_LOG_ALL=false
SMS_LOG_FAILED=true
```

## SMS Methods Configuration

### 1. Logger Method (Development)

Perfect for development and testing.

```env
SMS_METHOD=logger
SMS_LOG_ALL=true
```

**How it works:**
- OTP codes are logged to `storage/logs/laravel.log`
- No external dependencies required
- Check logs: `tail -f storage/logs/laravel.log`

### 2. HTTP API Gateway

Connect to any SMS provider via HTTP API.

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://api.provider.com/send
SMS_USERNAME=your_api_username
SMS_PASSWORD=your_api_password
SMS_SENDER_ID=YourBrand
```

**Supported API formats:**
- Standard REST APIs
- JSON request/response
- Basic authentication
- Custom headers support

### 3. Email-to-SMS Gateway

Send SMS via carrier email gateways.

```env
SMS_METHOD=email_gateway
SMS_EMAIL_GATEWAY_ENABLED=true
SMS_DEFAULT_CARRIER=verizon
```

**Supported carriers:**
- Verizon: `@vtext.com`
- AT&T: `@txt.att.net`
- T-Mobile: `@tmomail.net`
- Sprint: `@messaging.sprintpcs.com`

### 4. SMPP Protocol

Direct connection to SMS carriers.

```env
SMS_METHOD=smpp
SMPP_HOST=carrier.smpp.com
SMPP_PORT=2775
SMPP_USERNAME=your_smpp_user
SMPP_PASSWORD=your_smpp_pass
```

**Requirements:**
- Install: `composer require onlinecity/php-smpp`
- Direct carrier agreement
- SMPP credentials from carrier

## Advanced Configuration

### OTP Settings

Edit `config/sms.php`:

```php
'otp' => [
    'length' => 6,                    // OTP code length
    'expires_in_minutes' => 10,       // OTP expiration time
    'resend_delay_seconds' => 60,     // Delay between resends
    'max_attempts' => 3,              // Max verification attempts
],
```

### Rate Limiting

```php
'rate_limit' => [
    'max_sms_per_hour' => 10,         // SMS per hour per phone
    'max_sms_per_day' => 50,          // SMS per day per phone
    'max_attempts_per_phone' => 5,    // Max attempts per phone
],
```

### Carrier Configuration

```php
'email_gateway' => [
    'enabled' => env('SMS_EMAIL_GATEWAY_ENABLED', false),
    'default_carrier' => env('SMS_DEFAULT_CARRIER', 'verizon'),
    'carriers' => [
        'verizon' => '@vtext.com',
        'att' => '@txt.att.net',
        'tmobile' => '@tmomail.net',
        'sprint' => '@messaging.sprintpcs.com',
        'boost' => '@myboostmobile.com',
        'cricket' => '@sms.cricketwireless.net',
        'metropcs' => '@mymetropcs.com',
    ],
],
```

## SMS Gateway Configurations

### Popular HTTP API Gateways

#### Vonage (Nexmo)
```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://rest.nexmo.com/sms/json
SMS_USERNAME=your_api_key
SMS_PASSWORD=your_api_secret
SMS_SENDER_ID=YourBrand
```

#### Amazon SNS
```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://sns.us-east-1.amazonaws.com/
# Configure AWS credentials separately
```

#### MessageBird
```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://rest.messagebird.com/messages
SMS_USERNAME=your_access_key
SMS_SENDER_ID=YourBrand
```

### Custom Gateway Integration

To add a custom gateway, extend the `CustomSmsService`:

```php
private function sendViaCustomGateway($phoneNumber, $message)
{
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->customApiKey,
        'Content-Type' => 'application/json',
    ])->post($this->customGatewayUrl, [
        'to' => $this->formatPhoneNumber($phoneNumber),
        'message' => $message,
        'from' => $this->senderId,
    ]);

    if ($response->successful()) {
        $data = $response->json();
        return [
            'success' => true,
            'message_id' => $data['id'] ?? null,
            'method' => 'custom_gateway'
        ];
    }

    throw new Exception('Custom gateway error: ' . $response->body());
}
```

## Security Configuration

### CORS Settings

Edit `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        // Add production domains
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

### Sanctum Configuration

Edit `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

## Database Configuration

### Migration Customization

You can customize the database schema by modifying migrations:

```php
// User table customization
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->unique();
    $table->timestamp('phone_verified_at')->nullable();
    $table->integer('failed_otp_attempts')->default(0);
    $table->timestamp('last_otp_request')->nullable();
});

// OTP verification table
Schema::create('otp_verifications', function (Blueprint $table) {
    $table->id();
    $table->string('phone');
    $table->string('otp', 6);
    $table->timestamp('expires_at');
    $table->integer('attempts')->default(0);
    $table->timestamps();
    
    $table->index(['phone', 'otp']);
});
```

## Frontend Configuration

### Vue.js API Configuration

Update `resources/js/pages/Home.vue`:

```javascript
// API base URL configuration
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'

// Request configuration
const defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
}
```

### Vite Configuration

Edit `vite.config.js`:

```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: '0.0.0.0',
        port: 3000,
        cors: true,
    },
});
```

## Production Configuration

### Environment Variables for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use production SMS method
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://api.production-sms-provider.com/send

# Production database
DB_CONNECTION=mysql
DB_HOST=your-production-db-host
DB_DATABASE=phone_auth_prod

# Security
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

### HTTPS Configuration

Ensure your production environment uses HTTPS:

```php
// In AppServiceProvider boot method
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

### Caching Configuration

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Monitoring and Logging

### Log Configuration

Edit `config/logging.php`:

```php
'channels' => [
    'sms' => [
        'driver' => 'daily',
        'path' => storage_path('logs/sms.log'),
        'level' => 'info',
        'days' => 14,
    ],
],
```

### Usage in Code

```php
use Illuminate\Support\Facades\Log;

// Log SMS events
Log::channel('sms')->info('OTP sent', [
    'phone' => $phoneNumber,
    'method' => 'http_api',
    'gateway' => 'provider_name'
]);
```

## Troubleshooting

### Common Issues

1. **SMS not sending**
   - Check SMS method configuration
   - Verify gateway credentials
   - Check logs for errors

2. **CORS errors**
   - Update allowed origins in `config/cors.php`
   - Restart Laravel server after changes

3. **Database connection issues**
   - Verify database credentials
   - Check database server status
   - Run `php artisan migrate:status`

### Debug Mode

Enable debug logging:

```env
APP_DEBUG=true
LOG_LEVEL=debug
SMS_LOG_ALL=true
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

---

For more specific configuration help, see our [SMS Gateway Setup Guide](sms-gateways.md) or open an issue on GitHub.
