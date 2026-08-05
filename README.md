# VPC Dashboard

A focused internal MVP built with Laravel 13 and Filament 5 for simplified accounting, customer follow-ups, task management, an internal calendar, and compact reporting.

## Modules

- **Dashboard:** financial, task, and follow-up indicators.
- **CRM:** clients, assignments, interaction notes, and next follow-up dates.
- **Tasks:** priorities, status updates, deadlines, and automatic overdue detection.
- **Accounting:** income, expenses, costs, balanced journal entries, and invoices.
- **Calendar:** monthly view of client follow-ups and task deadlines.
- **Reports:** period-based financial and operational summary.

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 22+
- MySQL 8+ in production, or SQLite for local development and tests

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Set `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` before running the seeder to create the first administrator. The seeder intentionally creates no default credentials.

Open `/admin` after setup.

## Quality checks

```bash
vendor/bin/pint --test
php artisan test
```

Detailed documentation is available in [`docs/`](docs/).
