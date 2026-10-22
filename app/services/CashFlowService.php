<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CashFlowService
{
    private array $cashCodes;
    private array $legacyCashCodes;
    private array $categories;
    private array $prefixFallbacks;
    private float $tolerance;

    public function __construct()
    {
        $this->cashCodes = array_values(array_map('strval', config('orion_cash_flow.cash_accounts', ['1101', '1102', '1103', '1110'])));
        $this->legacyCashCodes = array_values(array_map('strval', config('orion_cash_flow.legacy_cash_accounts', ['1111', '1112', '1113'])));
        $this->categories = config('orion_cash_flow.account_categories', []);
        $this->prefixFallbacks = config('orion_cash_flow.prefix_fallbacks', []);
        $this->tolerance = (float) config('orion_cash_flow.tolerance', 0.01);
    }

    public function buildReport(
        string $dateFrom,
        string $dateTo,
        ?string $comparisonFrom = null,
        ?string $comparisonTo = null,
    ): array {
        $current = $this->analysePeriod($dateFrom, $dateTo);

        $comparison = ($comparisonFrom && $comparisonTo)
            ? $this->analysePeriod($comparisonFrom, $comparisonTo)
            : $this->emptyAnalysis();

        $openingCash = $this->cashBalanceBefore($dateFrom);
        $closingCash = $this->cashBalanceAt($dateTo);
        $actualCashChange = $closingCash - $openingCash;

        $netClassifiedFlow = $current['totals']['operating']
            + $current['totals']['investing']
            + $current['totals']['financing'];

        $reconciliationDifference = ($openingCash + $netClassifiedFlow) - $closingCash;

        return [
            'current' => $current,
            'comparison' => $comparison,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'actual_cash_change' => $actualCashChange,
            'net_classified_flow' => $netClassifiedFlow,
            'reconciliation_difference' => $reconciliationDifference,
            'is_reconciled' => abs($reconciliationDifference) < $this->tolerance,
            'cash_positions' => $this->cashPositionsAt($dateTo),
            'waterfall' => $this->makeWaterfall([
                'operating' => $current['totals']['operating'],
                'investing' => $current['totals']['investing'],
                'financing' => $current['totals']['financing'],
                'net' => $netClassifiedFlow,
            ]),
            'legacy_warning' => $this->legacyCashWarning($dateTo),
        ];
    }

    public function analysePeriod(string $dateFrom, string $dateTo): array
    {
        $entries = JournalEntry::query()
            ->with(['lines.account'])
            ->where('status', 'posted')
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $totals = [
            'operating' => 0.0,
            'investing' => 0.0,
            'financing' => 0.0,
            'unclassified' => 0.0,
        ];

        $movements = [];
        $internalTransferCount = 0;
        $internalTransferVolume = 0.0;
        $cashEntriesCount = 0;

        foreach ($entries as $entry) {
            $lines = $entry->lines->filter(fn ($line) => $line->account !== null);
            if ($lines->isEmpty()) {
                continue;
            }

            $cashLines = $lines->filter(fn ($line) => $this->isCashCode((string) $line->account->code));
            if ($cashLines->isEmpty()) {
                continue;
            }

            $cashEntriesCount++;

            $cashDebit = (float) $cashLines->sum(fn ($line) => (float) $line->debit);
            $cashCredit = (float) $cashLines->sum(fn ($line) => (float) $line->credit);
            $cashEffect = $cashDebit - $cashCredit;

            if ($cashDebit > $this->tolerance && $cashCredit > $this->tolerance) {
                $internalTransferVolume += min($cashDebit, $cashCredit);
            }

            // Pure cash-to-cash transfer, for example 1110 Dr / 1101 Cr.
            if (abs($cashEffect) < $this->tolerance) {
                $internalTransferCount++;
                continue;
            }

            $nonCashLines = $lines->reject(fn ($line) => $this->isCashCode((string) $line->account->code));

            if ($nonCashLines->isEmpty()) {
                $totals['unclassified'] += $cashEffect;
                $movements[] = $this->movementRow($entry, null, 'unclassified', $cashEffect, 'لا يوجد طرف مقابل غير نقدي');
                continue;
            }

            // For cash inflows use credit-side counterpart lines. For outflows use debit-side counterpart lines.
            $relevant = $nonCashLines->filter(function ($line) use ($cashEffect): bool {
                $net = (float) $line->debit - (float) $line->credit;
                return $cashEffect > 0 ? $net < -$this->tolerance : $net > $this->tolerance;
            });

            // Defensive fallback for unusual multi-line journals.
            if ($relevant->isEmpty()) {
                $relevant = $nonCashLines->filter(function ($line): bool {
                    return abs((float) $line->debit - (float) $line->credit) >= $this->tolerance;
                });
            }

            $relevantWeight = (float) $relevant->sum(function ($line): float {
                return abs((float) $line->debit - (float) $line->credit);
            });

            if ($relevantWeight < $this->tolerance) {
                $totals['unclassified'] += $cashEffect;
                $movements[] = $this->movementRow($entry, null, 'unclassified', $cashEffect, 'تعذر تحديد الطرف المقابل');
                continue;
            }

            foreach ($relevant as $line) {
                $weight = abs((float) $line->debit - (float) $line->credit) / $relevantWeight;
                $allocatedCash = $cashEffect * $weight;
                $code = (string) $line->account->code;
                $category = $this->categoryForCode($code);

                if ($category === 'none') {
                    $category = 'unclassified';
                }

                $totals[$category] += $allocatedCash;
                $movements[] = $this->movementRow($entry, $line, $category, $allocatedCash);
            }
        }

        $grouped = $this->groupMovements($movements);

        return [
            'totals' => $totals,
            'movements' => array_values(array_reverse($movements)),
            'grouped' => $grouped,
            'cash_entries_count' => $cashEntriesCount,
            'internal_transfer_count' => $internalTransferCount,
            'internal_transfer_volume' => $internalTransferVolume,
            'unclassified_count' => count(array_filter($movements, fn ($row) => $row['category'] === 'unclassified')),
        ];
    }

    private function movementRow($entry, $line, string $category, float $amount, ?string $fallbackDescription = null): array
    {
        return [
            'journal_entry_id' => (int) $entry->id,
            'entry_number' => (string) $entry->entry_number,
            'date' => $entry->entry_date?->format('d/m/Y') ?? '',
            'date_iso' => $entry->entry_date?->format('Y-m-d') ?? '',
            'reference' => (string) ($entry->reference ?? ''),
            'description' => (string) ($line?->description ?: $entry->description ?: $fallbackDescription ?: ''),
            'account_id' => $line?->account?->id ? (int) $line->account->id : null,
            'account_code' => (string) ($line?->account?->code ?? ''),
            'account_name' => (string) ($line?->account?->name ?? 'غير مصنف'),
            'category' => $category,
            'amount' => round($amount, 2),
            'direction' => $amount >= 0 ? 'in' : 'out',
        ];
    }

    private function groupMovements(array $movements): array
    {
        $grouped = [
            'operating' => [],
            'investing' => [],
            'financing' => [],
            'unclassified' => [],
        ];

        foreach ($movements as $row) {
            $category = $row['category'];
            $key = ($row['account_code'] ?: 'UNCLASSIFIED') . '|' . $row['account_name'];

            if (!isset($grouped[$category][$key])) {
                $grouped[$category][$key] = [
                    'account_id' => $row['account_id'],
                    'code' => $row['account_code'],
                    'name' => $row['account_name'],
                    'amount' => 0.0,
                    'count' => 0,
                ];
            }

            $grouped[$category][$key]['amount'] += $row['amount'];
            $grouped[$category][$key]['count']++;
        }

        foreach ($grouped as $category => $rows) {
            $rows = array_values($rows);
            usort($rows, fn ($a, $b) => strnatcasecmp((string) $a['code'], (string) $b['code']));
            $grouped[$category] = $rows;
        }

        return $grouped;
    }

    private function categoryForCode(string $code): string
    {
        if (isset($this->categories[$code])) {
            return (string) $this->categories[$code];
        }

        // Longest prefix first.
        $fallbacks = $this->prefixFallbacks;
        uksort($fallbacks, fn ($a, $b) => strlen((string) $b) <=> strlen((string) $a));

        foreach ($fallbacks as $prefix => $category) {
            if (str_starts_with($code, (string) $prefix)) {
                return (string) $category;
            }
        }

        return 'none';
    }

    private function isCashCode(string $code): bool
    {
        return in_array($code, $this->cashCodes, true);
    }

    private function cashBalanceBefore(string $date): float
    {
        return $this->cashBalanceByDate($date, true);
    }

    private function cashBalanceAt(string $date): float
    {
        return $this->cashBalanceByDate($date, false);
    }

    private function cashBalanceByDate(string $date, bool $strictlyBefore): float
    {
        $accounts = Account::query()
            ->whereIn('code', $this->cashCodes)
            ->orderBy('code')
            ->get();

        $total = 0.0;

        foreach ($accounts as $account) {
            $opening = $account->balance_type === 'credit'
                ? -1 * (float) $account->opening_balance
                : (float) $account->opening_balance;

            $query = JournalEntryLine::query()
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($date, $strictlyBefore) {
                    $q->where('status', 'posted');
                    if ($strictlyBefore) {
                        $q->where('entry_date', '<', $date);
                    } else {
                        $q->where('entry_date', '<=', $date);
                    }
                });

            $debit = (float) (clone $query)->sum('debit');
            $credit = (float) (clone $query)->sum('credit');
            $total += $opening + $debit - $credit;
        }

        return round($total, 2);
    }

    private function cashPositionsAt(string $date): array
    {
        $accounts = Account::query()
            ->whereIn('code', $this->cashCodes)
            ->orderBy('code')
            ->get();

        $rows = [];

        foreach ($accounts as $account) {
            $opening = $account->balance_type === 'credit'
                ? -1 * (float) $account->opening_balance
                : (float) $account->opening_balance;

            $query = JournalEntryLine::query()
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $date));

            $debit = (float) (clone $query)->sum('debit');
            $credit = (float) (clone $query)->sum('credit');
            $balance = $opening + $debit - $credit;

            $rows[] = [
                'account_id' => (int) $account->id,
                'code' => (string) $account->code,
                'name' => (string) $account->name,
                'balance' => round($balance, 2),
            ];
        }

        return $rows;
    }

    private function legacyCashWarning(string $dateTo): array
    {
        $legacy = Account::query()
            ->whereIn('code', $this->legacyCashCodes)
            ->get();

        if ($legacy->isEmpty()) {
            return ['exists' => false, 'lines_count' => 0, 'balance' => 0.0, 'codes' => []];
        }

        $ids = $legacy->pluck('id');

        $query = JournalEntryLine::query()
            ->whereIn('account_id', $ids)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $dateTo));

        $debit = (float) (clone $query)->sum('debit');
        $credit = (float) (clone $query)->sum('credit');

        return [
            'exists' => true,
            'lines_count' => (clone $query)->count(),
            'balance' => round($debit - $credit, 2),
            'codes' => $legacy->pluck('code')->map(fn ($v) => (string) $v)->values()->all(),
        ];
    }

    private function makeWaterfall(array $values): array
    {
        $steps = [];
        $running = 0.0;

        foreach (['operating', 'investing', 'financing'] as $key) {
            $value = (float) ($values[$key] ?? 0);
            $start = $running;
            $running += $value;
            $steps[] = ['key' => $key, 'value' => $value, 'start' => $start, 'end' => $running];
        }

        $steps[] = ['key' => 'net', 'value' => (float) ($values['net'] ?? $running), 'start' => 0.0, 'end' => (float) ($values['net'] ?? $running)];

        $points = [0.0];
        foreach ($steps as $step) {
            $points[] = $step['start'];
            $points[] = $step['end'];
        }

        $min = min($points);
        $max = max($points);
        if (abs($max - $min) < $this->tolerance) {
            $max = 1;
            $min = -1;
        }

        $range = $max - $min;
        $zeroPct = (($max - 0.0) / $range) * 100;

        foreach ($steps as &$step) {
            $upper = max($step['start'], $step['end']);
            $lower = min($step['start'], $step['end']);
            $step['top_pct'] = (($max - $upper) / $range) * 100;
            $step['height_pct'] = max((($upper - $lower) / $range) * 100, 1.2);
        }
        unset($step);

        return [
            'steps' => $steps,
            'zero_pct' => $zeroPct,
            'min' => $min,
            'max' => $max,
        ];
    }

    private function emptyAnalysis(): array
    {
        return [
            'totals' => ['operating' => 0.0, 'investing' => 0.0, 'financing' => 0.0, 'unclassified' => 0.0],
            'movements' => [],
            'grouped' => ['operating' => [], 'investing' => [], 'financing' => [], 'unclassified' => []],
            'cash_entries_count' => 0,
            'internal_transfer_count' => 0,
            'internal_transfer_volume' => 0.0,
            'unclassified_count' => 0,
        ];
    }
}
