# API Documentation

This documentation covers the RESTful API endpoints available in the Laravel Auth Starter Kits. Each authentication kit provides its own set of API endpoints for authentication flows, user management, and related functionality.

## 📱 Phone Authentication API

### Base URL

All API URLs referenced are relative to your application's domain:

```
https://your-domain.com/api
```

For local development:

```
http://localhost:8000/api
```

### Authentication

API endpoints are protected using Laravel Sanctum. To authenticate, include the token in the `Authorization` header:

```
Authorization: Bearer YOUR_API_TOKEN
```

### Response Format

All responses are returned in JSON format with a consistent structure:

```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful"
}
```

Error responses:

```json
{
  "success": false,
  "error": {
    "code": "invalid_input",
    "message": "The provided data is invalid",
    "details": {
      // Validation errors or additional information
    }
  }
}
```

### Error Codes

| Code | Description |
|------|-------------|
| `invalid_input` | Validation error in request data |
| `unauthorized` | Authentication required or failed |
| `forbidden` | Not permitted to perform action |
| `not_found` | Resource not found |
| `rate_limited` | Too many requests |
| `server_error` | Internal server error |
| `unavailable` | Service unavailable |

### Phone Authentication Endpoints

#### 1. Send OTP

```
POST /auth/phone/send-otp
```

Send a one-time password to the specified phone number.

**Request:**

```json
{
  "phone": "+12125550123"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "expires_in": 600,
    "resend_after": 60
  },
  "message": "Verification code sent successfully"
}
```

**Rate Limiting:**
- 5 requests per phone number every 10 minutes

#### 2. Verify OTP

```
POST /auth/phone/verify
```

Verify an OTP sent to a phone number.

**Request:**

```json
{
  "phone": "+12125550123",
  "code": "123456"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "verified": true,
    "action": "registered" // or "authenticated"
  },
  "message": "Phone number verified successfully"
}
```

**Rate Limiting:**
- 10 verification attempts per phone number every 10 minutes

#### 3. Authenticate with OTP

```
POST /auth/phone/login
```

Authenticate a user with a verified phone number and OTP.

**Request:**

```json
{
  "phone": "+12125550123",
  "code": "123456"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "token": "YOUR_API_TOKEN",
    "user": {
      "id": 1,
      "phone": "+12125550123",
      "phone_verified_at": "2025-07-01T12:00:00.000000Z",
      "created_at": "2025-06-15T12:00:00.000000Z",
      "updated_at": "2025-07-01T12:00:00.000000Z"
    }
  },
  "message": "Authentication successful"
}
```

#### 4. Get Authenticated User

```
GET /user
```

