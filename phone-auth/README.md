# 📱 Custom SMS Authentication System

A Laravel-based phone number authentication system with custom SMS sending capabilities, built without dependency on third-party services like Twilio. This system provides multiple SMS delivery methods and is designed to be flexible, secure, and production-ready.

![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

## ✨ Features

- 🔐 **Phone Number Authentication** - Secure OTP-based registration and login
- 📞 **Multiple SMS Methods** - HTTP API, SMPP, Email-to-SMS, and logging
- 🛡️ **Security First** - Rate limiting, OTP expiration, and secure token management
- 🔧 **Developer Friendly** - Comprehensive logging and development tools
- 🌐 **Production Ready** - Configurable for various SMS gateways
- 📱 **Modern Frontend** - Vue.js SPA with Tailwind CSS
- 🎯 **Zero Dependencies** - No Twilio, Nexmo, or other SMS service requirements

## 🚀 Quick Start

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & npm
- MySQL/PostgreSQL database

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/custom-sms-auth.git
   cd custom-sms-auth
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=phone_auth
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Configure SMS settings** (see [Configuration](#configuration))

7. **Start development servers**
   ```bash
   # Terminal 1 - Laravel backend
   php artisan serve
   
   # Terminal 2 - Frontend assets
   npm run dev
   ```

8. **Visit your application**
   ```
   http://localhost:8000
   ```

## 📋 SMS Methods

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

## ⚙️ Configuration

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

## 🔌 API Endpoints

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

## 🏗️ Architecture

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

## 🛡️ Security Features

- **Rate Limiting** - Prevents spam and abuse
- **OTP Expiration** - Time-limited verification codes
- **Secure Tokens** - Laravel Sanctum for API authentication
- **Phone Validation** - Server-side phone number validation
- **CORS Protection** - Configured for security
- **Input Sanitization** - All inputs are validated and sanitized

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# With coverage
php artisan test --coverage
```

### Manual Testing
1. Set `SMS_METHOD=logger` in `.env`
2. Register with a phone number
3. Check `storage/logs/laravel.log` for OTP
4. Complete verification process

## 📚 Documentation

- [Installation Guide](docs/installation.md)
- [Configuration Reference](docs/configuration.md)
- [SMS Gateway Setup](docs/sms-gateways.md)
- [API Documentation](docs/api.md)
- [Contributing Guide](CONTRIBUTING.md)
- [Security Policy](SECURITY.md)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Quick Contribution Steps
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Areas for Contribution
- Additional SMS gateway integrations
- Enhanced security features
- UI/UX improvements
- Documentation improvements
- Test coverage expansion
- Performance optimizations

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel framework for the robust backend foundation
- Vue.js for the reactive frontend experience
- Tailwind CSS for the beautiful styling system
- Laravel Sanctum for secure API authentication

## 📞 Support

- **Documentation**: Check our [docs](docs/) folder
- **Issues**: [GitHub Issues](https://github.com/yourusername/custom-sms-auth/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/custom-sms-auth/discussions)

## 🗺️ Roadmap

- [ ] SMS gateway marketplace integration
- [ ] Multi-language support
- [ ] Enhanced analytics dashboard
- [ ] Webhook support for SMS delivery status
- [ ] Docker containerization
- [ ] Kubernetes deployment guides

---

**Made with ❤️ by the community**

If you find this project helpful, please consider giving it a ⭐ on GitHub!
