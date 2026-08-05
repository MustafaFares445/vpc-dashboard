# Installation

## Requirements

PHP 8.3+, Composer 2, Node.js 22+, and either MySQL 8+ or SQLite.

## Steps

```bash
cp .env.example .env
composer install
php artisan key:generate
```

Configure the database and administrator variables:

```dotenv
APP_TIMEZONE=Asia/Jerusalem
APP_CURRENCY=USD
ADMIN_NAME="System Administrator"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD="use-a-strong-password"
```

Then initialize the application:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Run locally with `composer dev` or `php artisan serve`.
