<?php

use App\Services\JournalEntryService;
use Illuminate\Validation\ValidationException;

it('creates balanced journal entries atomically', function () {
    $entry = app(JournalEntryService::class)->create(
        ['entry_date' => '2026-08-05', 'description' => 'Cash sale'],
        [
            ['account_name' => 'Cash', 'debit' => 500, 'credit' => 0],
            ['account_name' => 'Sales', 'debit' => 0, 'credit' => 500],
        ],
    );

    expect($entry->lines)->toHaveCount(2)
        ->and((float) $entry->lines->sum('debit'))->toBe(500.0)
        ->and((float) $entry->lines->sum('credit'))->toBe(500.0);
});

it('rejects unbalanced journal entries without persisting data', function () {
    expect(fn () => app(JournalEntryService::class)->create(
        ['entry_date' => '2026-08-05'],
        [
            ['account_name' => 'Cash', 'debit' => 500, 'credit' => 0],
            ['account_name' => 'Sales', 'debit' => 0, 'credit' => 450],
        ],
    ))->toThrow(ValidationException::class);

    $this->assertDatabaseCount('journal_entries', 0);
    $this->assertDatabaseCount('journal_entry_lines', 0);
});
