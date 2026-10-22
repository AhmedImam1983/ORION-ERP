<?php

namespace App\Filament\Admin\Resources\Salaries\Pages;

use App\Filament\Admin\Resources\Salaries\SalaryResource;
use App\Models\Salary;
use Filament\Resources\Pages\ListRecords;

class ListSalaries extends ListRecords
{
    protected static string $resource = SalaryResource::class;

    protected string $view = 'filament.admin.resources.salaries.pages.list-salaries';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getSalaryStats(): array
    {
        $query = Salary::query()
            ->where('month', now()->month)
            ->where('year', now()->year);

        return [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'net' => (float) (clone $query)->sum('net_salary'),
        ];
    }

    public function getCurrentMonthName(): string
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ][now()->month] ?? '';
    }
}
