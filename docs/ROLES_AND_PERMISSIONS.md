# Roles and Permissions

## Super Admin

`users.is_super_admin = true` is the system-owner bypass. A Super Admin can access every policy/permission and is the only user allowed to manage users, roles, and role permissions.

The system prevents the active Super Admin from removing their own Super Admin flag, deactivating themselves, or deleting the final active Super Admin.

## Users and employees

There is no separate Employee model. Every user where `is_super_admin = false` is an employee and can be selected for client assignment, task assignment, and client follow-up responsibility.

Employee profile data is stored on `users`: `phone`, `job_title`, `hire_date`, and `notes`.

## Roles

Roles are managed with `spatie/laravel-permission`. The built-in roles are:

- `admin`: receives all functional permissions, but cannot manage users/roles unless the user is also a Super Admin.
- `employee`: receives the default scoped employee permissions.

Super Admins can create additional roles and choose any seeded permissions for them from the Filament **Roles & Permissions** resource.

## Permission groups

- Clients: `clients.view`, `clients.manage`
- Follow-ups: `interactions.view`, `interactions.create`, `interactions.update`, `interactions.delete`
- Tasks: `tasks.view`, `tasks.create`, `tasks.update`, `tasks.manage`, `tasks.delete`
- Accounting: `accounting.view`, `accounting.manage`
- Audit logs: `audit-logs.view`
- Reports: `reports.view`

Policies and scoped queries use these permissions. Super Admin is applied through a global `Gate::before` bypass.
