# Two-Factor Authentication Kit

TOTP and SMS-based 2FA for Laravel applications.

## Features

- TOTP support (Google Authenticator, Authy)
- SMS-based 2FA
- Recovery codes
- QR code generation for authenticator apps
- Remember trusted devices
- Vue.js frontend with Tailwind CSS

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL, PostgreSQL, or SQLite

## Installation

```bash
cd two-factor-auth
composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

php artisan serve &
npm run dev
```

## Usage

After logging in with email/password:

1. Enable 2FA in account settings
2. Scan QR code with authenticator app (or choose SMS)
3. Enter verification code to confirm setup
4. Save recovery codes securely

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/2fa/enable | Enable 2FA |
| POST | /api/2fa/verify | Verify 2FA code |
| POST | /api/2fa/disable | Disable 2FA |
| GET | /api/2fa/recovery-codes | Get recovery codes |
| POST | /api/2fa/regenerate-codes | Regenerate recovery codes |

## License

MIT
```

## Configuration

### Environment Variables

```env
# Two-Factor Settings
TWO_FACTOR_RECOVERY_CODES_COUNT=8
TWO_FACTOR_WINDOW=1

# SMS Provider (for SMS-based 2FA)
SMS_PROVIDER=vonage
VONAGE_KEY=
VONAGE_SECRET=
VONAGE_SMS_FROM=
```

### Config File

The `config/two-factor.php` file contains:

- `window`: TOTP validation window (default: 1)
- `recovery_codes_count`: Number of recovery codes (default: 8)
- `remember_device_days`: How long to remember trusted devices

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | Login (step 1) |
| POST | `/api/auth/two-factor` | Verify 2FA code (step 2) |
| POST | `/api/auth/logout` | Logout |

### Two-Factor Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/two-factor/enable` | Enable 2FA (get QR code) |
| POST | `/api/two-factor/confirm` | Confirm 2FA setup |
| POST | `/api/two-factor/disable` | Disable 2FA |
| GET | `/api/two-factor/recovery-codes` | Get recovery codes |
| POST | `/api/two-factor/recovery-codes` | Regenerate recovery codes |

## Authentication Flow

### Standard Login with 2FA:

1. User submits email/password
2. Server validates credentials
3. If 2FA enabled, returns `two_factor_required: true`
4. User submits TOTP code from authenticator app
5. Server validates code and returns auth token

### 2FA Setup Flow:

1. User navigates to security settings
2. Request 2FA enable - receive secret and QR code
3. User scans QR code with authenticator app
4. User confirms by entering a valid code
5. Server enables 2FA and returns recovery codes

## Security Features

- Secure secret key storage (encrypted)
- Rate limiting on verification attempts
- Recovery codes are hashed
- TOTP codes expire after 30 seconds
- Device remembering uses secure tokens
- Session invalidation on 2FA changes

## Architecture

```
two-factor-auth/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       └── TwoFactorController.php
│   ├── Models/
│   │   └── User.php
│   └── Services/
│       ├── TwoFactorService.php
│       └── RecoveryCodeService.php
├── config/
│   └── two-factor.php
├── database/
│   └── migrations/
└── resources/
    └── js/
        └── pages/
            ├── Login.vue
            ├── TwoFactorChallenge.vue
            ├── Dashboard.vue
            └── SecuritySettings.vue
```

## Troubleshooting

### Codes not working?

- Ensure device time is synchronized (NTP)
- Try the previous or next code (window setting)
- Use a recovery code if locked out

### QR code not scanning?

- Enter the secret key manually
- Verify the issuer name is correct

## License

MIT License
