# Architecture

The application is intentionally organized as a small Laravel/Filament MVP.

- Eloquent models define relationships, casts, and reusable query scopes.
- Policies enforce admin and employee boundaries independently of navigation visibility.
- Services contain calculations and transactional business rules.
- Filament resources and pages provide the internal dashboard UI.
- Pest feature tests cover business rules and access boundaries.

## Core services

- `FinancialSummaryService`: period totals, profit, net profit, and percentage.
- `JournalEntryService`: validates and atomically stores balanced debit/credit entries.
- `InvoiceService`: recalculates line totals, invoice totals, and payment status on the server.
- `CalendarEventService`: combines scoped task deadlines and client follow-ups.
- `CompactReportService`: period-based financial and operational summary.

No generic ERP module system, external calendar synchronization, or customizable permission builder is included.
