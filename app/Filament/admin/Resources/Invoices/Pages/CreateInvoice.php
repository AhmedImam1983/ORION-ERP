<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Models\Product;
use App\Services\InvoicePostingService;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected string $view = 'filament.admin.resources.invoices.pages.create-invoice';

    public function getSubheading(): ?string
    {
        return 'أنشئ فاتورة بيع أو شراء أو مرتجع مع الأصناف والضريبة وطريقة السداد من شاشة واحدة';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الفاتورة بنجاح';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $type = $data['type'] ?? 'sale';

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

        // paid_amount لا يُدخل يدوياً بعد تفعيل السندات المالية.
        $data['paid_amount'] = 0;

        // partial / paid حالات مالية مشتقة من السندات، وليست اختياراً عند الإنشاء.
        if (in_array($data['status'] ?? 'draft', ['partial', 'paid'], true)) {
            $data['status'] = 'confirmed';
        }

        $items = $data['items'] ?? [];

        $invoiceSubtotal = 0;
        $invoiceTax      = 0;
        $invoiceDiscount = 0;
        $cleanItems      = [];

        foreach ($items as $item) {

            if (empty($item['product_id'])) {
                continue;
            }

            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            $item['product_name'] = $product->name;
            $item['product_code'] = $product->code;
            $item['unit']         = $product->unit;

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

            $qty             = (float) ($item['quantity'] ?? 1);
            $price           = (float) ($item['unit_price'] ?? 0);
            $discountPercent = (float) ($item['discount_percent'] ?? 0);
            $taxPercent      = (float) ($item['tax_percent'] ?? 0);

            $lineSubtotal   = $qty * $price;
            $lineDiscount   = $lineSubtotal * ($discountPercent / 100);
            $afterDiscount  = $lineSubtotal - $lineDiscount;
            $lineTax        = $afterDiscount * ($taxPercent / 100);
            $lineTotal      = $afterDiscount + $lineTax;

            $item['discount_amount'] = round($lineDiscount, 2);
            $item['tax_amount']      = round($lineTax, 2);
            $item['total']           = round($lineTotal, 2);

            $item['vat_type'] = $item['vat_type'] ?? ($product->vat_type ?? 'S');

            $invoiceSubtotal += $lineSubtotal;
            $invoiceDiscount += $lineDiscount;
            $invoiceTax      += $lineTax;

            $cleanItems[] = $item;
        }

        $data['items'] = $cleanItems;

        $invoiceDiscountPercent = (float) ($data['discount_percent'] ?? 0);
        $invoiceLevelDiscount   = $invoiceSubtotal * ($invoiceDiscountPercent / 100);
        $totalDiscount          = $invoiceDiscount + $invoiceLevelDiscount;
        $totalTax               = $invoiceTax;
        $total                  = $invoiceSubtotal - $totalDiscount + $totalTax;
        $paidAmount             = 0.0;

        $data['subtotal']          = round($invoiceSubtotal, 2);
        $data['discount_amount']   = round($totalDiscount, 2);
        $data['tax_amount']        = round($totalTax, 2);
        $data['total']             = round($total, 2);
        $data['remaining_amount']  = round(max($total - $paidAmount, 0), 2);
        $data['created_by']        = auth()->id();

        return $data;
    }

    /**
     * بعد حفظ الـ Invoice والـ Items — نعيد الحساب ثم نرحّل الفاتورة من خلال
     * InvoicePostingService فقط (GL + Inventory atomically).
     */
    protected function afterCreate(): void
    {
        $invoice = $this->getRecord();

        // ── إعادة حساب الإجمالي من الـ items الفعلية بعد حفظها ──────────
        $items        = $invoice->items()->get();
        $subtotal     = 0;
        $itemDiscount = 0;
        $taxAmount    = 0;

        foreach ($items as $item) {
            $lineSubtotal    = (float) $item->quantity * (float) $item->unit_price;
            $discountPercent = (float) ($item->discount_percent ?? 0);
            $discount        = $lineSubtotal * ($discountPercent / 100);
            $afterDiscount   = $lineSubtotal - $discount;
            $taxPercent      = (float) ($item->tax_percent ?? 0);
            $tax             = $afterDiscount * ($taxPercent / 100);
            $lineTotal       = $afterDiscount + $tax;

            $item->update([
                'discount_amount' => round($discount, 2),
                'tax_amount'      => round($tax, 2),
                'total'           => round($lineTotal, 2),
            ]);

            $subtotal     += $lineSubtotal;
            $itemDiscount += $discount;
            $taxAmount    += $tax;
        }

        $invoiceDiscountPercent = (float) ($invoice->discount_percent ?? 0);
        $invoiceLevelDiscount   = $subtotal * ($invoiceDiscountPercent / 100);
        $totalDiscount          = $itemDiscount + $invoiceLevelDiscount;
        $total                  = $subtotal - $totalDiscount + $taxAmount;
        $paidAmount             = (float) ($invoice->paid_amount ?? 0);
        $remaining              = max($total - $paidAmount, 0);

        $invoice->update([
            'subtotal'         => round($subtotal, 2),
            'discount_amount'  => round($totalDiscount, 2),
            'tax_amount'       => round($taxAmount, 2),
            'total'            => round($total, 2),
            'remaining_amount' => round($remaining, 2),
        ]);

        // المسودة/الملغاة لا تُرحّل. partial تُعامل كفاتورة مُثبتة تجارياً،
        // أما التحصيل الفعلي فسيتم لاحقاً عن طريق سند القبض.
        if (!in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
            return;
        }

        $invoice->refresh();

        try {
            app(InvoicePostingService::class)->post($invoice);
        } catch (\Throwable $e) {
            /*
             * InvoicePostingService rolls GL + stock back together.
             * Keep the saved document as Draft so we never leave a
             * "confirmed but unposted" invoice after a stock failure.
             */
            $invoice->updateQuietly([
                'status' => 'draft',
            ]);

            Log::error('CreateInvoice posting Error: ' . $e->getMessage(), [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            Notification::make()
                ->title('تعذر ترحيل الفاتورة — حُفظت كمسودة')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}