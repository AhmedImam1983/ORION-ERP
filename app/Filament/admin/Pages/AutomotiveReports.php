<?php

namespace App\Filament\Admin\Pages;

use Illuminate\Support\Facades\DB;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class AutomotiveReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'تقارير قطع الغيار';
    protected static ?string $title = 'تقارير قطع الغيار';
    protected static ?string $slug = 'automotive-reports';
    protected static ?int $navigationSort = 20;
    protected string $view = 'filament.admin.pages.automotive-reports';

    public ?array $data = [];
    public string $from;
    public string $to;
    public ?int $brandId = null;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
        $this->form->fill([
            'from' => $this->from,
            'to' => $this->to,
            'brand_id' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('from')->label('من تاريخ')->native(false)->required(),
            DatePicker::make('to')->label('إلى تاريخ')->native(false)->required(),
            Select::make('brand_id')->label('العلامة التجارية')->options(fn () => DB::table('brands')->whereNull('deleted_at')->where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->preload()->nullable(),
        ])->columns(3)->statePath('data');
    }

    public function refreshReports(): void
    {
        $this->from = $this->data['from'] ?? $this->from;
        $this->to = $this->data['to'] ?? $this->to;
        $this->brandId = isset($this->data['brand_id']) && $this->data['brand_id'] !== '' ? (int) $this->data['brand_id'] : null;
    }

    public function getSummary(): array
    {
        $query = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('products as p', 'p.id', '=', 'ii.product_id')
            ->where('i.type', 'sale')
            ->whereBetween('i.invoice_date', [$this->from, $this->to]);

        if ($this->brandId) {
            $query->where('p.brand_id', $this->brandId);
        }

        $sales = (clone $query)->sum('ii.total');
        $qty = (clone $query)->sum('ii.quantity');
        $invoices = (clone $query)->distinct('i.id')->count('i.id');

        $stock = DB::table('warehouse_stocks as ws')
            ->join('products as p', 'p.id', '=', 'ws.product_id')
            ->when($this->brandId, fn ($q) => $q->where('p.brand_id', $this->brandId))
            ->sum('ws.quantity');

        return [
            'sales' => (float) $sales,
            'qty' => (float) $qty,
            'invoices' => (int) $invoices,
            'stock' => (float) $stock,
        ];
    }

    public function getTopProducts(int $limit = 10)
    {
        return DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('products as p', 'p.id', '=', 'ii.product_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->where('i.type', 'sale')
            ->whereBetween('i.invoice_date', [$this->from, $this->to])
            ->when($this->brandId, fn ($q) => $q->where('p.brand_id', $this->brandId))
            ->groupBy('p.id', 'p.code', 'p.name', 'b.name')
            ->select('p.code', 'p.name', 'b.name as brand_name', DB::raw('SUM(ii.quantity) as quantity'), DB::raw('SUM(ii.total) as total'))
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get();
    }

    public function getBrandStock()
    {
        return DB::table('warehouse_stocks as ws')
            ->join('products as p', 'p.id', '=', 'ws.product_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->when($this->brandId, fn ($q) => $q->where('p.brand_id', $this->brandId))
            ->groupBy('b.id', 'b.name')
            ->select(DB::raw("COALESCE(b.name, 'بدون علامة') as brand_name"), DB::raw('COUNT(DISTINCT p.id) as products'), DB::raw('SUM(ws.quantity) as quantity'))
            ->orderByDesc('quantity')
            ->limit(12)
            ->get();
    }

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }
}
