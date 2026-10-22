<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Services\ZatcaService;
use App\Services\SupplierRefundPostingService;
use App\Models\Treasury;
use App\Models\BankAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected string $view = 'filament.admin.resources.invoices.pages.view-invoice';

    public function getSubheading(): ?string
    {
        if ($this->record?->type === 'purchase_return') {
            return 'تفاصيل مرتجع الشراء والأصناف والضريبة وأثر التسوية على ذمة المورد';
        }

        if ($this->record?->type === 'sale_return') {
            return 'تفاصيل مرتجع البيع والأصناف والضريبة وأثر التسوية';
        }

        return 'ملخص الفاتورة والأصناف والتحصيل والضريبة وحالة زاتكا في شاشة واحدة';
    }

    protected function getHeaderActions(): array
    {
        return [
            // طباعة
            Action::make('print')
                ->label('طباعة')
                ->color('gray')
                ->url(fn() => route('invoice.print', $this->record->id))
                ->openUrlInNewTab(),

            // واتساب
            Action::make('whatsapp')
                ->label('واتساب')
                ->color('success')
                ->url(fn() => $this->getWhatsappUrl())
                ->openUrlInNewTab()
                ->visible(fn() => !empty($this->record->customer?->phone)),

            // ══ إرسال إلى زاتكا ══════════════════════════════════
            Action::make('zatca_submit')
                ->label(fn() => $this->record->zatca_sent ? 'إعادة إرسال لزاتكا' : 'إرسال إلى زاتكا')
                ->color(fn() => $this->record->zatca_sent ? 'info' : 'primary')
                ->visible(fn() => in_array($this->record->type, ['sale']) && $this->record->status !== 'draft')
                ->requiresConfirmation()
                ->modalHeading('إرسال الفاتورة إلى زاتكا')
                ->modalDescription('سيتم إرسال الفاتورة إلى منظومة زاتكا السعودية. تأكد من صحة البيانات الضريبية.')
                ->modalSubmitActionLabel('إرسال')
                ->action(function () {
                    try {
                        $result = app(ZatcaService::class)->submitInvoice($this->record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('تم إرسال الفاتورة إلى زاتكا')
                                ->body('UUID: ' . $result['uuid'] . ' | بيئة: ' . $result['environment'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل إرسال الفاتورة إلى زاتكا')
                                ->body($result['error'])
                                ->danger()
                                ->send();
                        }

                        $this->refreshFormData([
                            'zatca_sent', 'zatca_uuid', 'zatca_status_code',
                            'zatca_environment', 'zatca_error',
                        ]);

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // استرداد مبلغ من المورد ضد رصيد مرتجع شراء مدين
            Action::make('supplier_refund')
                ->label('استرداد من المورد')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(function () {
                    if ($this->record->type !== 'purchase_return') {
                        return false;
                    }

                    return app(SupplierRefundPostingService::class)
                        ->availableForReturn($this->record) > 0.009;
                })
                ->modalHeading('استرداد مبلغ من المورد')
                ->modalDescription(function () {
                    $available = app(SupplierRefundPostingService::class)
                        ->availableForReturn($this->record);

                    return 'الرصيد المتاح للاسترداد حاليًا: ' .
                        number_format($available, 2) .
                        ' ر.س. سيتم إنشاء سند قبض وقيد محاسبي وحركة خزينة/بنك مترابطة.';
                })
                ->modalSubmitActionLabel('تأكيد الاسترداد')
                ->form([
                    Select::make('destination_type')
                        ->label('وجهة الاسترداد')
                        ->options([
                            'treasury' => 'خزينة',
                            'bank'     => 'بنك',
                        ])
                        ->required(),

                    Select::make('treasury_id')
                        ->label('الخزينة المستلمة — عند اختيار خزينة')
                        ->options(fn () => Treasury::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable(),

                    Select::make('bank_account_id')
                        ->label('الحساب البنكي المستلم — عند اختيار بنك')
                        ->options(fn () => BankAccount::query()
                            ->where('is_active', true)
                            ->orderBy('bank_name')
                            ->get()
                            ->mapWithKeys(fn ($bank) => [
                                $bank->id => trim($bank->bank_name . ' — ' . $bank->account_name),
                            ])
                            ->all())
                        ->searchable(),

                    TextInput::make('amount')
                        ->label('المبلغ')
                        ->numeric()
                        ->prefix('ر.س')
                        ->required()
                        ->default(fn () => app(SupplierRefundPostingService::class)
                            ->availableForReturn($this->record)),

                    TextInput::make('reference')
                        ->label('مرجع خارجي / رقم تحويل')
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('البيان')
                        ->default(fn () => 'استرداد من المورد عن مرتجع الشراء ' . $this->record->invoice_number)
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    try {
                        $voucher = app(SupplierRefundPostingService::class)->post([
                            ...$data,
                            'invoice_id'  => $this->record->id,
                            'receipt_date'=> today(),
                        ]);

                        Notification::make()
                            ->title('تم استرداد المبلغ من المورد')
                            ->body($voucher->voucher_number . ' — ' . number_format((float) $voucher->amount, 2) . ' ر.س')
                            ->success()
                            ->send();

                        $this->redirect(
                            \App\Filament\Admin\Resources\ReceiptVouchers\ReceiptVoucherResource::getUrl(
                                'view',
                                ['record' => $voucher]
                            )
                        );
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('تعذر تسجيل استرداد المورد')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // نسخ
            Action::make('copy')
                ->label('نسخ')
                ->color('warning')
                ->action(function () {
                    $new = $this->record->replicate();
                    $new->invoice_number   = \App\Models\Invoice::generateNumber($this->record->type);
                    $new->status           = 'draft';
                    $new->paid_amount      = 0;
                    $new->remaining_amount = $new->total;

                    // تصفير بيانات زاتكا بالكامل — لا تُنسخ أبداً
                    $new->zatca_sent         = false;
                    $new->zatca_environment  = null;
                    $new->zatca_status_code  = null;
                    $new->zatca_uuid         = null;
                    $new->zatca_invoice_hash = null;
                    $new->zatca_error        = null;
                    $new->zatca_sent_at      = null;
                    $new->zatca_response     = null;

                    $new->created_at = now();
                    $new->save();

                    foreach ($this->record->items as $item) {
                        $newItem             = $item->replicate();
                        $newItem->invoice_id = $new->id;
                        $newItem->save();
                    }

                    Notification::make()
                        ->title('تم نسخ الفاتورة: ' . $new->invoice_number)
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('view', ['record' => $new]));
                }),

            EditAction::make()->label('تعديل'),
        ];
    }

    private function getWhatsappUrl(): string
    {
        $invoice  = $this->record;
        $customer = $invoice->customer;
        $phone    = preg_replace('/[^0-9]/', '', $customer?->phone ?? '');

        if (str_starts_with($phone, '0')) {
            $phone = '966' . substr($phone, 1);
        }

        $currency = Setting::get('currency_symbol', 'ر.س');

        $message  = "عزيزي {$customer?->name}،\n";
        $message .= "نشكركم على تعاملكم معنا 🌟\n\n";
        $message .= "📄 *فاتورة رقم:* {$invoice->invoice_number}\n";
        $message .= "📅 *التاريخ:* " . $invoice->invoice_date->format('d/m/Y') . "\n";
        $message .= "💰 *الإجمالي:* " . number_format($invoice->total, 2) . " {$currency}\n";
        $message .= "✅ *المدفوع:* " . number_format($invoice->paid_amount, 2) . " {$currency}\n";

        if ($invoice->remaining_amount > 0) {
            $message .= "⚠️ *المتبقي:* " . number_format($invoice->remaining_amount, 2) . " {$currency}\n";
        }

        if ($invoice->zatca_uuid) {
            $message .= "\n🔐 *رقم الفاتورة الزاتكا:* {$invoice->zatca_uuid}\n";
        }

        $message .= "\nشكراً لثقتكم 🙏";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('components.invoice-footer', [
            'invoice' => $this->record,
        ]);
    }
}