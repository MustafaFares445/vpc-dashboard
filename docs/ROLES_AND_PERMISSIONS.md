# Roles and Permissions

## Administrator

Administrators manage users, clients, assignments, tasks, accounting records, invoices, journal entries, reports, and audit logs.

The system prevents deleting, deactivating, or removing the admin role from the final active administrator.

## Employee

Employees can:

- view clients assigned to them;
- create and update follow-up interactions for those clients;
- view tasks assigned to them;
- update task status and execution notes;
- view their scoped calendar events.

Employees cannot manage users, accounting data, reports, audit logs, client master data, task assignment, priority, deadline, or administrative task fields.

All rules are enforced with Laravel policies and query scopes, not only hidden menu items.
