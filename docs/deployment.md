# Deployment Guide

This guide provides comprehensive instructions for deploying Laravel Auth Starter Kits to various production environments.

## 📋 Pre-Deployment Checklist

Before deploying to production, ensure you have completed these critical steps:

### Security
- [ ] Generate a new application key (`php artisan key:generate`)
- [ ] Set up proper environment variables
- [ ] Configure HTTPS
- [ ] Enable CSRF protection
- [ ] Set up proper error handling

### Performance
- [ ] Cache configurations (`php artisan config:cache`)
- [ ] Cache routes (`php artisan route:cache`)
- [ ] Optimize autoloader (`composer install --optimize-autoloader --no-dev`)
- [ ] Compile frontend assets (`npm run build`)

### Database
- [ ] Run migrations (`php artisan migrate`)
- [ ] Seed required data (`php artisan db:seed`)
- [ ] Configure database for production use
- [ ] Set up proper database indexing

## 🖥️ Standard Server Deployment

### System Requirements

- PHP >= 8.1
- Composer
- Node.js >= 16.x and npm
- Web server (Apache/Nginx)
- Database (MySQL/PostgreSQL)
- SSL certificate

### Step-by-Step Deployment

#### 1. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and npm
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs
```

#### 2. Web Server Setup

##### Nginx Configuration

Create a new Nginx configuration:

```nginx
# /etc/nginx/sites-available/laravel-auth

server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name your-domain.com www.your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    root /var/www/laravel-auth/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;";
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/laravel-auth /etc/nginx/sites-enabled/
sudo nginx -t  # Test configuration
sudo systemctl restart nginx
```

##### Apache Configuration

Create a new Apache virtual host:

```apache
# /etc/apache2/sites-available/laravel-auth.conf

<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    
    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    
    DocumentRoot /var/www/laravel-auth/public
    
    <Directory /var/www/laravel-auth/public>
        Options -Indexes +FollowSymLinks +MultiViews
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem
    
    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;"
    
    ErrorLog ${APACHE_LOG_DIR}/laravel-auth-error.log
    CustomLog ${APACHE_LOG_DIR}/laravel-auth-access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite laravel-auth.conf
sudo a2enmod rewrite ssl headers
sudo apache2ctl configtest  # Test configuration
sudo systemctl restart apache2
```

#### 3. Application Deployment

```bash
# Clone repository
cd /var/www
git clone https://github.com/yourusername/laravel-auth-starter-kits.git laravel-auth
cd laravel-auth

# Navigate to the specific kit
cd phone-auth

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel-auth
sudo chmod -R 755 /var/www/laravel-auth
sudo chmod -R 775 /var/www/laravel-auth/storage /var/www/laravel-auth/bootstrap/cache

# Create and configure environment file
cp .env.example .env
php artisan key:generate

# Configure .env file
# (Edit .env with proper production settings)

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

#### 4. SSL Certificate with Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx  # For Nginx
# OR
sudo apt install -y certbot python3-certbot-apache  # For Apache

# Generate certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com  # For Nginx
# OR
sudo certbot --apache -d your-domain.com -d www.your-domain.com  # For Apache

# Auto-renewal
sudo systemctl status certbot.timer  # Verify auto-renewal is active
```

## 🐳 Docker Deployment

### Using Docker Compose

#### 1. Create Docker Compose Configuration

Create a `docker-compose.yml` file in your project root:

```yaml
version: '3'

services:
  app:
    build:
      context: ./phone-auth
      dockerfile: Dockerfile
    image: laravel-auth-starter/phone-auth
    container_name: laravel-auth-app
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - DB_PORT=3306
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - SMS_PROVIDER=${SMS_PROVIDER}
    volumes:
      - ./phone-auth:/var/www
      - ./phone-auth/.env.docker:/var/www/.env
    depends_on:
      - db
      - redis
    networks:
      - laravel-auth-network

  nginx:
    image: nginx:alpine
    container_name: laravel-auth-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./phone-auth:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - laravel-auth-network

  db:
    image: mysql:8.0
    container_name: laravel-auth-db
    restart: unless-stopped
    environment:
      - MYSQL_DATABASE=${DB_DATABASE}
      - MYSQL_USER=${DB_USERNAME}
      - MYSQL_PASSWORD=${DB_PASSWORD}
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
    volumes:
      - db-data:/var/lib/mysql
    networks:
      - laravel-auth-network

  redis:
    image: redis:alpine
    container_name: laravel-auth-redis
    restart: unless-stopped
    networks:
      - laravel-auth-network

