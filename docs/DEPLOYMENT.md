# Deployment

## Environment

Set production database, mail, URL, timezone, currency, storage, queue, and administrator variables. Use `APP_DEBUG=false`.

## Commands

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan filament:optimize
```

Configure a queue worker for the database queue and a scheduler entry:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Ensure HTTPS, database backups, writable `storage` and `bootstrap/cache`, and log rotation are configured before launch.
