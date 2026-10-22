<?php

namespace App\Filament\Admin\Resources\Employees\Pages;

use App\Filament\Admin\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected string $view = 'filament.admin.resources.employees.pages.list-employees';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getEmployeeStats(): array
    {
        $query = Employee::query();

        $basicPayroll = (float) (clone $query)->sum('basic_salary');
        $allowances = (float) (clone $query)->sum('allowances');

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'payroll' => $basicPayroll + $allowances,
            'departments' => (clone $query)
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->count('department'),
        ];
    }
}