networks:
  laravel-auth-network:
    driver: bridge

volumes:
  db-data:
    driver: local
```

#### 2. Create Dockerfile

Create a `Dockerfile` in your authentication kit directory (e.g., `phone-auth/Dockerfile`):

```dockerfile
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-install gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Change current user to www-data
USER www-data

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
```

#### 3. Create Nginx Configuration

Create the file `docker/nginx/conf.d/app.conf`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name your-domain.com www.your-domain.com;
    
    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;
    
    index index.php;
    root /var/www/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;";
}
```

#### 4. Create Environment File

Create a `.env.docker` file in your auth kit directory:

```
APP_NAME="Laravel Auth"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel_auth
DB_USERNAME=laravel_user
DB_PASSWORD=secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

SMS_PROVIDER=vonage
VONAGE_KEY=your_vonage_key
VONAGE_SECRET=your_vonage_secret
VONAGE_FROM="Laravel Auth"
```

#### 5. Deploy with Docker Compose

```bash
# Create .env file for Docker Compose
cat > .env << EOL
DB_DATABASE=laravel_auth
DB_USERNAME=laravel_user
DB_PASSWORD=secure_password
DB_ROOT_PASSWORD=very_secure_password
SMS_PROVIDER=vonage
EOL

# Create necessary directories
mkdir -p docker/nginx/ssl

# Copy SSL certificates (replace with your actual certificates)
cp /path/to/your/fullchain.pem docker/nginx/ssl/
cp /path/to/your/privkey.pem docker/nginx/ssl/

# Start containers
docker-compose up -d

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --force

# Cache configurations
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## ☁️ Cloud Deployments

### AWS Deployment

#### Using AWS Elastic Beanstalk

1. Install the EB CLI:
   ```bash
   pip install awsebcli
   ```

2. Initialize your EB project:
   ```bash
   cd phone-auth
   eb init
   ```

3. Create `.ebextensions/01_environment.config`:
   ```yaml
   option_settings:
     aws:elasticbeanstalk:container:php:phpini:
       document_root: /public
       memory_limit: 256M
       zlib.output_compression: "true"
       allow_url_fopen: "true"
     aws:elasticbeanstalk:application:environment:
       APP_ENV: production
       APP_DEBUG: false
   ```

4. Create a `.platform/nginx/conf.d/elasticbeanstalk/laravel.conf` file:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

5. Create a `Procfile`:
   ```
   web: vendor/bin/heroku-php-nginx -C .platform/nginx/conf.d/elasticbeanstalk/laravel.conf public/
   ```

6. Deploy to Elastic Beanstalk:
   ```bash
   eb create laravel-auth-env
   ```

7. Configure environment variables:
   ```bash
   eb setenv APP_KEY=$(php artisan key:generate --show) \
              DB_CONNECTION=mysql \
              DB_HOST=your-rds-endpoint \
              DB_PORT=3306 \
              DB_DATABASE=laravel_auth \
              DB_USERNAME=admin \
              DB_PASSWORD=secure_password \
              SMS_PROVIDER=vonage \
              VONAGE_KEY=your_key \
              VONAGE_SECRET=your_secret
   ```

### Google Cloud Run

1. Create a `cloudbuild.yaml` file:
   ```yaml
   steps:
   - name: 'gcr.io/cloud-builders/docker'
     args: ['build', '-t', 'gcr.io/$PROJECT_ID/laravel-auth:$COMMIT_SHA', '.']
   
   - name: 'gcr.io/cloud-builders/docker'
     args: ['push', 'gcr.io/$PROJECT_ID/laravel-auth:$COMMIT_SHA']
   
   - name: 'gcr.io/google.com/cloudsdktool/cloud-sdk'
     entrypoint: gcloud
     args:
     - 'run'
     - 'deploy'
     - 'laravel-auth'
     - '--image'
     - 'gcr.io/$PROJECT_ID/laravel-auth:$COMMIT_SHA'
     - '--region'
     - 'us-central1'
     - '--platform'
     - 'managed'
     - '--allow-unauthenticated'
     - '--set-env-vars'
     - 'APP_ENV=production,APP_DEBUG=false,DB_CONNECTION=mysql,DB_HOST=10.0.0.4,DB_PORT=3306,DB_DATABASE=laravel_auth,DB_USERNAME=laravel_user,DB_PASSWORD=secure_password,SMS_PROVIDER=vonage,VONAGE_KEY=your_key,VONAGE_SECRET=your_secret'
   
   images:
   - 'gcr.io/$PROJECT_ID/laravel-auth:$COMMIT_SHA'
   ```

2. Deploy to Cloud Run:
   ```bash
   gcloud builds submit --config cloudbuild.yaml
   ```

### Heroku Deployment

1. Create a `Procfile` in your auth kit directory:
   ```
   web: vendor/bin/heroku-php-apache2 public/
   ```

2. Initialize Git repository (if not already done):
   ```bash
   cd phone-auth
   git init
   git add .
   git commit -m "Initial commit for Heroku"
   ```

3. Create Heroku app:
   ```bash
   heroku create laravel-auth-starter
   ```

4. Add database:
   ```bash
   heroku addons:create heroku-postgresql:hobby-dev
   ```

5. Configure environment variables:
   ```bash
   heroku config:set \
     APP_NAME="Laravel Auth" \
     APP_ENV=production \
     APP_KEY=$(php artisan key:generate --show) \
     APP_DEBUG=false \
     APP_URL=https://laravel-auth-starter.herokuapp.com \
     SMS_PROVIDER=vonage \
     VONAGE_KEY=your_vonage_key \
     VONAGE_SECRET=your_vonage_secret
   ```

6. Deploy to Heroku:
   ```bash
   git push heroku main
   ```

7. Run migrations:
   ```bash
   heroku run php artisan migrate --force
   ```

## 🔄 Continuous Integration/Deployment

### GitHub Actions CI/CD Pipeline

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  tests:
    name: Run tests
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, dom, fileinfo, mysql
        coverage: xdebug
    
    - name: Get composer cache directory
      id: composer-cache
      run: echo "::set-output name=dir::$(composer config cache-files-dir)"
    
    - name: Cache composer dependencies
      uses: actions/cache@v2
      with:
        path: ${{ steps.composer-cache.outputs.dir }}
        key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
        restore-keys: ${{ runner.os }}-composer-
    
    - name: Install dependencies
      run: |
        cd phone-auth
        composer install --no-progress --prefer-dist --optimize-autoloader
    
    - name: Prepare environment
      run: |
        cd phone-auth
        cp .env.example .env
        php artisan key:generate
        touch database/database.sqlite
    
    - name: Run tests
      run: |
        cd phone-auth
        php artisan test

  deploy:
    name: Deploy to production
    runs-on: ubuntu-latest
    needs: tests
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup SSH
      uses: webfactory/ssh-agent@v0.5.3
      with:
        ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
    
    - name: Deploy to server
      run: |
        ssh -o StrictHostKeyChecking=no ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }} << 'EOF'
          cd /var/www/laravel-auth
          git pull origin main
          cd phone-auth
          composer install --optimize-autoloader --no-dev
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          npm install
          npm run build
          chown -R www-data:www-data .
        EOF
```

