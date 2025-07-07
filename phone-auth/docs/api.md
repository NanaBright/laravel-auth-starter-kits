# API Documentation

This document describes the REST API endpoints for the Custom SMS Authentication System.

## Base URL

```
http://localhost:8000/api
```

## Authentication

The API uses Laravel Sanctum for authentication. After successful OTP verification, you'll receive a bearer token.

### Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your-token}  // For protected routes
```

## Endpoints

### 1. Register User

Register a new user with phone number and password.

**Endpoint:** `POST /api/auth/register`

**Request Body:**
```json
{
  "phone": "+1234567890",
  "password": "securepassword"
}
```

**Response (201 Created):**
```json
{
  "message": "Registration successful. OTP sent to your phone.",
  "user_id": 1
}
```

**Response (422 Validation Error):**
```json
{
  "message": "Validation failed",
  "errors": {
    "phone": ["The phone has already been taken."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

---

### 2. Login User

Initiate login process for existing user.

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
  "phone": "+1234567890"
}
```

**Response (200 Success):**
```json
{
  "message": "OTP sent to your phone for verification."
}
```

**Response (404 Not Found):**
```json
{
  "message": "User not found"
}
```

---

### 3. Verify OTP

Verify the OTP code sent to the user's phone.

**Endpoint:** `POST /api/auth/verify-otp`

**Request Body:**
```json
{
  "phone": "+1234567890",
  "otp": "123456"
}
```

**Response (200 Success):**
```json
{
  "message": "Phone verified successfully",
  "user": {
    "id": 1,
    "phone": "+1234567890",
    "phone_verified_at": "2025-07-06T10:30:00.000000Z",
    "created_at": "2025-07-06T10:00:00.000000Z",
    "updated_at": "2025-07-06T10:30:00.000000Z"
  },
  "token": "1|abc123def456ghi789..."
}
```

**Response (400 Invalid OTP):**
```json
{
  "message": "Invalid or expired OTP"
}
```

---

### 4. Resend OTP

Resend OTP code to the user's phone.

**Endpoint:** `POST /api/auth/resend-otp`

**Request Body:**
```json
{
  "phone": "+1234567890"
}
```

**Response (200 Success):**
```json
{
  "message": "OTP resent successfully"
}
```

**Response (404 Not Found):**
```json
{
  "message": "User not found"
}
```

---

### 5. Logout User

Logout the authenticated user (revoke token).

**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {your-token}
```

**Response (200 Success):**
```json
{
  "message": "Logged out successfully"
}
```

---

### 6. Get User Profile

Get the authenticated user's profile information.

**Endpoint:** `GET /api/auth/user`

**Headers:**
```
Authorization: Bearer {your-token}
```

**Response (200 Success):**
```json
{
  "id": 1,
  "phone": "+1234567890",
  "phone_verified_at": "2025-07-06T10:30:00.000000Z",
  "created_at": "2025-07-06T10:00:00.000000Z",
  "updated_at": "2025-07-06T10:30:00.000000Z"
}
```

## Error Responses

### Common HTTP Status Codes

- `200` - Success
- `201` - Created (successful registration)
- `400` - Bad Request (invalid OTP, etc.)
- `401` - Unauthorized (missing/invalid token)
- `404` - Not Found (user not found)
- `422` - Validation Error
- `500` - Internal Server Error

### Error Response Format

```json
{
  "message": "Error description",
  "errors": {
    "field": ["Specific field error"]
  }
}
```

## Authentication Flow

### Complete Registration Flow

```javascript
// 1. Register user
const registerResponse = await fetch('/api/auth/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    phone: '+1234567890',
    password: 'securepassword'
  })
});

// 2. User receives OTP via SMS (check logs in development)

// 3. Verify OTP
const verifyResponse = await fetch('/api/auth/verify-otp', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '123456'
  })
});

const { token, user } = await verifyResponse.json();

// 4. Store token for future requests
localStorage.setItem('auth_token', token);
```

### Complete Login Flow

```javascript
// 1. Initiate login
const loginResponse = await fetch('/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    phone: '+1234567890'
  })
});

// 2. User receives OTP via SMS

// 3. Verify OTP (same as registration)
const verifyResponse = await fetch('/api/auth/verify-otp', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '123456'
  })
});

const { token, user } = await verifyResponse.json();
localStorage.setItem('auth_token', token);
```

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **SMS sending**: 10 SMS per hour, 50 SMS per day per phone number
- **OTP attempts**: 3 attempts per OTP code
- **Resend delay**: 60 seconds between resend requests

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1625097600
```

## CORS Configuration

The API supports CORS for frontend applications:

```javascript
// Allowed origins (configure in config/cors.php)
const allowedOrigins = [
  'http://localhost:3000',
  'http://127.0.0.1:3000',
  'https://yourdomain.com'
];
```

## Phone Number Format

### Accepted Formats

The API accepts various phone number formats:

```json
{
  "phone": "+1234567890"     // Preferred format
}
{
  "phone": "1234567890"      // 10 digits (US)
}
{
  "phone": "11234567890"     // 11 digits with country code
}
{
  "phone": "(123) 456-7890"  // Formatted (will be normalized)
}
```

### Validation Rules

- US phone numbers: 10 or 11 digits
- International format: starts with `+`
- Automatically normalized to `+1XXXXXXXXXX` format

## Development Testing

### Using Logger Method

When `SMS_METHOD=logger`, OTP codes are logged instead of sent:

```bash
# Watch logs for OTP codes
tail -f storage/logs/laravel.log
```

Example log entry:
```
[2025-07-06 10:30:00] local.INFO: SMS to +1234567890: Your verification code is: 123456. This code will expire in 10 minutes.
```

### Testing with cURL

```bash
# Register user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"phone":"+1234567890","password":"testpass123"}'

# Check logs for OTP, then verify
curl -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"phone":"+1234567890","otp":"123456"}'
```

## SDK Examples

### JavaScript/TypeScript

```typescript
class SmsAuthClient {
  private baseUrl: string;
  private token?: string;

  constructor(baseUrl: string = 'http://localhost:8000/api') {
    this.baseUrl = baseUrl;
  }

  async register(phone: string, password: string) {
    const response = await fetch(`${this.baseUrl}/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ phone, password })
    });
    
    return response.json();
  }

  async verifyOtp(phone: string, otp: string) {
    const response = await fetch(`${this.baseUrl}/auth/verify-otp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ phone, otp })
    });
    
    const data = await response.json();
    if (data.token) {
      this.token = data.token;
    }
    
    return data;
  }

  async getUser() {
    if (!this.token) throw new Error('Not authenticated');
    
    const response = await fetch(`${this.baseUrl}/auth/user`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    
    return response.json();
  }
}
```

### PHP (for server-to-server)

```php
<?php

class SmsAuthClient
{
    private $baseUrl;
    private $token;

    public function __construct($baseUrl = 'http://localhost:8000/api')
    {
        $this->baseUrl = $baseUrl;
    }

    public function register($phone, $password)
    {
        return $this->makeRequest('POST', '/auth/register', [
            'phone' => $phone,
            'password' => $password
        ]);
    }

    public function verifyOtp($phone, $otp)
    {
        $response = $this->makeRequest('POST', '/auth/verify-otp', [
            'phone' => $phone,
            'otp' => $otp
        ]);

        if (isset($response['token'])) {
            $this->token = $response['token'];
        }

        return $response;
    }

    private function makeRequest($method, $endpoint, $data = null)
    {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
```

## Webhooks (Future Feature)

Future versions will support webhooks for SMS delivery status:

```json
{
  "event": "sms.delivered",
  "data": {
    "message_id": "msg_123",
    "phone": "+1234567890",
    "status": "delivered",
    "timestamp": "2025-07-06T10:30:00Z"
  }
}
```

---

For more information, see our [Configuration Guide](configuration.md) or [Contributing Guide](../CONTRIBUTING.md).
