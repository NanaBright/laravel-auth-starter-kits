# Email Authentication Kit

Email-based authentication with magic links and email verification for Laravel.

## Features

- Magic link (passwordless) login
- Email verification flow
- Customizable email templates
- Rate limiting and token expiration
- Vue.js frontend with Tailwind CSS

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL, PostgreSQL, or SQLite
- SMTP service (Gmail, SendGrid, Mailgun, etc.)

## Installation

```bash
cd email-auth
composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

php artisan serve &
npm run dev
```

## Email Configuration

Update `.env` with your mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Your App"
```

## Configuration

Magic link settings in `config/auth.php`:

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

## Customization

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

## API Endpoints

### Authentication

- `POST /api/auth/email/send-magic-link` - Send magic link
- `POST /api/auth/email/verify-magic-link` - Verify magic link
- `POST /api/auth/email/send-verification` - Send email verification
- `POST /api/auth/email/verify-email` - Verify email address

### User Management

- `GET /api/user` - Get authenticated user
- `PUT /api/user` - Update user profile
- `DELETE /api/user` - Delete user account

## Security Features

- **Rate Limiting** - Prevents spam and abuse
- **Token Expiration** - Magic links expire after configured time
- **Email Verification** - Ensures valid email addresses
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Validation** - Comprehensive input validation
- **Audit Logging** - Track authentication events

## Analytics & Monitoring

- **Email Delivery Tracking** - Monitor email delivery success
- **Engagement Metrics** - Track link clicks and usage
- **Failed Attempts** - Log and monitor failed authentication
- **Performance Metrics** - Response times and success rates

## Testing

Run the test suite:

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
- [Security Guide](docs/security.md)
- [Email Templates](docs/email-templates.md)

## Contributing

We welcome contributions! Please see our [Contributing Guide](../CONTRIBUTING.md) for details.

## License

This project is open-sourced software licensed under the [MIT license](../LICENSE).

## Support

- [Documentation](../docs/)
- [Issue Tracker](https://github.com/NanaBright/laravel-auth-starter-kits/issues)
- [Discussions](https://github.com/NanaBright/laravel-auth-starter-kits/discussions)

---

**Ready to get started?** Follow the [Installation Guide](docs/installation.md) to set up email authentication in your Laravel application.

[Back to Main README](../README.md) | [Phone Auth](../phone-auth/README.md)
