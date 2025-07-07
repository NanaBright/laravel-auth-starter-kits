# Security Guide

This comprehensive security guide covers best practices for securing Laravel Auth Starter Kits in production environments.

## 📋 Security Overview

Laravel Auth Starter Kits are built with security as a top priority. The following security features are included by default:

1. **Authentication Security** - Secure implementation of authentication flows
2. **Rate Limiting** - Protection against brute force attacks
3. **Input Validation** - Thorough validation of all user inputs
4. **CSRF Protection** - Cross-Site Request Forgery prevention
5. **XSS Protection** - Cross-Site Scripting protection
6. **SQL Injection Prevention** - Parameterized queries
7. **Secure Headers** - HTTP security headers
8. **Session Security** - Secure session handling

## 🔒 Authentication Security

### OTP Authentication Security (Phone Auth)

#### OTP Implementation Best Practices

- **Time-Limited OTPs** - All OTPs have a configurable expiry time (default: 10 minutes)
- **OTP Length** - Default 6 digits, configurable up to 8 for increased security
- **Rate Limiting** - Limited attempts (default: 5 per 10 minutes)
- **Secure Storage** - OTPs are stored as hashes, never in plain text
- **Stateless Generation** - OTPs can be stateless using HMAC-based algorithm

#### Configuration

In `config/sms.php`:

```php
'otp' => [
    'length' => env('OTP_LENGTH', 6),
    'expiry' => env('OTP_EXPIRY', 10), // minutes
    'algorithm' => env('OTP_ALGORITHM', 'random'), // 'random', 'hmac', 'totp'
    'case_sensitive' => false,
],
```

#### Securing OTP Delivery

- **Provider Redundancy** - Multiple SMS providers for failover
- **Secure API Keys** - Environment variable storage for API credentials
- **Masked Display** - Only show last few digits of phone numbers
- **Delivery Tracking** - Monitor delivery rates and anomalies

### Authentication Flows

#### Phone Verification Flow

1. User enters phone number
2. Rate limiting check performed
3. OTP generated and stored securely
4. SMS sent via secure gateway
5. User enters OTP
6. OTP verified (within time limit)
7. Authentication token generated

#### Account Recovery

- **Multiple Channel Verification** - Secondary verification method where available
- **Graduated Access** - Limited access until additional verification
- **Activity Logging** - Log all recovery attempts
- **Notification** - Alert user of account recovery attempts

## 🔄 Rate Limiting

### Implementation

Laravel Auth Starter Kits use Laravel's built-in rate limiting middleware:

```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    // ...
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];

// routes/api.php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/phone/send-otp', [AuthController::class, 'sendOtp']);
});

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/phone/verify', [AuthController::class, 'verifyOtp']);
});
```

### Custom Rate Limiters

Custom rate limiters for specific authentication actions:

```php
// app/Providers/RouteServiceProvider.php
public function boot()
{
    $this->configureRateLimiting();
}

protected function configureRateLimiting()
{
    RateLimiter::for('sms', function (Request $request) {
        // Different limits based on environment
        $maxAttempts = config('app.env') === 'production' ? 5 : 10;
        
        return Limit::perMinute($maxAttempts)->by($request->phone);
    });
    
    RateLimiter::for('otp-verify', function (Request $request) {
        return Limit::perMinute(10)->by($request->phone);
    });
}
```

### IP-based Rate Limiting

```php
RateLimiter::for('global-api', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});
```

### Graduated Rate Limiting

Implement graduated rate limiting for suspicious behavior:

```php
// app/Services/RateLimitingService.php
public function getOtpAttemptLimit($phone)
{
    $attemptCount = $this->getRecentAttemptCount($phone);
    
    // Gradually reduce limit with increasing attempts
    if ($attemptCount > 20) return 1; // 1 per hour
    if ($attemptCount > 10) return 2; // 2 per hour
    if ($attemptCount > 5) return 5; // 5 per hour
    
    return 10; // Default
}
```

## 🛡️ Input Validation

### Form Request Validation

All inputs are validated using Laravel's Form Request Validation:

```php
// app/Http/Requests/SendOtpRequest.php
class SendOtpRequest extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', 'string', new PhoneNumber],
        ];
    }
}

// app/Http/Requests/VerifyOtpRequest.php
class VerifyOtpRequest extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', 'string', new PhoneNumber],
            'code' => ['required', 'string', 'size:' . config('sms.otp.length')],
        ];
    }
}
```