## 📊 Monitoring and Maintenance

### Server Monitoring

#### Setting Up Prometheus and Grafana

1. Install Prometheus:
   ```bash
   sudo apt update
   sudo apt install -y prometheus prometheus-node-exporter
   ```

2. Install Grafana:
   ```bash
   sudo apt-get install -y apt-transport-https software-properties-common
   wget -q -O - https://packages.grafana.com/gpg.key | sudo apt-key add -
   sudo add-apt-repository "deb https://packages.grafana.com/oss/deb stable main"
   sudo apt update
   sudo apt install -y grafana
   sudo systemctl enable grafana-server
   sudo systemctl start grafana-server
   ```

3. Configure Laravel to expose metrics using `laravel-prometheus-exporter` package

### Log Management

#### Using Laravel's Built-in Logging

Configure log rotation in `config/logging.php`:

```php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
],
```

#### Setting Up ELK Stack (Elasticsearch, Logstash, Kibana)

1. Install Docker and Docker Compose
2. Create `elk/docker-compose.yml`:

```yaml
version: '3'
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:7.14.0
    environment:
      - discovery.type=single-node
    ports:
      - 9200:9200
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data

  logstash:
    image: docker.elastic.co/logstash/logstash:7.14.0
    ports:
      - 5000:5000
    volumes:
      - ./logstash/pipeline:/usr/share/logstash/pipeline
    depends_on:
      - elasticsearch

  kibana:
    image: docker.elastic.co/kibana/kibana:7.14.0
    ports:
      - 5601:5601
    depends_on:
      - elasticsearch

volumes:
  elasticsearch_data:
```

