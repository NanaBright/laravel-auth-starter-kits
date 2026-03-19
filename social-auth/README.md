# Social Authentication Starter Kit

A Laravel 11 starter kit that provides authentication via social providers using Laravel Socialite.

## Features

- OAuth authentication with multiple providers:
  - Google
  - GitHub
  - Facebook
  - Twitter (X)
- Account linking (connect multiple social accounts)
- Automatic user creation on first login
- Profile synchronization
- API token authentication with Sanctum
- Vue.js 3 frontend with Tailwind CSS
- Dark mode support

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- SQLite/MySQL/PostgreSQL

## Installation

1. Clone and install dependencies:

```bash
cd social-auth
composer install
npm install
```

2. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Set up OAuth credentials in `.env`:

```env
# Google
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret

# GitHub
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret

# Facebook
FACEBOOK_CLIENT_ID=your-client-id
FACEBOOK_CLIENT_SECRET=your-client-secret

# Twitter
TWITTER_CLIENT_ID=your-client-id
TWITTER_CLIENT_SECRET=your-client-secret
```

4. Run migrations:

```bash
php artisan migrate
```

5. Start the development server:

```bash
php artisan serve
npm run dev
```

## OAuth Provider Setup

### Google

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `http://localhost:8000/auth/google/callback`

### GitHub

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Create a new OAuth App
3. Set authorization callback URL: `http://localhost:8000/auth/github/callback`

### Facebook

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app
3. Add Facebook Login product
4. Set valid OAuth redirect URI: `http://localhost:8000/auth/facebook/callback`

### Twitter (X)

1. Go to [Twitter Developer Portal](https://developer.twitter.com/)
2. Create a new project and app
3. Enable OAuth 2.0
4. Set callback URL: `http://localhost:8000/auth/twitter/callback`

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/auth/{provider}` | Redirect to OAuth provider |
| GET | `/auth/{provider}/callback` | Handle OAuth callback |
| POST | `/api/auth/logout` | Logout and revoke token |
| GET | `/api/user` | Get authenticated user |
| GET | `/api/user/connected-accounts` | List connected social accounts |
| DELETE | `/api/user/connected-accounts/{id}` | Disconnect a social account |

## Architecture

```
social-auth/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Auth/
│   │           └── SocialController.php
│   ├── Models/
│   │   ├── User.php
│   │   └── SocialAccount.php
│   └── Services/
│       └── SocialAuthService.php
├── config/
│   └── services.php
├── database/
│   └── migrations/
├── resources/
│   └── js/
│       └── pages/
│           ├── Login.vue
│           ├── Dashboard.vue
│           └── ConnectedAccounts.vue
└── routes/
    ├── api.php
    └── web.php
```

## Security Considerations

- OAuth state parameter is validated to prevent CSRF
- Tokens are securely stored using Sanctum
- Email verification is optional but recommended
- Rate limiting is applied to callback routes

## License

MIT License