### Custom Validation Rules

Custom validation for phone numbers:

```php
// app/Rules/PhoneNumber.php
class PhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Use libphonenumber to validate
        try {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($value, null);
            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function message()
    {
        return 'The :attribute must be a valid phone number.';
    }
}
```

### Sanitization

Input sanitization to prevent XSS and injection attacks:

```php
// app/Http/Middleware/SanitizeInput.php
class SanitizeInput
{
    public function handle($request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$input) {
            $input = filter_var($input, FILTER_SANITIZE_STRING);
        });
        
        $request->merge($input);
        
        return $next($request);
    }
}
```

## 🔐 CSRF Protection

### Web Routes

Laravel Auth Starter Kits use Laravel's built-in CSRF protection for web routes:

```php
// app/Http/Middleware/VerifyCsrfToken.php
class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // Webhook endpoints that don't require CSRF
        'webhook/sms/*',
    ];
}
```

### SPA Protection

For SPAs using the API:

```php
// resources/js/bootstrap.js
import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Fetch CSRF token on SPA initialization
document.addEventListener('DOMContentLoaded', () => {
    axios.get('/sanctum/csrf-cookie');
});
```

## 🛡️ XSS Protection

### Output Escaping

All output is automatically escaped by Laravel's Blade templating engine:

```blade
{{-- This is automatically escaped --}}
{{ $userInput }}

{{-- Only use this for trusted content --}}
{!! $trustedHtml !!}
```

### Content Security Policy

Configure Content Security Policy headers:

```php
// app/Http/Middleware/AddSecurityHeaders.php
class AddSecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data:; " .
            "font-src 'self' data:;"
        );
        
        return $response;
    }
}
```

### Vue.js XSS Protection

For Vue.js components, use the `v-text` directive or mustache interpolation:

```vue
<template>
  <!-- Safe interpolation -->
  <div>{{ userInput }}</div>
  
  <!-- Avoid for user input -->
  <div v-html="trustedContent"></div>
</template>
```

## 🔒 SQL Injection Prevention

### Eloquent ORM

Laravel Auth Starter Kits use Eloquent ORM which automatically provides parameterized queries:

```php
// Safe - uses parameterized queries
$user = User::where('phone', $request->phone)->first();

// Safe - uses parameterized queries
$otpVerification = OtpVerification::create([
    'phone' => $request->phone,
    'otp' => $hashedOtp,
    'expires_at' => now()->addMinutes(config('sms.otp.expiry')),
]);
```

### Query Builder

When using the query builder, always use parameterized queries:

```php
// Safe - uses parameterized queries
DB::table('users')
    ->where('phone', $request->phone)
    ->update(['phone_verified_at' => now()]);

// AVOID - vulnerable to SQL injection
DB::raw("SELECT * FROM users WHERE phone = '{$request->phone}'");
```

## 🔒 Session Security

### Session Configuration

Secure session configuration in `config/session.php`:

```php
return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120), // minutes
    'expire_on_close' => false,
    'encrypt' => true,
    'secure' => env('SESSION_SECURE_COOKIE', true), // HTTPS only
    'http_only' => true, // Not accessible via JavaScript
    'same_site' => 'lax', // Protect against CSRF
];
```

### API Token Security

Secure token handling with Laravel Sanctum:

```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),
    
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 60 * 24), // 24 hours
    
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

## 🔐 Password Storage

Even though Phone Auth doesn't use passwords directly, secure password hashing is implemented for future authentication methods:

```php
// For password-based authentication kits
use Illuminate\Support\Facades\Hash;

// Storing password
$user->password = Hash::make($password);
$user->save();

