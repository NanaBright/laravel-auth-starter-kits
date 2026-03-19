# Phone Authentication Kit

Phone number authentication with OTP verification for Laravel. Supports multiple SMS gateways without requiring specific third-party SDKs.

## Features

- OTP-based registration and login
- Multiple SMS delivery methods (HTTP API, SMPP, Email-to-SMS, logging)
- Rate limiting and OTP expiration
- Vue.js frontend with Tailwind CSS
- Laravel Sanctum for API tokens

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL, PostgreSQL, or SQLite

## Installation

```bash
cd phone-auth
composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

php artisan serve &
npm run dev
```

## SMS Methods

### 1. Logger (Development)
Perfect for development and testing. OTP codes are logged to Laravel logs.

```env
SMS_METHOD=logger
```

Check logs: `tail -f storage/logs/laravel.log`

### 2. HTTP API Gateway
Connect to any SMS gateway via HTTP API.

```env
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://api.your-sms-provider.com/send
SMS_USERNAME=your_username
SMS_PASSWORD=your_password
SMS_SENDER_ID=YourApp
```

### 3. Email-to-SMS Gateway
Send SMS via carrier email gateways (great for testing).

```env
SMS_METHOD=email_gateway
SMS_EMAIL_GATEWAY_ENABLED=true
SMS_DEFAULT_CARRIER=verizon
```

### 4. SMPP Protocol
Direct connection to SMS carriers (requires `php-smpp` library).

```env
SMS_METHOD=smpp
SMPP_HOST=your.smpp.host
SMPP_PORT=2775
SMPP_USERNAME=your_username
SMPP_PASSWORD=your_password
```

## Configuration

### Basic SMS Configuration

```env
# SMS Method (logger|http_api|email_gateway|smpp)
SMS_METHOD=logger

# HTTP API Settings
SMS_GATEWAY_URL=https://api.your-sms-gateway.com/send
SMS_USERNAME=your_username
SMS_PASSWORD=your_password
SMS_SENDER_ID=YourApp

# OTP Settings (configured in config/sms.php)
# - OTP length: 6 digits
# - Expiration: 10 minutes
# - Resend delay: 60 seconds
# - Max attempts: 3

# Rate Limiting
# - Max SMS per hour: 10
# - Max SMS per day: 50
# - Max attempts per phone: 5
```

### Advanced Configuration

See [`docs/configuration.md`](docs/configuration.md) for detailed configuration options.

## API Endpoints

### Authentication Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/register` | Register new user |
| `POST` | `/api/auth/login` | Login existing user |
| `POST` | `/api/auth/verify-otp` | Verify OTP code |
| `POST` | `/api/auth/resend-otp` | Resend OTP code |
| `POST` | `/api/auth/logout` | Logout user |
| `GET` | `/api/auth/user` | Get authenticated user |

### Example Usage

```javascript
// Register
const response = await fetch('/api/auth/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    phone: '+1234567890',
    password: 'securepassword'
  })
});

// Verify OTP
const verifyResponse = await fetch('/api/auth/verify-otp', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '123456'
  })
});
```

## Architecture

### Backend (Laravel)
- **AuthController** - Handles authentication logic
- **CustomSmsService** - SMS sending abstraction layer
- **OtpVerification Model** - OTP storage and validation
- **User Model** - User management with Sanctum tokens

### Frontend (Vue.js)
- **SPA Architecture** - Single page application
- **Vue Router** - Client-side routing
- **Tailwind CSS** - Modern, responsive styling
- **Component-based** - Reusable Vue components

### Database Schema
```sql
-- Users table
users (id, phone, password, phone_verified_at, created_at, updated_at)

-- OTP verifications table  
otp_verifications (id, phone, otp, expires_at, created_at, updated_at)

-- Personal access tokens (Sanctum)
personal_access_tokens (...)
```

## Security Features

- **Rate Limiting** - Prevents spam and abuse
- **OTP Expiration** - Time-limited verification codes
- **Secure Tokens** - Laravel Sanctum for API authentication
- **Phone Validation** - Server-side phone number validation
- **CORS Protection** - Configured for security
- **Input Sanitization** - All inputs are validated and sanitized

## Testing

```bash
php artisan test
```

For development, set `SMS_METHOD=logger` and check `storage/logs/laravel.log` for OTP codes.

## Documentation

- [Configuration](docs/configuration.md)
- [SMS Gateways](docs/sms-gateways.md)
- [API Reference](docs/api.md)

## License

MIT

If you find this project helpful, please consider giving it a star on GitHub!
