<?php

use App\Enums\ClientStatus;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Services\InvoiceService;
use Illuminate\Validation\ValidationException;

it('calculates invoice totals and payment status on the server', function () {
    $client = Client::query()->create(['name' => 'Acme', 'status' => ClientStatus::Active]);
    $invoice = app(InvoiceService::class)->create([
        'client_id' => $client->id, 'issue_date' => '2026-08-05', 'paid_amount' => 50,
    ], [
        ['description' => 'Consulting', 'quantity' => 2, 'unit_price' => 100],
        ['description' => 'Support', 'quantity' => 1, 'unit_price' => 50],
    ]);

    expect((float) $invoice->subtotal)->toBe(250.0)
        ->and((float) $invoice->total)->toBe(250.0)
        ->and((float) $invoice->items->sum('line_total'))->toBe(250.0)
        ->and($invoice->status)->toBe(InvoiceStatus::PartiallyPaid);
});

it('rejects a paid amount above the calculated total', function () {
    $client = Client::query()->create(['name' => 'Acme', 'status' => ClientStatus::Active]);
    expect(fn () => app(InvoiceService::class)->create([
        'client_id' => $client->id, 'issue_date' => '2026-08-05', 'paid_amount' => 101,
    ], [['description' => 'Service', 'quantity' => 1, 'unit_price' => 100]]))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('invoices', 0);
});