// Verifying password
if (Hash::check($password, $user->password)) {
    // Password is correct
}
```

## 📱 Phone Number Security

### Phone Number Verification

Phone numbers are verified through OTP:

```php
// app/Services/PhoneVerificationService.php
public function markAsVerified($phone)
{
    $user = User::where('phone', $phone)->first();
    
    if ($user) {
        $user->phone_verified_at = now();
        $user->save();
        return true;
    }
    
    return false;
}
```

### Phone Number Formatting

All phone numbers are stored in E.164 format:

```php
// app/Services/PhoneService.php
public function formatPhoneNumber($phone)
{
    try {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($phone, null);
        
        if ($phoneUtil->isValidNumber($phoneNumber)) {
            return $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
        }
    } catch (\Exception $e) {
        // Log error
    }
    
    return null; // Invalid phone number
}
```

## 📝 Audit Logging

### Security Event Logging

Log all security-related events:

```php
// app/Services/SecurityAuditService.php
public function logSecurityEvent($eventType, $user = null, $data = [])
{
    $userId = $user ? $user->id : null;
    
    SecurityAudit::create([
        'event_type' => $eventType,
        'user_id' => $userId,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'data' => json_encode($data),
    ]);
}
```

Usage:

```php
// In authentication controller
$this->securityAudit->logSecurityEvent('otp_verification_success', $user, [
    'phone' => $request->phone,
    'verification_time' => now(),
]);
```

### Failed Authentication Logging

Log failed authentication attempts:

```php
// In authentication controller
public function verifyOtp(VerifyOtpRequest $request)
{
    // Verification logic...
    
    if (!$success) {
        $this->securityAudit->logSecurityEvent('otp_verification_failed', null, [
            'phone' => $request->phone,
            'attempt' => $attempt,
        ]);
        
        return response()->json([
            'success' => false,
            'error' => [
                'message' => 'Invalid verification code',
            ],
        ], 422);
    }
}
```

## 🔒 API Security

### API Authentication

Secure API authentication with Laravel Sanctum:

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    // Other authenticated routes...
});
```

### API Rate Limiting

Rate limit API endpoints:

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', [UserController::class, 'show']);
});
```

### API Token Expiration

Configure token expiration in `config/sanctum.php`:

```php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 60 * 24 * 7), // 7 days
```

## 🔒 HTTP Security Headers

### Security Headers Middleware

Add security headers to all responses:

```php
// app/Http/Middleware/AddSecurityHeaders.php
class AddSecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()');
        
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        return $response;
    }
}
```

Register the middleware in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\AddSecurityHeaders::class,
];
```

## 🔐 Environment Security

### Environment File

Secure your `.env` file:

```bash
# Don't commit to version control
.env
.env.backup
.env.*.local

# Set strict permissions
chmod 600 .env
```

### Production Environment

Set proper environment variables for production:

```
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_COOKIE_HTTPONLY=true
SESSION_COOKIE_SAMESITE=lax
```

### Database Security

Use principle of least privilege for database access:

```
# Development
DB_USERNAME=dev_user
DB_PASSWORD=dev_password

# Production
DB_USERNAME=prod_user_with_limited_privileges
DB_PASSWORD=strong_random_password
```

## 🔎 Vulnerability Scanning

### Automated Security Scanning

Set up GitHub Actions for security scanning:

```yaml
# .github/workflows/security.yml
name: Security Scan

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]
  schedule:
    - cron: '0 0 * * 0'  # Weekly on Sundays

jobs:
  security-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: PHP Security Checker
        uses: symfonycorp/security-checker-action@v3
      
      - name: NPM Audit
        run: npm audit --audit-level=high
```

### Manual Security Review

Regular manual security reviews:

1. Code review for security issues
2. Review of third-party dependencies
3. Security testing of authentication flows
4. Penetration testing

## 🚨 Incident Response

### Security Breach Response Plan

1. **Containment**: Isolate affected systems
2. **Investigation**: Determine scope and impact
3. **Remediation**: Fix vulnerabilities
4. **Communication**: Notify affected users
5. **Post-Incident Analysis**: Learn from the incident

### User Notification

In case of a security breach:

```php
// app/Services/SecurityNotificationService.php
public function notifyOfSecurityEvent($users, $eventType, $details)
{
    foreach ($users as $user) {
        // Send notification via preferred channel
        $user->notify(new SecurityIncidentNotification($eventType, $details));
        
        // Log notification
        $this->securityAudit->logSecurityEvent('security_notification_sent', $user, [
            'event_type' => $eventType,
            'details' => $details,
        ]);
    }
}
```

## 🧪 Security Testing

### Unit Tests for Security Features

```php
// tests/Feature/Auth/OtpVerificationTest.php
public function test_otp_expires_after_configured_time()
{
    // Create OTP
    $phone = '+12125550123';
    $otpService = app(OtpService::class);
    $otp = $otpService->generate($phone);
    
    // Travel forward in time past expiry
    $this->travel(config('sms.otp.expiry') + 1)->minutes();
    
    // Attempt verification with correct OTP
    $response = $this->postJson('/api/auth/phone/verify', [
        'phone' => $phone,
        'code' => $otp,
    ]);
    
    // Should fail due to expiration
    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'expired_code');
}
```

