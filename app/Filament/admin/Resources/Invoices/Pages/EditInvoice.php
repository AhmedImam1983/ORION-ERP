<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Services\InvoicePostingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected string $view = 'filament.admin.resources.invoices.pages.edit-invoice';

    public function getSubheading(): ?string
    {
        return 'راجع الفاتورة والتحصيل والحالة والضريبة مع الحفاظ على منطق الترحيل الحالي';
    }

    /*
    |--------------------------------------------------------------------------
    | إعادة حساب الفاتورة قبل الحفظ
    |--------------------------------------------------------------------------
    */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $invoice = $this->getRecord();
        $type = $data['type'] ?? $invoice->type;

        if (
            in_array($type, ['sale', 'sale_return'], true) &&
            empty($data['customer_id'])
        ) {
            throw ValidationException::withMessages([
                'customer_id' => 'يجب اختيار العميل لفاتورة البيع أو مرتجع البيع.',
            ]);
        }

        if (
            in_array($type, ['purchase', 'purchase_return'], true) &&
            empty($data['supplier_id'])
        ) {
            throw ValidationException::withMessages([
                'supplier_id' => 'يجب اختيار المورد لفاتورة الشراء أو مرتجع الشراء.',
            ]);
        }

        // المبلغ المدفوع لا يُعدل من شاشة الفاتورة؛ مصدره السندات المالية.
        $data['paid_amount'] = (float) $invoice->paid_amount;

        // لو الفاتورة لم تُرحل بعد، لا نسمح بإدخال partial / paid يدوياً.
        if ($invoice->status === 'draft') {
            if (in_array($data['status'] ?? 'draft', ['partial', 'paid'], true)) {
                $data['status'] = 'confirmed';
            }
        } else {
            // بعد الترحيل، الحالة المالية لا تُغيّر من شاشة تعديل الفاتورة.
            // سندات القبض/الصرف أو Reversal هي المسؤولة عنها.
            $data['status'] = $invoice->status;
        }

        $items = $data['items'] ?? [];

        $invoiceSubtotal = 0;
        $invoiceTax = 0;
        $invoiceDiscount = 0;

        $cleanItems = [];

        foreach ($items as $item) {

            /*
             * تجاهل أي سطر فارغ في الـRepeater
             */
            if (empty($item['product_id'])) {
                continue;
            }

            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            /*
             * بيانات المنتج
             */
            $item['product_name'] = $product->name;
            $item['product_code'] = $product->code;
            $item['unit'] = $product->unit;

            /*
             * السعر الافتراضي حسب نوع الفاتورة
             */
            if (
                !isset($item['unit_price']) ||
                $item['unit_price'] === '' ||
                $item['unit_price'] === null
            ) {
                $item['unit_price'] = in_array(
                    $data['type'] ?? 'sale',
                    ['purchase', 'purchase_return'],
                    true
                )
                    ? ($product->cost_price ?? 0)
                    : ($product->sale_price ?? 0);
            }

            $item['cost_price'] = $product->cost_price ?? 0;

            /*
             * VAT snapshot
             */
            $item['vat_type'] =
                $item['vat_type']
                ?? ($product->vat_type ?? 'S');

            $item['vat_exemption_reason_code'] =
                $item['vat_exemption_reason_code']
                ?? $product->vat_exemption_reason_code;

            $item['vat_exemption_reason'] =
                $item['vat_exemption_reason']
                ?? $product->vat_exemption_reason;

            /*
             * القيم الحسابية
             */
            $qty = (float) ($item['quantity'] ?? 1);

            $price = (float) ($item['unit_price'] ?? 0);

            $discountPercent =
                (float) ($item['discount_percent'] ?? 0);

            $taxPercent =
                (float) ($item['tax_percent'] ?? 0);

            /*
             * إجمالي الصنف قبل الضريبة
             */
            $lineSubtotal = $qty * $price;

            /*
             * خصم الصنف
             */
            $lineDiscount =
                $lineSubtotal *
                ($discountPercent / 100);

            /*
             * بعد الخصم
             */
            $afterDiscount =
                $lineSubtotal - $lineDiscount;

            /*
             * VAT
             */
            $lineTax =
                $afterDiscount *
                ($taxPercent / 100);

            /*
             * الإجمالي النهائي للصنف
             */
            $lineTotal =
                $afterDiscount + $lineTax;

            /*
             * حفظ قيم الصنف
             */
            $item['discount_amount'] =
                round($lineDiscount, 2);

            $item['tax_amount'] =
                round($lineTax, 2);

            $item['total'] =
                round($lineTotal, 2);

            /*
             * تجميع إجماليات الفاتورة
             */
            $invoiceSubtotal += $lineSubtotal;
            $invoiceDiscount += $lineDiscount;
            $invoiceTax += $lineTax;

            $cleanItems[] = $item;
        }

        /*
         * استبعاد الصفوف الفارغة
         */
        $data['items'] = $cleanItems;

        /*
         * الخصم العام على الفاتورة
         */
        $invoiceDiscountPercent =
            (float) ($data['discount_percent'] ?? 0);

        $invoiceLevelDiscount =
            $invoiceSubtotal *
            ($invoiceDiscountPercent / 100);

        $totalDiscount =
            $invoiceDiscount +
            $invoiceLevelDiscount;

        /*
         * إجمالي الضريبة
         */
        $totalTax = $invoiceTax;

        /*
         * الإجمالي النهائي
         */
        $total =
            $invoiceSubtotal -
            $totalDiscount +
            $totalTax;

        /*
         * تحديث رأس الفاتورة
         */
        $data['subtotal'] =
            round($invoiceSubtotal, 2);

        $data['discount_amount'] =
            round($totalDiscount, 2);

        $data['tax_amount'] =
            round($totalTax, 2);

        $data['total'] =
            round($total, 2);

        /*
         * المدفوع والمتبقي
         */
        $paidAmount =
            (float) $invoice->paid_amount;

        $data['remaining_amount'] =
            round(
                max($total - $paidAmount, 0),
                2
            );

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Header Actions
    |--------------------------------------------------------------------------
    */
    protected function getHeaderActions(): array
    {
        return [

            ViewAction::make(),

            /*
            |--------------------------------------------------------------------------
            | إنشاء جدول أقساط
            |--------------------------------------------------------------------------
            */
            Action::make('generate_installments')
                ->label('إنشاء جدول أقساط')
                ->color('warning')
                ->visible(
                    fn () =>
                        $this->getRecord()->payment_type === 'installment'
                )
                ->form([
                    \Filament\Forms\Components\TextInput::make('count')
                        ->label('عدد الأقساط')
                        ->numeric()
                        ->required()
                        ->default(6)
                        ->minValue(2)
                        ->maxValue(60),

                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label('تاريخ أول قسط')
                        ->required()
                        ->default(
                            today()->addMonth()
                        ),

                    \Filament\Forms\Components\Select::make('frequency')
                        ->label('التكرار')
                        ->options([
                            'weekly' =>
                                'أسبوعي',

                            'biweekly' =>
                                'كل أسبوعين',

                            'monthly' =>
                                'شهري',

                            'quarterly' =>
                                'كل 3 شهور',
                        ])
                        ->default('monthly')
                        ->required(),
                ])
                ->action(function (array $data) {

                    \App\Models\Installment::generateSchedule(
                        $this->getRecord(),
                        $data['count'],
                        $data['start_date'],
                        $data['frequency']
                    );

                    Notification::make()
                        ->title(
                            'تم إنشاء ' .
                            $data['count'] .
                            ' قسط'
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        request()->header('Referer')
                    );
                }),

            /*
            |--------------------------------------------------------------------------
            | نسخ الفاتورة
            |--------------------------------------------------------------------------
            */
            Action::make('duplicate')
                ->label('نسخ الفاتورة')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('نسخ الفاتورة')
                ->modalDescription(
                    'هيتعمل فاتورة جديدة بنفس الأصناف بحالة مسودة — هتقدر تعدل فيها.'
                )
                ->modalSubmitActionLabel('نسخ')
                ->action(function () {

                    $original = $this->getRecord();

                    $new = Invoice::create([
                        'invoice_number' =>
                            Invoice::generateNumber(
                                $original->type
                            ),

                        'type' =>
                            $original->type,

                        'payment_type' =>
                            $original->payment_type,

                        'customer_id' =>
                            $original->customer_id,

                        'supplier_id' =>
                            $original->supplier_id,

                        'branch_id' =>
                            $original->branch_id,

                        'warehouse_id' =>
                            $original->warehouse_id,

                        'invoice_date' =>
                            today(),

                        'due_date' =>
                            $original->due_date,

                        'subtotal' =>
                            $original->subtotal,

                        'discount_amount' =>
                            $original->discount_amount,

                        'discount_percent' =>
                            $original->discount_percent,

                        'tax_amount' =>
                            $original->tax_amount,

                        'tax_percent' =>
                            $original->tax_percent,

                        'total' =>
                            $original->total,

                        'paid_amount' =>
                            0,

                        'remaining_amount' =>
                            $original->total,

                        'status' =>
                            'draft',

                        'notes' =>
                            'نسخة من فاتورة ' .
                            $original->invoice_number,

                        'created_by' =>
                            auth()->id(),
                    ]);

                    foreach ($original->items as $item) {

                        InvoiceItem::create([
                            'invoice_id' =>
                                $new->id,

                            'product_id' =>
                                $item->product_id,

                            'product_name' =>
                                $item->product_name,

                            'product_code' =>
                                $item->product_code,

                            'unit' =>
                                $item->unit,

                            'quantity' =>
                                $item->quantity,

                            'unit_price' =>
                                $item->unit_price,

                            'cost_price' =>
                                $item->cost_price,

                            'discount_percent' =>
                                $item->discount_percent,

                            'discount_amount' =>
                                $item->discount_amount,

                            'tax_percent' =>
                                $item->tax_percent,

                            'tax_amount' =>
                                $item->tax_amount,

                            'vat_type' =>
                                $item->vat_type,

                            'vat_exemption_reason_code' =>
                                $item->vat_exemption_reason_code,

                            'vat_exemption_reason' =>
                                $item->vat_exemption_reason,

                            'total' =>
                                $item->total,

                            'notes' =>
                                $item->notes,
                        ]);
                    }

                    Notification::make()
                        ->title('✅ تم نسخ الفاتورة')
                        ->body(
                            'رقم الفاتورة الجديدة: ' .
                            $new->invoice_number
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        InvoiceResource::getUrl(
                            'edit',
                            [
                                'record' => $new,
                            ]
                        )
                    );
                }),

            /*
            |--------------------------------------------------------------------------
            | حذف
            |--------------------------------------------------------------------------
            */
            DeleteAction::make(),

            ForceDeleteAction::make(),

            RestoreAction::make(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | بعد حفظ الفاتورة
    |--------------------------------------------------------------------------
    */
   protected function afterSave(): void
{
    $invoice = $this->getRecord();

    /*
    |--------------------------------------------------------------------------
    | إعادة حساب إجماليات الفاتورة بعد حفظ الـ InvoiceItems
    |--------------------------------------------------------------------------
    */

    $items = $invoice->items()->get();

    $subtotal = 0;
    $itemDiscount = 0;
    $taxAmount = 0;

    foreach ($items as $item) {

        $quantity = (float) $item->quantity;
        $unitPrice = (float) $item->unit_price;

        $lineSubtotal = $quantity * $unitPrice;

        $discountPercent =
            (float) ($item->discount_percent ?? 0);

        $discount =
            $lineSubtotal *
            ($discountPercent / 100);

        $afterDiscount =
            $lineSubtotal - $discount;

        $taxPercent =
            (float) ($item->tax_percent ?? 0);

        $tax =
            $afterDiscount *
            ($taxPercent / 100);

        $lineTotal =
            $afterDiscount + $tax;

        /*
         * تحديث بيانات الصنف نفسها
         */
        $item->update([
            'discount_amount' => round($discount, 2),
            'tax_amount'      => round($tax, 2),
            'total'           => round($lineTotal, 2),
        ]);

        $subtotal += $lineSubtotal;
        $itemDiscount += $discount;
        $taxAmount += $tax;
    }

    /*
    |--------------------------------------------------------------------------
    | خصم الفاتورة العام
    |--------------------------------------------------------------------------
    */

    $invoiceDiscountPercent =
        (float) ($invoice->discount_percent ?? 0);

    $invoiceLevelDiscount =
        $subtotal *
        ($invoiceDiscountPercent / 100);

    $totalDiscount =
        $itemDiscount +
        $invoiceLevelDiscount;

    /*
    |--------------------------------------------------------------------------
    | الإجمالي النهائي
    |--------------------------------------------------------------------------
    */

    $total =
        $subtotal -
        $totalDiscount +
        $taxAmount;

    $paidAmount =
        (float) ($invoice->paid_amount ?? 0);

    $remaining =
        max($total - $paidAmount, 0);

    /*
    |--------------------------------------------------------------------------
    | تحديث رأس الفاتورة
    |--------------------------------------------------------------------------
    */

    $invoice->update([
        'subtotal'         => round($subtotal, 2),
        'discount_amount'  => round($totalDiscount, 2),
        'tax_amount'       => round($taxAmount, 2),
        'total'            => round($total, 2),
        'remaining_amount' => round($remaining, 2),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Unified posting AFTER InvoiceItems are saved
    |--------------------------------------------------------------------------
    |
    | Draft -> confirmed is intentionally posted here, not from Observer::updated().
    | InvoicePostingService is idempotent, so already-posted invoices are not
    | duplicated and historical stock is never backfilled.
    */

    if (
        in_array(
            $invoice->status,
            ['confirmed', 'partial', 'paid'],
            true
        )
    ) {
        try {
            app(InvoicePostingService::class)->post($invoice);
        } catch (\Throwable $e) {
            $invoice->updateQuietly([
                'status' => 'draft',
            ]);

            Log::error('EditInvoice posting Error: ' . $e->getMessage(), [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            Notification::make()
                ->title('تعذر ترحيل الفاتورة — عادت لمسودة')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }
    } else {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    try {

        $admin =
            \App\Models\User::role('super_admin')->first();

        if (!$admin) {
            return;
        }

        $typeMap = [
            'sale' =>
                'بيع',

            'purchase' =>
                'شراء',

            'sale_return' =>
                'مرتجع بيع',

            'purchase_return' =>
                'مرتجع شراء',
        ];

        $type =
            $typeMap[$invoice->type]
            ?? $invoice->type;

        DB::table('notifications')->insert([
            'id' =>
                Str::uuid()->toString(),

            'type' =>
                \Filament\Notifications\DatabaseNotification::class,

            'notifiable_type' =>
                \App\Models\User::class,

            'notifiable_id' =>
                $admin->id,

            'data' =>
                json_encode([
                    'title' =>
                        '🧾 فاتورة ' .
                        $type .
                        ' — ' .
                        $invoice->invoice_number,

                    'body' =>
                        (
                            $invoice->customer?->name
                            ??
                            $invoice->supplier?->name
                            ??
                            'عميل نقدي'
                        )
                        .
                        ' · '
                        .
                        number_format(
                            $invoice->total,
                            2
                        )
                        .
                        ' ر.س',

                    'color' =>
                        'success',

                    'icon' =>
                        'heroicon-o-document-text',

                    'iconColor' =>
                        'success',

                    'format' =>
                        'filament',

                    'actions' =>
                        [],
                ]),

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

    } catch (\Exception $e) {

        Log::error(
            'Notification Error: ' .
            $e->getMessage()
        );
    }
}
}