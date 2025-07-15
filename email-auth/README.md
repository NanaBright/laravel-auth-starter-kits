# Email Authentication Kit

🔐 **Secure email-based authentication for Laravel applications**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](../LICENSE)

A production-ready email authentication starter kit for Laravel applications. This kit provides secure email-based authentication with magic links, email verification, and customizable email templates.

## ✨ Features

- 🔐 **Magic Link Authentication** - Passwordless login via email
- 📧 **Email Verification** - Secure email address verification
- 📨 **Custom Email Templates** - Beautiful, responsive email templates
- 🛡️ **Security First** - Rate limiting, token expiration, and protection
- 🎨 **Modern UI** - Vue.js 3 + Tailwind CSS interface
- 📱 **Responsive Design** - Works perfectly on all devices
- 🔄 **Fallback Support** - Multiple email providers support
- 📊 **Analytics** - Track email delivery and engagement
- 🌐 **Multi-language** - i18n support for global applications

## 📋 Requirements

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- Laravel 11.x
- MySQL, PostgreSQL, or SQLite
- SMTP email service (Gmail, SendGrid, Mailgun, etc.)

## 🚀 Quick Start

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database and email settings in .env
```

### 3. Set Up Database

```bash
# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

### 4. Configure Email Service

Update your `.env` file with your email service credentials:

```env
# Gmail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Your App Name"

# Or use SendGrid
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your-sendgrid-api-key

# Or use Mailgun
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-secret
```

### 5. Start Development Servers

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite development server
npm run dev
```

### 6. Visit Your Application

Open your browser and visit:
```
http://localhost:8000
```

## 🔧 Configuration

### Magic Link Settings

Configure magic link behavior in `config/auth.php`:

```php
'magic_links' => [
    'expiry' => 15, // minutes
    'max_attempts' => 5,
    'rate_limit' => 3, // requests per minute
    'require_verification' => true,
],
```

### Email Templates

Customize email templates in `resources/views/emails/`:

- `magic-link.blade.php` - Magic link email template
- `verification.blade.php` - Email verification template
- `welcome.blade.php` - Welcome email template

### Rate Limiting

Configure rate limiting in `config/mail.php`:

```php
'rate_limiting' => [
    'enabled' => true,
    'max_attempts' => 5,
    'decay_minutes' => 10,
],
```

## 🎨 Customization

### Frontend Components

The kit includes Vue.js components for:

- Email input forms
- Magic link verification
- Email verification flows
- User dashboard
- Settings and preferences

### Styling

Customize the appearance using Tailwind CSS:

```bash
# Rebuild CSS after changes
npm run build
```

### Email Templates

Edit Blade templates in `resources/views/emails/` to match your brand:

- Customize colors, fonts, and layout
- Add your logo and branding
- Modify email content and messaging

## 📡 API Endpoints

### Authentication

- `POST /api/auth/email/send-magic-link` - Send magic link
- `POST /api/auth/email/verify-magic-link` - Verify magic link
- `POST /api/auth/email/send-verification` - Send email verification
- `POST /api/auth/email/verify-email` - Verify email address

### User Management

- `GET /api/user` - Get authenticated user
- `PUT /api/user` - Update user profile
- `DELETE /api/user` - Delete user account

## 🔒 Security Features

- **Rate Limiting** - Prevents spam and abuse
- **Token Expiration** - Magic links expire after configured time
- **Email Verification** - Ensures valid email addresses
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Validation** - Comprehensive input validation
- **Audit Logging** - Track authentication events

## 📊 Analytics & Monitoring

- **Email Delivery Tracking** - Monitor email delivery success
- **Engagement Metrics** - Track link clicks and usage
- **Failed Attempts** - Log and monitor failed authentication
- **Performance Metrics** - Response times and success rates

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 📚 Documentation

- [Installation Guide](docs/installation.md)
- [Configuration Reference](docs/configuration.md)
- [API Documentation](docs/api.md)
- [Security Guide](docs/security.md)
- [Email Templates](docs/email-templates.md)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](../CONTRIBUTING.md) for details.

## 📄 License

This project is open-sourced software licensed under the [MIT license](../LICENSE).

## 📞 Support

- 📖 [Documentation](../docs/)
- 🐛 [Issue Tracker](https://github.com/NanaBright/laravel-auth-starter-kits/issues)
- 💬 [Discussions](https://github.com/NanaBright/laravel-auth-starter-kits/discussions)

---

**Ready to get started?** Follow the [Installation Guide](docs/installation.md) to set up email authentication in your Laravel application.

[⬅️ Back to Main README](../README.md) | [📱 Phone Auth](../phone-auth/README.md)
