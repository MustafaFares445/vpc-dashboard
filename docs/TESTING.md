# Testing

The project uses Pest with Laravel and Livewire support.

```bash
php artisan test
vendor/bin/pint --test
```

The CI workflow uses SQLite and runs migrations, seeders, the frontend build, Pint, and the complete Pest suite.

Critical coverage includes:

- panel access and inactive users;
- role boundaries and record scoping;
- follow-up date synchronization;
- task completion and overdue logic;
- financial summaries;
- balanced journal entries and rollback behavior;
- invoice totals and payment statuses;
- calendar event scoping;
- compact report calculations.
