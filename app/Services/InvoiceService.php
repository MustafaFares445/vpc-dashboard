<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function create(array $data, array $items): Invoice
    {
        [$normalizedItems, $subtotal] = $this->normalizeItems($items);
        $invoiceData = $this->prepareInvoiceData($data, $subtotal);
        $invoiceData['invoice_number'] = $invoiceData['invoice_number'] ?: $this->generateInvoiceNumber();

        return DB::transaction(function () use ($invoiceData, $normalizedItems): Invoice {
            $invoice = Invoice::query()->create($invoiceData);
            $invoice->items()->createMany($normalizedItems);
            return $invoice->load(['items', 'client']);
        });
    }

    public function update(Invoice $invoice, array $data, array $items): Invoice
    {
        [$normalizedItems, $subtotal] = $this->normalizeItems($items);
        $invoiceData = $this->prepareInvoiceData($data, $subtotal);

        return DB::transaction(function () use ($invoice, $invoiceData, $normalizedItems): Invoice {
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());
            $lockedInvoice->update($invoiceData);
            $lockedInvoice->items()->delete();
            $lockedInvoice->items()->createMany($normalizedItems);
            return $lockedInvoice->load(['items', 'client']);
        });
    }

    private function prepareInvoiceData(array $data, float $subtotal): array
    {
        $paidAmount = round((float) ($data['paid_amount'] ?? 0), 2);
        if ($paidAmount < 0 || $paidAmount > $subtotal) {
            throw ValidationException::withMessages(['data.paid_amount' => 'المبلغ المدفوع يجب أن يكون بين صفر وإجمالي الفاتورة.']);
        }

        $prepared = Arr::only($data, ['invoice_number', 'client_id', 'issue_date', 'due_date', 'paid_amount', 'notes', 'created_by']);
        $prepared['invoice_number'] = $prepared['invoice_number'] ?? null;
        $prepared['subtotal'] = $subtotal;
        $prepared['total'] = $subtotal;
        $prepared['paid_amount'] = $paidAmount;
        $prepared['status'] = InvoiceStatus::fromAmounts($subtotal, $paidAmount);
        return $prepared;
    }

    private function normalizeItems(array $items): array
    {
        if (count($items) < 1) {
            throw ValidationException::withMessages(['data.items' => 'يجب إضافة بند واحد على الأقل للفاتورة.']);
        }

        $normalized = [];
        $subtotal = 0.0;

        foreach (array_values($items) as $index => $item) {
            $quantity = round((float) ($item['quantity'] ?? 0), 2);
            $unitPrice = round((float) ($item['unit_price'] ?? 0), 2);

            if (blank($item['description'] ?? null)) {
                throw ValidationException::withMessages(["data.items.{$index}.description" => 'وصف البند مطلوب.']);
            }

            if ($quantity <= 0 || $unitPrice < 0) {
                throw ValidationException::withMessages(["data.items.{$index}" => 'الكمية يجب أن تكون موجبة والسعر لا يمكن أن يكون سالبًا.']);
            }

            $lineTotal = round($quantity * $unitPrice, 2);
            $subtotal += $lineTotal;
            $normalized[] = ['description' => $item['description'], 'quantity' => $quantity, 'unit_price' => $unitPrice, 'line_total' => $lineTotal, 'sort_order' => $index + 1];
        }

        return [$normalized, round($subtotal, 2)];
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
