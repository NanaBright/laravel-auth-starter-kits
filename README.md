# Laravel Authentication Starter Kits

**Complete authentication solutions for modern Laravel applications**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Tests](https://github.com/NanaBright/laravel-auth-starter-kits/workflows/Tests/badge.svg)](https://github.com/NanaBright/laravel-auth-starter-kits/actions)

A collection of production-ready authentication starter kits for Laravel applications. Each kit provides a complete authentication solution with modern UI, robust security, and extensive customization options.

## Available Starter Kits

| Kit | Status | Description | Features |
|-----|--------|-------------|----------|
| **[Phone Auth](./phone-auth/)** | **Available** | Phone/SMS verification | OTP, Multiple SMS gateways, Rate limiting |
| **[Email Auth](./email-auth/)** | **Available** | Email-based authentication | Magic links, Email verification, Templates |
| **OTP Auth** | **Coming Soon** | Multi-channel OTP | SMS + Email, Backup codes, Time-based |
| **Social Auth** | **Planned** | Social media login | Google, GitHub, Facebook, Twitter |
| **Two-Factor Auth** | **Planned** | 2FA authentication | TOTP, SMS, Recovery codes |
| **Passwordless Auth** | **Planned** | Passwordless login | Magic links, Biometric, WebAuthn |

## Why Choose These Starter Kits?

- **Production Ready** - Battle-tested code with security best practices
- **Modern UI** - Beautiful Vue.js interfaces with Tailwind CSS
- **Security First** - Built-in rate limiting, validation, and protection
- **Highly Configurable** - Easy customization for your needs
- **Well Documented** - Comprehensive guides and examples
- **Community Driven** - Open source with active community support
- **Easy Deployment** - Docker support and deployment guides

## Quick Start

Choose your preferred authentication method and get started in minutes:

### Phone Authentication

```bash
# Clone the repository
git clone https://github.com/NanaBright/laravel-auth-starter-kits.git
cd laravel-auth-starter-kits/phone-auth

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Start development servers
php artisan serve &
npm run dev
```

Visit `http://localhost:8000` and start building!

### Email Authentication

```bash
# Clone the repository
git clone https://github.com/NanaBright/laravel-auth-starter-kits.git
cd laravel-auth-starter-kits/email-auth

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Start development servers
php artisan serve &
npm run dev
```

Visit `http://localhost:8000` and start building!

## What's Included

### Core Features (All Kits)
- **Laravel 11.x** - Latest Laravel framework
- **Vue.js 3.x** - Modern reactive frontend
- **Tailwind CSS** - Utility-first styling
- **Laravel Sanctum** - API authentication
- **Rate Limiting** - Abuse prevention
- **Input Validation** - Security and UX
- **Error Handling** - Graceful error management
- **Responsive Design** - Mobile-first approach

### Phone Auth Specific
- **Multiple SMS Gateways** - Vonage, AWS SNS, custom APIs
- **OTP Verification** - Time-limited verification codes
- **SMS Analytics** - Delivery tracking and metrics
- **Fallback Support** - Multiple delivery methods
- **Cost Optimization** - Smart provider routing

### Email Auth Specific
- **Magic Link Authentication** - Passwordless email login
- **Email Verification** - Secure email validation
- **Email Templates** - Customizable notification templates
- **Secure Token Generation** - Time-limited verification tokens
- **Multiple Email Providers** - SMTP, SendGrid, Mailgun, SES

## Architecture

### Backend (Laravel)
- **Controllers** - Clean, organized authentication logic
- **Services** - Reusable business logic (SMS, Email, etc.)
- **Models** - Eloquent models with relationships
- **Middleware** - Authentication and rate limiting
- **Migrations** - Database schema management

### Frontend (Vue.js)
- **Components** - Reusable Vue components
- **Composables** - Shared reactive logic
- **Routing** - Vue Router for SPA navigation
- **State Management** - Reactive state with localStorage
- **Form Handling** - Validation and error display

### Database Schema
```sql
-- Core tables (all kits)
users (id, created_at, updated_at, ...)

-- Phone auth specific
users (phone, phone_verified_at)
otp_verifications (phone, otp, expires_at)

-- Email auth specific
users (email, email_verified_at)
magic_links (email, token, expires_at)
```

## Security Features

- **Rate Limiting** - Prevents spam and abuse
- **Input Validation** - Server-side validation for all inputs
- **CSRF Protection** - Cross-site request forgery prevention
- **XSS Protection** - Cross-site scripting prevention
- **SQL Injection Prevention** - Parameterized queries
- **Secure Headers** - Security headers configuration
- **Token Management** - Secure API token handling

## Documentation

### General Documentation
- [Installation Guide](docs/installation.md) - Complete setup instructions
- [Configuration Guide](docs/configuration.md) - Detailed configuration options
- [Deployment Guide](docs/deployment.md) - Production deployment
- [Security Guide](docs/security.md) - Security best practices

### Kit-Specific Documentation
- [Phone Auth Guide](phone-auth/README.md) - Phone/SMS authentication
- [Email Auth Guide](email-auth/README.md) - Email/Magic link authentication
- [API Documentation](docs/api.md) - REST API reference
- [Frontend Guide](docs/frontend.md) - Vue.js customization

## Contributing

We welcome contributions! Whether you want to:

- Fix bugs
- Add new features
- Create new authentication kits
- Improve documentation
- Add tests
- Add internationalization

See our [Contributing Guide](CONTRIBUTING.md) for details.

### Quick Contribution Steps
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Areas for Contribution
- **New Authentication Methods** - Biometric, WebAuthn, etc.
- **SMS Gateway Integrations** - New providers and protocols
- **UI/UX Improvements** - Better designs and user experience
- **Documentation** - Guides, tutorials, and examples
- **Testing** - Unit tests and integration tests
- **Internationalization** - Multi-language support

## Roadmap

### Phase 1 (Current)
- [x] Phone/SMS Authentication Kit
- [x] Email Authentication Kit
- [x] Magic link implementation
- [x] Email template system
- [x] Comprehensive documentation
- [x] Multiple SMS gateway support
- [x] Production deployment guides

### Phase 2 (Q4 2025)
- [ ] Multi-channel OTP Kit
- [ ] Social Authentication Kit
- [ ] Two-Factor Authentication Kit
- [ ] Admin dashboard for user management

### Phase 3 (2026)
- [ ] Passwordless Authentication Kit
- [ ] Biometric Authentication Kit
- [ ] Enterprise features
- [ ] Advanced analytics

## Community

### Contributors
Thanks to all contributors who make this project possible!

<!-- Contributors will be automatically updated -->

### Recognition
- **Top Contributors** - Monthly recognition in README
- **Feature Sponsors** - Sponsor a specific authentication method
- **Community Champions** - Help others in discussions

### Get Help
- **Documentation** - Check our comprehensive guides
- **Discussions** - [GitHub Discussions](https://github.com/NanaBright/laravel-auth-starter-kits/discussions)
- **Issues** - [Report bugs or request features](https://github.com/NanaBright/laravel-auth-starter-kits/issues)
- **Security** - See our [Security Policy](SECURITY.md)

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Acknowledgments

- **Laravel Team** - For the amazing framework
- **Vue.js Team** - For the reactive frontend framework
- **Tailwind CSS** - For the beautiful utility-first CSS
- **Community Contributors** - For making this project better

## Stats

![GitHub stars](https://img.shields.io/github/stars/NanaBright/laravel-auth-starter-kits)
![GitHub forks](https://img.shields.io/github/forks/NanaBright/laravel-auth-starter-kits)
![GitHub issues](https://img.shields.io/github/issues/NanaBright/laravel-auth-starter-kits)
![GitHub pull requests](https://img.shields.io/github/issues-pr/NanaBright/laravel-auth-starter-kits)

---

**Made by the community**

If you find this project helpful, please consider giving it a star on GitHub!

[Star this project](https://github.com/NanaBright/laravel-auth-starter-kits) | [Report Bug](https://github.com/NanaBright/laravel-auth-starter-kits/issues) | [Request Feature](https://github.com/NanaBright/laravel-auth-starter-kits/issues)