Get the currently authenticated user's details.

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "phone": "+12125550123",
    "phone_verified_at": "2025-07-01T12:00:00.000000Z",
    "created_at": "2025-06-15T12:00:00.000000Z",
    "updated_at": "2025-07-01T12:00:00.000000Z"
    // Additional user fields...
  },
  "message": "User retrieved successfully"
}
```

#### 5. Update User Profile

```
PUT /user
```

Update the authenticated user's profile.

**Request:**

```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "phone": "+12125550123",
    "name": "John Doe",
    "email": "john@example.com",
    "phone_verified_at": "2025-07-01T12:00:00.000000Z",
    "created_at": "2025-06-15T12:00:00.000000Z",
    "updated_at": "2025-07-07T12:00:00.000000Z"
  },
  "message": "User updated successfully"
}
```

#### 6. Logout

```
POST /auth/logout
```

Invalidate the current API token.

**Response:**

```json
{
  "success": true,
  "data": null,
  "message": "Logged out successfully"
}
```

#### 7. Change Phone Number

```
POST /auth/phone/change
```

Change the authenticated user's phone number.

**Request:**

```json
{
  "phone": "+12125550124"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "expires_in": 600,
    "resend_after": 60
  },
  "message": "Verification code sent to new phone number"
}
```

#### 8. Verify New Phone Number

```
POST /auth/phone/change/verify
```

Verify the OTP sent to the new phone number.

**Request:**

```json
{
  "phone": "+12125550124",
  "code": "123456"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "phone": "+12125550124",
      "phone_verified_at": "2025-07-07T12:00:00.000000Z",
      "created_at": "2025-06-15T12:00:00.000000Z",
      "updated_at": "2025-07-07T12:00:00.000000Z"
    }
  },
  "message": "Phone number updated successfully"
}
```

#### 9. Delete Account

```
DELETE /user
```

Delete the authenticated user's account.

**Response:**

```json
{
  "success": true,
  "data": null,
  "message": "Account deleted successfully"
}
```

### SMS Delivery Status Webhook

```
POST /webhook/sms/status
```

Receives SMS delivery status updates from providers.

**Request (from provider):**

Format varies by provider. Example for Vonage:

```json
{
  "message_id": "abcdef1234567890",
  "to": "+12125550123",
  "status": "delivered",
  "timestamp": "2025-07-07T12:00:00.000000Z"
}
```

**Response:**

```json
{
  "success": true
}
```

## 📊 API Statistics and Analytics

```
GET /admin/stats/sms
```

Get SMS delivery statistics (admin only).

**Response:**

```json
{
  "success": true,
  "data": {
    "total_sent": 1250,
    "delivered": 1175,
    "failed": 75,
    "delivery_rate": 94.0,
    "providers": {
      "vonage": 750,
      "twilio": 450,
      "sns": 50
    },
    "daily": [
      {
        "date": "2025-07-01",
        "sent": 125,
        "delivered": 118
      },
      {
        "date": "2025-07-02",
        "sent": 147,
        "delivered": 139
      }
      // Additional dates...
    ]
  },
  "message": "Statistics retrieved successfully"
}
```

## 📋 API Testing Guide

### Postman Collection

A Postman collection is available for testing the API endpoints:

1. Download the [Postman Collection](https://example.com/postman/laravel-auth-starter-kits.json)
2. Import into Postman
3. Set up your environment variables
4. Run the requests

### Testing with cURL

#### Send OTP Example

```bash
curl -X POST "http://localhost:8000/api/auth/phone/send-otp" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"phone": "+12125550123"}'
```

#### Verify OTP Example

```bash
curl -X POST "http://localhost:8000/api/auth/phone/verify" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"phone": "+12125550123", "code": "123456"}'
```

### Test Phone Numbers

For development and testing, you can use these test phone numbers:

| Phone Number | Behavior |
|--------------|----------|
| +12025550142 | Always succeeds, OTP: 123456 |
| +12025550143 | Always fails delivery |
| +12025550144 | Delivery delay |
| +12025550145 | OTP expires immediately |

Configure test numbers in `config/sms.php` under `test_numbers`.

## 🧪 API Testing in CI/CD

### PHPUnit Tests

```bash
# Run API tests
php artisan test --filter=Api

# Run specific endpoint tests
php artisan test --filter=PhoneAuthTest
```

### GitHub Actions

The project includes GitHub Actions for automated API testing:

```yaml
name: API Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
      - name: Run API tests
        run: php artisan test --filter=Api
```

## 📝 API Versioning

The API uses versioning to ensure backwards compatibility.

### Current Version

The current stable API version is `v1`, which is the default:

```
/api/auth/phone/send-otp  (Default to v1)
```

### Explicit Versioning

You can explicitly specify the version:

```
/api/v1/auth/phone/send-otp
```

Future versions will be accessible via:

```
/api/v2/auth/phone/send-otp
```

### Deprecation Policy

- APIs are supported for at least 12 months after a new version is released
- Deprecated endpoints will return a warning header
- Deprecated endpoints will eventually return a 410 Gone status

## 🔒 API Security

### Rate Limiting

All API endpoints are rate-limited to prevent abuse. When a rate limit is exceeded, the API returns:

```json
{
  "success": false,
  "error": {
    "code": "rate_limited",
    "message": "Too many requests. Please try again in 60 seconds.",
    "details": {
      "available_in": 60
    }
  }
}
```

With HTTP status code `429 Too Many Requests`.

### CORS Policy

API endpoints support Cross-Origin Resource Sharing (CORS) for specified domains. Configure allowed origins in `config/cors.php`.

### API Tokens

API tokens automatically expire after 7 days by default. This can be configured in `config/sanctum.php`.

## 🚀 Next Steps

- Check out the [Frontend Guide](frontend.md) for integrating with the API
- Read the [Security Guide](security.md) for API security best practices
- Learn about [Deployment Options](deployment.md) for your API
- Explore [SMS Gateway Integration](sms-gateways.md) for custom providers

---

**Need help with the API?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
