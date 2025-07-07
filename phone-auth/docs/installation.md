# Installation Guide

This guide will help you install and set up the Custom SMS Authentication System from scratch.

## System Requirements

### Minimum Requirements

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Node.js**: 16.x or higher
- **npm**: 8.x or higher
- **Database**: MySQL 8.0+, PostgreSQL 13+, or SQLite 3.8+
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### Recommended Environment

- **PHP**: 8.2 with OPcache enabled
- **Memory**: 512MB minimum, 1GB recommended
- **Storage**: 1GB free space
- **Database**: MySQL 8.0+ or PostgreSQL 14+

### PHP Extensions

Ensure these PHP extensions are installed:

```bash
# Required extensions
php -m | grep -E "(openssl|pdo|mbstring|tokenizer|xml|ctype|json|bcmath|curl|fileinfo)"

# For MySQL
php -m | grep pdo_mysql

# For PostgreSQL  
php -m | grep pdo_pgsql
```

## Installation Methods

### Method 1: Clone from GitHub (Recommended)

```bash
# Clone the repository
git clone https://github.com/yourusername/custom-sms-auth.git
cd custom-sms-auth

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Method 2: Download Release

```bash
# Download latest release
wget https://github.com/yourusername/custom-sms-auth/archive/v1.0.0.zip
unzip v1.0.0.zip
cd custom-sms-auth-1.0.0

# Continue with installation steps above
composer install --no-dev --optimize-autoloader
npm install --production
```

### Method 3: Composer Create-Project

```bash
# Create project via Composer (when available)
composer create-project yourusername/custom-sms-auth my-sms-auth
cd my-sms-auth
```

## Environment Configuration

### 1. Database Setup

#### MySQL Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE phone_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sms_auth'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON phone_auth.* TO 'sms_auth'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### PostgreSQL Setup

```bash
# Create database
sudo -u postgres psql
CREATE DATABASE phone_auth;
CREATE USER sms_auth WITH ENCRYPTED PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE phone_auth TO sms_auth;
\q
```

### 2. Environment File Configuration

Edit `.env` file:

```env
# Application Settings
APP_NAME="Custom SMS Auth"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phone_auth
DB_USERNAME=sms_auth
DB_PASSWORD=secure_password

# SMS Configuration (Start with logger for development)
SMS_METHOD=logger
SMS_LOG_ALL=true
SMS_LOG_FAILED=true

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache Configuration
CACHE_STORE=file

# Queue Configuration
QUEUE_CONNECTION=database
```

### 3. Run Database Migrations

```bash
# Run migrations
php artisan migrate

# Optional: Seed sample data
php artisan db:seed
```

## Development Setup

### 1. Start Backend Server

```bash
# Start Laravel development server
php artisan serve

# Server will be available at http://127.0.0.1:8000
```

### 2. Start Frontend Development

```bash
# In a new terminal, start Vite development server
npm run dev

# Frontend assets will be served with hot reload
```

### 3. Verify Installation

Visit `http://localhost:8000` in your browser. You should see the authentication interface.

### 4. Test SMS Functionality

1. Set `SMS_METHOD=logger` in `.env`
2. Try registering with a phone number
3. Check `storage/logs/laravel.log` for OTP codes
4. Complete the verification process

## Production Setup

### 1. Web Server Configuration

#### Apache Configuration

Create virtual host in `/etc/apache2/sites-available/sms-auth.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/sms-auth/public
    
    <Directory /var/www/sms-auth/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sms-auth-error.log
    CustomLog ${APACHE_LOG_DIR}/sms-auth-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite sms-auth
sudo systemctl reload apache2
```

#### Nginx Configuration

Create config in `/etc/nginx/sites-available/sms-auth`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/sms-auth/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/sms-auth /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

### 2. Production Environment

```env
# Production settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use production SMS method
SMS_METHOD=http_api
SMS_GATEWAY_URL=https://api.your-sms-provider.com/send
SMS_USERNAME=your_production_username
SMS_PASSWORD=your_production_password

# Secure session settings
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Production database
DB_HOST=your-production-db-host
DB_DATABASE=phone_auth_prod
DB_USERNAME=sms_auth_prod
DB_PASSWORD=very_secure_password

# Cache and sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=redis_password
REDIS_PORT=6379
```

### 3. Build Production Assets

```bash
# Build optimized assets
npm run build

# Optimize Composer autoloader
composer install --no-dev --optimize-autoloader

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link
```

### 4. File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/sms-auth

# Set correct permissions
sudo find /var/www/sms-auth -type f -exec chmod 644 {} \;
sudo find /var/www/sms-auth -type d -exec chmod 755 {} \;

# Make storage and cache writable
sudo chmod -R 775 /var/www/sms-auth/storage
sudo chmod -R 775 /var/www/sms-auth/bootstrap/cache
```

## Docker Installation

### Using Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=local
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    volumes:
      - .:/var/www/html

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: phone_auth
      MYSQL_USER: sms_auth
      MYSQL_PASSWORD: password
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data

  npm:
    image: node:18-alpine
    working_dir: /app
    volumes:
      - .:/app
    command: npm run dev

volumes:
  mysql_data:
  redis_data:
```

Create `Dockerfile`:

```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
```

Start with Docker:

```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# View logs
docker-compose logs -f app
```

## Troubleshooting

### Common Installation Issues

#### 1. Composer Memory Issues

```bash
# Increase memory limit
php -d memory_limit=2G /usr/local/bin/composer install

# Or set environment variable
export COMPOSER_MEMORY_LIMIT=2G
composer install
```

#### 2. Permission Errors

```bash
# Fix storage permissions
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# Change ownership if needed
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
```

#### 3. Database Connection Issues

```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check database service
sudo systemctl status mysql
# or
sudo systemctl status postgresql
```

#### 4. Node.js/npm Issues

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Update Node.js if too old
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Verification Checklist

After installation, verify these work:

- [ ] Laravel server starts: `php artisan serve`
- [ ] Frontend builds: `npm run dev`
- [ ] Database connection: `php artisan migrate:status`
- [ ] Registration form loads at `http://localhost:8000`
- [ ] SMS logging works (check `storage/logs/laravel.log`)
- [ ] OTP verification completes successfully

### Performance Optimization

#### 1. PHP Optimization

```ini
; php.ini optimizations
memory_limit = 256M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

#### 2. Database Optimization

```sql
-- Add indexes for better performance
CREATE INDEX idx_otp_phone_expires ON otp_verifications(phone, expires_at);
CREATE INDEX idx_users_phone_verified ON users(phone, phone_verified_at);
```

#### 3. Caching Setup

```bash
# Enable Redis for caching
composer require predis/predis

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Next Steps

After successful installation:

1. **Configure SMS Method** - See [Configuration Guide](configuration.md)
2. **Set up SMS Gateway** - See [SMS Gateway Setup](sms-gateways.md)
3. **Review Security** - See [Security Policy](../SECURITY.md)
4. **Deploy to Production** - Follow production setup above
5. **Monitor and Maintain** - Set up log monitoring and backups

## Getting Help

If you encounter issues:

- Check our [Configuration Guide](configuration.md)
- Review [Troubleshooting section](#troubleshooting)
- Search [GitHub Issues](https://github.com/yourusername/custom-sms-auth/issues)
- Join our [Discussions](https://github.com/yourusername/custom-sms-auth/discussions)

---

Ready to configure your SMS gateway? Continue to [SMS Gateway Setup](sms-gateways.md).