### Rate Limiting Tests

```php
// tests/Feature/Auth/RateLimitingTest.php
public function test_otp_sending_is_rate_limited()
{
    $phone = '+12125550123';
    
    // Send requests up to the limit
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson('/api/auth/phone/send-otp', [
            'phone' => $phone,
        ]);
        $response->assertSuccessful();
    }
    
    // One more request should be rate limited
    $response = $this->postJson('/api/auth/phone/send-otp', [
        'phone' => $phone,
    ]);
    
    $response->assertStatus(429); // Too Many Requests
}
```

## 🔄 Regular Updates

### Dependency Updates

Set up Dependabot for automated dependency updates:

```yaml
# .github/dependabot.yml
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/phone-auth"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 10
    
  - package-ecosystem: "npm"
    directory: "/phone-auth"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 10
```

### Security Patching

Regularly apply security patches:

```bash
# Update Laravel framework
composer update laravel/framework --with-all-dependencies

# Update all dependencies
composer update

# Update npm packages
npm update
```

## 📊 Security Monitoring

### Real-time Security Monitoring

Implement real-time security monitoring:

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    'Illuminate\Auth\Events\Failed' => [
        'App\Listeners\LogFailedAuthenticationAttempt',
    ],
    'Illuminate\Auth\Events\Login' => [
        'App\Listeners\LogSuccessfulLogin',
    ],
    'Illuminate\Auth\Events\Logout' => [
        'App\Listeners\LogSuccessfulLogout',
    ],
];
```

### Anomaly Detection

Implement anomaly detection for authentication attempts:

```php
// app/Services/AnomalyDetectionService.php
public function detectAnomalies($user, $request)
{
    $anomalies = [];
    
    // Check for unusual location
    if ($this->isUnusualLocation($user, $request->ip())) {
        $anomalies[] = 'unusual_location';
    }
    
    // Check for unusual time
    if ($this->isUnusualTime($user)) {
        $anomalies[] = 'unusual_time';
    }
    
    // Check for unusual device
    if ($this->isUnusualDevice($user, $request->userAgent())) {
        $anomalies[] = 'unusual_device';
    }
    
    return $anomalies;
}
```

## 🔧 Security Configuration Checklist

### Pre-launch Security Checklist

Before deploying to production, ensure:

- [ ] Environment is set to production
- [ ] Debug mode is disabled
- [ ] HTTPS is enforced
- [ ] Secure cookies are enabled
- [ ] Rate limiting is configured
- [ ] API endpoints are protected
- [ ] Security headers are configured
- [ ] Database credentials are secure
- [ ] API keys are secure
- [ ] Session configuration is secure
- [ ] Log files are protected
- [ ] Sensitive data is encrypted
- [ ] Error reporting is configured correctly
- [ ] Permissions are set correctly

### Configuration Validator

Create an artisan command to validate security configuration:

```php
// app/Console/Commands/SecurityCheck.php
class SecurityCheck extends Command
{
    protected $signature = 'security:check';
    protected $description = 'Check security configuration';
    
    public function handle()
    {
        $issues = [];
        
        // Check environment
        if (config('app.env') !== 'production') {
            $issues[] = 'App environment is not set to production';
        }
        
        // Check debug mode
        if (config('app.debug')) {
            $issues[] = 'Debug mode is enabled';
        }
        
        // Check HTTPS
        if (!config('session.secure')) {
            $issues[] = 'Secure cookies are not enabled';
        }
        
        // Output issues
        if (count($issues) > 0) {
            $this->error('Security issues found:');
            foreach ($issues as $issue) {
                $this->line('- ' . $issue);
            }
            return 1;
        }
        
        $this->info('No security issues found');
        return 0;
    }
}
```

## 📚 Additional Resources

### Security Documentation

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Security_Cheat_Sheet.html)

### Security Tools

- [PHP Security Checker](https://github.com/sensiolabs/security-checker)
- [OWASP ZAP](https://www.zaproxy.org/)
- [Snyk](https://snyk.io/)

## 🚀 Next Steps

- Learn about [Deployment Options](deployment.md) with security best practices
- Check out [API Documentation](api.md) for secure API usage
- Explore [SMS Gateway Integration](sms-gateways.md) for phone authentication
- Review [Frontend Guide](frontend.md) for client-side security best practices

---

**Need security help?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
