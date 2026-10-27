# Production deployment guide

## 1. Server prerequisites

- Ubuntu 22.04+ or Debian 12+
- PHP 8.3, Composer 2, Nginx/Apache, MySQL 8, Redis 7+, Node.js 20+
- HTTPS certificate from Let’s Encrypt or a trusted CA
- `php-fpm`, `supervisord`, and a cron entry for Laravel scheduler

## 2. Recommended production .env values

```env
APP_NAME="Dhakebir Jersey House"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shop.example.com
APP_TIMEZONE=Asia/Dhaka
APP_LOCALE=bn
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sellapp
DB_USERNAME=sellapp
DB_PASSWORD=strong-password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=hello@example.com
MAIL_PASSWORD=secret
MAIL_FROM_ADDRESS="hello@shop.example.com"
MAIL_FROM_NAME="Dhakebir Jersey House"
```

## 3. Deployment steps

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
npm install
npm run build
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 4. Process supervision

```bash
php artisan queue:work --tries=3 --timeout=90 --sleep=5 --queue=default
php artisan schedule:run
```

Use `supervisord` or a systemd service to keep the queue worker alive and restart it automatically after crashes.

## 5. Security hardening checklist

- Restrict SSH access and disable password logins where possible.
- Keep the Laravel app, Composer packages, and OS packages updated.
- Run daily database backups and retain them off-site.
- Enable 2FA for admin users and disable unused roles.
- Use HTTPS only and set secure cookies in production.
- Limit file upload sizes and validate file types before storing media.
- Review Laravel logs and queue worker output regularly.

## 6. Nginx example

```nginx
server {
    listen 80;
    server_name shop.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name shop.example.com;

    ssl_certificate /etc/letsencrypt/live/shop.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/shop.example.com/privkey.pem;

    root /var/www/sellapp/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}
```

## 7. Operational notes

- Keep Redis cache and queue services healthy; they power sessions, cache, and background jobs.
- Configure monitoring for CPU, memory, and disk usage on the production VPS.
- Use a CDN for large product images and static assets when traffic grows.
- Track the latest deployment status in a release checklist before toggling maintenance mode off.
