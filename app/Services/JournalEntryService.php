<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryService
{
    public function create(array $data, array $lines): JournalEntry
    {
        $normalizedLines = $this->validateAndNormalizeLines($lines);

        return DB::transaction(function () use ($data, $normalizedLines): JournalEntry {
            $entry = JournalEntry::query()->create(Arr::only($data, ['entry_date', 'reference', 'description', 'created_by']));
            $entry->lines()->createMany($normalizedLines);

            return $entry->load('lines');
        });
    }

    private function validateAndNormalizeLines(array $lines): array
    {
        if (count($lines) < 2) {
            throw ValidationException::withMessages(['data.lines' => 'يجب أن يحتوي القيد على سطرين على الأقل.']);
        }

        $normalized = [];
        $debitTotal = 0.0;
        $creditTotal = 0.0;

        foreach (array_values($lines) as $index => $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit < 0 || $credit < 0 || ($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                throw ValidationException::withMessages(["data.lines.{$index}" => 'يجب أن يحتوي كل سطر على قيمة مدينة أو دائنة موجبة واحدة فقط.']);
            }

            if (blank($line['account_name'] ?? null)) {
                throw ValidationException::withMessages(["data.lines.{$index}.account_name" => 'اسم الحساب مطلوب.']);
            }

            $debitTotal += $debit;
            $creditTotal += $credit;
            $normalized[] = ['account_name' => $line['account_name'], 'debit' => $debit, 'credit' => $credit, 'notes' => $line['notes'] ?? null];
        }

        if (abs(round($debitTotal - $creditTotal, 2)) > 0.001) {
            throw ValidationException::withMessages(['data.lines' => 'يجب أن يتساوى إجمالي المدين مع إجمالي الدائن.']);
        }

        return $normalized;
    }
}
