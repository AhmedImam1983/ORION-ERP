<?php

namespace App\Filament\Admin\Resources\StockMovements\Pages;

use App\Filament\Admin\Resources\StockMovements\StockMovementResource;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected string $view = 'filament.admin.resources.stock-movements.pages.create-stock-movement';

    public function getHeading(): string
    {
        return 'تسوية مخزون يدوية';
    }

    public function getSubheading(): ?string
    {
        return 'استخدمها فقط للتسويات أو الرصيد الافتتاحي مع تسجيل مرجع واضح';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if (!$user) {
            throw new AuthorizationException('يجب تسجيل الدخول قبل تسجيل حركة مخزون.');
        }

        if (!in_array($data['type'] ?? null, ['adjustment_in', 'adjustment_out', 'opening'], true)) {
            throw ValidationException::withMessages([
                'data.type' => 'نوع الحركة غير مسموح به في التسوية اليدوية.',
            ]);
        }

        $warehouseId = (int) ($data['warehouse_id'] ?? 0);
        $warehouse = Warehouse::query()
            ->whereKey($warehouseId)
            ->where('is_active', true)
            ->first();
        if (!$warehouse) {
            throw ValidationException::withMessages([
                'data.warehouse_id' => 'اختر مخزنًا نشطًا وصالحًا.',
            ]);
        }

        $quantity = (float) ($data['quantity'] ?? 0);
        $unitPrice = (float) ($data['unit_price'] ?? 0);
        if ($quantity <= 0 || $unitPrice < 0) {
            throw ValidationException::withMessages([
                'data.quantity' => 'الكمية يجب أن تكون موجبة، وسعر الوحدة لا يمكن أن يكون سالبًا.',
            ]);
        }

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $mappedBranch = Branch::query()->where('warehouse_id', $warehouseId)->first();
            if (!$mappedBranch) {
                $mainBranch = Branch::query()->where('is_main', true)->first();
                $defaultWarehouseId = Warehouse::query()
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->value('id');

                if ($mainBranch && !$mainBranch->warehouse_id && (int) $defaultWarehouseId === $warehouseId) {
                    $mappedBranch = $mainBranch;
                }
            }
            $data['branch_id'] = $mappedBranch?->id;
        } else {
            $branchId = (int) ($user->branch_id ?? 0);
            $branch = $branchId > 0
                ? Branch::query()->whereKey($branchId)->where('is_active', true)->first()
                : null;

            if (!$branch) {
                throw new AuthorizationException('حسابك غير مرتبط بفرع نشط.');
            }

            $allowedWarehouseId = $branch->warehouse_id;
            if (!$allowedWarehouseId && $branch->is_main) {
                $allowedWarehouseId = Warehouse::query()
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->value('id');
            }

            if (!$allowedWarehouseId) {
                throw ValidationException::withMessages([
                    'data.warehouse_id' => 'اربط الفرع بمخزن افتراضي من إعدادات الفروع أولًا.',
                ]);
            }

            if ((int) $allowedWarehouseId !== $warehouseId) {
                throw new AuthorizationException('لا يمكنك تسجيل حركة على مخزن فرع آخر.');
            }

            $data['branch_id'] = $branch->id;
        }

        $data['created_by'] = $user->getKey();
        $data['total'] = round($quantity * $unitPrice, 2);

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم تسجيل حركة المخزون';
    }
}
