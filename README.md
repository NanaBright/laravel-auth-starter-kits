# Laravel Authentication Starter Kits

A collection of production-ready authentication starter kits for Laravel 11 applications. Each kit is a standalone Laravel project with Vue.js 3 frontend, ready to clone and customize for your needs.

## Starter Kits

| Kit | Description |
|-----|-------------|
| [phone-auth](./phone-auth/) | SMS/OTP verification with support for Vonage, AWS SNS, and custom gateways |
| [email-auth](./email-auth/) | Magic link authentication and email verification |
| [otp-auth](./otp-auth/) | Multi-channel OTP (SMS + Email) with backup codes |
| [social-auth](./social-auth/) | OAuth login via Google, GitHub, Facebook, Twitter |
| [two-factor-auth](./two-factor-auth/) | TOTP and SMS-based 2FA with recovery codes |
| [admin-dashboard](./admin-dashboard/) | User management dashboard with analytics |
| [i18n](./i18n/) | Multi-language support module (10 languages, RTL) |
| [landing-page](./landing-page/) | Project showcase page |

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL, PostgreSQL, or SQLite

## Quick Start

```bash
git clone https://github.com/NanaBright/laravel-auth-starter-kits.git
cd laravel-auth-starter-kits/phone-auth

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

php artisan serve &
npm run dev
```

Open `http://localhost:8000` in your browser.

## Stack

- **Backend**: Laravel 11, Sanctum
- **Frontend**: Vue.js 3, Tailwind CSS, Vite
- **Database**: Eloquent ORM with migrations

## Project Structure

```
laravel-auth-starter-kits/
├── phone-auth/          # SMS/OTP authentication
├── email-auth/          # Magic link authentication
├── otp-auth/            # Multi-channel OTP
├── social-auth/         # OAuth providers
├── two-factor-auth/     # 2FA implementation
├── admin-dashboard/     # User management
├── i18n/                # Internationalization
├── landing-page/        # Showcase site
├── docs/                # Documentation
├── tests/               # Shared test utilities
└── docker/              # Docker configuration
```

## Documentation

- [Installation](docs/installation.md)
- [Configuration](docs/configuration.md)
- [SMS Gateways](docs/sms-gateways.md)
- [API Reference](docs/api.md)
- [Deployment](docs/deployment.md)
- [Security](docs/security.md)

Each kit also has its own README with specific setup instructions.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add your feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## Security

If you discover a security vulnerability, please see [SECURITY.md](SECURITY.md) for reporting instructions.

## License

MIT License. See [LICENSE](LICENSE) for details.
