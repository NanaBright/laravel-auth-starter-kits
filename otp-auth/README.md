# OTP Authentication Kit

Multi-channel OTP authentication for Laravel with SMS, Email, and backup code support.

## Features

- OTP delivery via SMS, Email, or both
- Backup codes for account recovery
- TOTP (Time-based One-Time Password) support
- Rate limiting and expiration
- Vue.js frontend with Tailwind CSS

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL, PostgreSQL, or SQLite
- SMS provider and/or SMTP service

## Installation

```bash
cd otp-auth
composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

php artisan serve &
npm run dev
```

## Configuration

Set your preferred OTP channel in `.env`:

```env
OTP_CHANNEL=sms       # sms, email, or both
OTP_LENGTH=6
OTP_EXPIRY=10         # minutes
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth/request-otp | Request OTP |
| POST | /api/auth/verify-otp | Verify OTP |
| POST | /api/auth/backup-codes | Generate backup codes |
| POST | /api/auth/verify-backup | Verify backup code |

## License

MIT
php artisan key:generate
```

### 3. Configure Channels

Update your `.env` file with channel settings:

```env
# OTP Configuration
OTP_PRIMARY_CHANNEL=email  # email, sms, or both
OTP_FALLBACK_CHANNEL=sms   # optional fallback
OTP_LENGTH=6
OTP_EXPIRY=10              # minutes

# SMS Configuration (if using SMS)
SMS_PROVIDER=vonage
VONAGE_KEY=your_key
VONAGE_SECRET=your_secret

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_app_password
```

### 4. Set Up Database

```bash
# Run migrations
php artisan migrate
```

### 5. Start Development Servers

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite
npm run dev
```

Visit `http://localhost:8000` to see the application.

## Configuration

### OTP Settings

Configure OTP behavior in `config/otp.php`:

```php
return [
    'channels' => [
        'primary' => env('OTP_PRIMARY_CHANNEL', 'email'),
        'fallback' => env('OTP_FALLBACK_CHANNEL', null),
    ],
    
    'code' => [
        'length' => env('OTP_LENGTH', 6),
        'expiry' => env('OTP_EXPIRY', 10), // minutes
        'type' => 'numeric', // numeric, alpha, alphanumeric
    ],
    
    'backup_codes' => [
        'count' => 10,
        'length' => 8,
    ],
    
    'rate_limiting' => [
        'max_attempts' => 5,
        'decay_minutes' => 10,
    ],
];
```

### Channel Priority

Set up channel priority for OTP delivery:

```php
'delivery_order' => ['email', 'sms'], // Try email first, then SMS
```

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register with email/phone |
| POST | `/api/auth/login` | Request OTP login |
| POST | `/api/auth/verify` | Verify OTP code |
| POST | `/api/auth/resend` | Resend OTP code |
| POST | `/api/auth/logout` | Logout user |
| GET | `/api/user` | Get authenticated user |

### Backup Codes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/auth/backup-codes` | Get backup codes |
| POST | `/api/auth/backup-codes/regenerate` | Generate new codes |
| POST | `/api/auth/backup-codes/verify` | Verify backup code |

### Channel Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/auth/channels` | Get user channels |
| POST | `/api/auth/channels/add` | Add new channel |
| DELETE | `/api/auth/channels/{id}` | Remove channel |
| PUT | `/api/auth/channels/{id}/primary` | Set as primary |

## Security Features

- **Rate Limiting** - Prevents brute force attacks
- **OTP Expiration** - Time-limited verification codes
- **Backup Code Hashing** - Secure storage of backup codes
- **Channel Verification** - Verify ownership before adding channels
- **Secure Tokens** - Laravel Sanctum for API authentication
- **Input Validation** - Comprehensive input validation

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## Documentation

- [Installation Guide](docs/installation.md)
- [Configuration Reference](docs/configuration.md)
- [API Documentation](docs/api.md)
- [Backup Codes Guide](docs/backup-codes.md)

## Contributing

We welcome contributions! Please see our [Contributing Guide](../CONTRIBUTING.md) for details.

## License

This project is open-sourced software licensed under the [MIT license](../LICENSE).

---

[Back to Main README](../README.md) | [Phone Auth](../phone-auth/README.md) | [Email Auth](../email-auth/README.md)
