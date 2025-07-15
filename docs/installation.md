# Installation Guide

This guide will walk you through the process of installing and setting up Laravel Auth Starter Kits on your system.

## 📋 Requirements

Before you begin, make sure your system meets the following requirements:

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- npm or yarn
- MySQL, PostgreSQL, or SQLite
- Laravel CLI (optional but recommended)

## 🚀 Quick Installation

### 1. Clone the Repository

```bash
# Clone the repository
git clone https://github.com/NanaBright/laravel-auth-starter-kits.git

# Navigate to the project directory
cd laravel-auth-starter-kits
```

### 2. Choose Your Authentication Kit

Navigate to your chosen authentication kit directory:

```bash
# For phone authentication
cd phone-auth
```

### 3. Install PHP Dependencies

```bash
composer install
```

### 4. Install JavaScript Dependencies

```bash
npm install
# or
yarn
```

### 5. Configure Environment

```bash
# Create .env file from the example
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 6. Configure Database

Edit the `.env` file and update the database connection information:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

For SQLite (simpler setup for development):

```
DB_CONNECTION=sqlite
# Leave other DB_* fields commented out
```

Then create the SQLite database file:

```bash
touch database/database.sqlite
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Configure SMS Gateway (Phone Auth Only)

Edit the `.env` file to configure your SMS gateway:

```
# Vonage (formerly Nexmo)
SMS_PROVIDER=vonage
VONAGE_KEY=your_vonage_key
VONAGE_SECRET=your_vonage_secret
VONAGE_FROM="Laravel Auth"

# OR for Twilio
SMS_PROVIDER=twilio
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_phone_number

# OR for AWS SNS
SMS_PROVIDER=sns
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=your_aws_region
AWS_SNS_SENDER_ID=Laravel
```

### 9. Start the Development Server

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite development server
npm run dev
# or
yarn dev
```

### 10. Visit Your Application

Open your browser and visit:

```
http://localhost:8000
```

## 🔍 Detailed Installation

### Database Setup Options

#### MySQL

1. Create a new MySQL database
2. Update `.env` with MySQL credentials

#### PostgreSQL

1. Create a new PostgreSQL database
2. Update `.env` with PostgreSQL credentials:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### SMS Gateway Configuration

#### Vonage (formerly Nexmo)

1. Create an account at [Vonage API](https://www.vonage.com/communications-apis/)
2. Get your API key and secret
3. Configure in `.env`
4. Set up webhooks in your Vonage dashboard (optional)

#### Twilio

1. Create an account at [Twilio](https://www.twilio.com/)
2. Get your SID and token from the dashboard
3. Purchase a phone number
4. Configure in `.env`

#### AWS SNS

1. Create an AWS account
2. Create IAM user with SNS full access
3. Configure in `.env`
4. Set up IAM policy for SMS sending only (recommended)

#### Custom SMS Provider

To use a custom SMS provider:

1. Create a new service class that implements `App\Services\SmsServiceInterface`
2. Register your service in `config/sms.php`
3. Configure any required API keys in `.env`

### Advanced Configuration

#### Rate Limiting

Customize rate limiting in `config/sms.php`:

```php
'rate_limiting' => [
    'max_attempts' => 5, // Maximum attempts
    'decay_minutes' => 10, // Time window in minutes
],
```

#### OTP Configuration

Customize OTP settings in `config/sms.php`:

```php
'otp' => [
    'length' => 6, // Length of OTP code
    'expiry' => 10, // Expiry time in minutes
    'type' => 'numeric', // 'numeric', 'alpha', 'alphanumeric'
],
```

#### Custom SMS Message

Customize the SMS message in `config/sms.php`:

```php
'messages' => [
    'verification' => 'Your verification code is: :otp. Valid for :minutes minutes.',
],
```

## 🐳 Docker Installation

### Using Docker Compose

1. Ensure Docker and Docker Compose are installed
2. Clone the repository
3. Navigate to the authentication kit

```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate
```

### Manual Docker Setup

1. Build the Docker image:

```bash
docker build -t laravel-auth-starter .
```

2. Run the container:

```bash
docker run -p 8000:80 laravel-auth-starter
```

## ⚠️ Troubleshooting

### Common Issues

#### Composer Memory Limit

If you encounter memory issues during installation:

```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

#### Permission Issues

If you encounter permission issues:

```bash
chmod -R 777 storage bootstrap/cache
```

#### SMS Not Sending

1. Check your SMS provider credentials
2. Ensure your account has sufficient credits
3. Check if phone numbers are in correct format (E.164)
4. Check Laravel logs in `storage/logs`

#### Database Connection Issues

1. Verify database credentials
2. Check if database server is running
3. Ensure database exists

### Getting Help

If you encounter any issues:

1. Check the [GitHub Issues](https://github.com/yourusername/laravel-auth-starter-kits/issues)
2. Join the [Discord Community](https://discord.gg/your-discord)
3. Ask on [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel-auth-starter-kits)

## 🔄 Updating

### Standard Update

```bash
# Pull the latest changes
git pull origin main

# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate
```

### Major Version Update

For major version updates, check the upgrade guide in the release notes.

## 🎯 Next Steps

- Read the [Configuration Guide](configuration.md) to customize your auth kit
- Explore the [API Documentation](api.md) to understand available endpoints
- Check the [Security Guide](security.md) for security best practices
- Learn about [deployment options](deployment.md) for production environments

---

**Having issues?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