3. Create Logstash pipeline configuration:

```
input {
  tcp {
    port => 5000
    codec => json
  }
}

filter {
  if [type] == "laravel" {
    grok {
      match => { "message" => "%{COMBINEDAPACHELOG}" }
    }
    date {
      match => [ "timestamp", "dd/MMM/yyyy:HH:mm:ss Z" ]
    }
  }
}

output {
  elasticsearch {
    hosts => ["elasticsearch:9200"]
    index => "laravel-%{+YYYY.MM.dd}"
  }
}
```

4. Configure Laravel for ELK in `config/logging.php`:

```php
'channels' => [
    'elk' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'debug'),
        'handler' => Monolog\Handler\SocketHandler::class,
        'handler_with' => [
            'connection_string' => 'tcp://logstash:5000',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],
],
```

### Database Backups

Set up automated database backups:

```bash
# Create backup script
cat > /usr/local/bin/db_backup.sh << 'EOL'
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/laravel-auth"
mkdir -p $BACKUP_DIR

# MySQL backup
mysqldump -u your_db_user -p'your_password' your_db_name | gzip > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql.gz"

# Keep only last 7 days of backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -type f -mtime +7 -delete
EOL

# Make script executable
chmod +x /usr/local/bin/db_backup.sh

# Add to crontab
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/db_backup.sh") | crontab -
```

### Automating Updates

Create an update script:

```bash
# Create update script
cat > /usr/local/bin/update_laravel_auth.sh << 'EOL'
#!/bin/bash
cd /var/www/laravel-auth

# Pull latest changes
git pull origin main

# Update dependencies
cd phone-auth
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Refresh application
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
chown -R www-data:www-data .

# Restart services if needed
sudo systemctl restart php8.1-fpm
EOL

# Make script executable
chmod +x /usr/local/bin/update_laravel_auth.sh

# Add to crontab for weekly updates
(crontab -l 2>/dev/null; echo "0 3 * * 0 /usr/local/bin/update_laravel_auth.sh") | crontab -
```

## 🚀 Scaling Your Application

### Horizontal Scaling

1. Use a load balancer (AWS ELB, Nginx, HAProxy)
2. Ensure session management works across multiple servers:
   
   ```php
   // config/session.php
   'driver' => env('SESSION_DRIVER', 'redis'),
   'connection' => env('SESSION_CONNECTION', 'session'),
   ```

3. Configure cache and queue to use Redis:

   ```php
   // config/cache.php
   'default' => env('CACHE_DRIVER', 'redis'),
   
   // config/queue.php
   'default' => env('QUEUE_CONNECTION', 'redis'),
   ```

### Database Scaling

1. Use database replication for read-heavy workloads:

   ```php
   // config/database.php
   'mysql' => [
       'read' => [
           'host' => [
               env('DB_READ_HOST_1', '127.0.0.1'),
               env('DB_READ_HOST_2', '127.0.0.1'),
           ],
       ],
       'write' => [
           'host' => env('DB_WRITE_HOST', '127.0.0.1'),
       ],
       // Other configuration...
   ],
   ```

2. Implement caching for expensive queries
3. Use proper indexing for frequently accessed columns

## 🔄 Rollback Strategy

If a deployment causes issues, have a rollback plan:

```bash
# Create rollback script
cat > /usr/local/bin/rollback.sh << 'EOL'
#!/bin/bash
cd /var/www/laravel-auth

# Specify the commit or tag to roll back to
ROLLBACK_COMMIT=$1

if [ -z "$ROLLBACK_COMMIT" ]; then
    echo "Please specify a commit or tag to roll back to"
    exit 1
fi

# Roll back to specified commit
git checkout $ROLLBACK_COMMIT

# Update dependencies
cd phone-auth
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Refresh application
php artisan migrate:refresh --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
chown -R www-data:www-data .

# Restart services
sudo systemctl restart php8.1-fpm
EOL

# Make script executable
chmod +x /usr/local/bin/rollback.sh
```

Usage:
```bash
/usr/local/bin/rollback.sh v1.2.3
```

## 🚀 Next Steps

- Explore [API Documentation](api.md) for integrating with your application
- Check out [SMS Gateway Integration](sms-gateways.md) for phone authentication
- Review [Security Guide](security.md) for hardening your deployment
- Learn about [Frontend Customization](frontend.md) for theming your auth UI

---

**Need deployment help?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
