# VPC Dashboard

A small internal MVP built with Laravel 13 and Filament 5 for:

- simplified accounting;
- client follow-up management;
- task management;
- internal calendar and compact reporting.

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 22+
- MySQL 8+ for production, or SQLite for local development

## Installation

```bash
composer setup
php artisan db:seed
composer dev
```

Open `/admin` and sign in with the administrator configured through:

```dotenv
ADMIN_NAME=
ADMIN_EMAIL=
ADMIN_PASSWORD=
```

The seeder does not create a default password when these variables are missing.

## Quality checks

```bash
composer quality
```

## Architecture

The application intentionally remains a small Laravel/Filament MVP. Business rules live in services and models, while Filament resources provide the internal user interface.
