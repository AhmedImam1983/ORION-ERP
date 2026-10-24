<?php

namespace App\Filament\Admin\Resources\Invoices;

use App\Filament\Admin\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Admin\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Admin\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Admin\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Admin\Resources\Invoices\Pages\PurchaseReturn;
use App\Filament\Admin\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Admin\Resources\Invoices\Schemas\InvoiceInfolist;
use App\Filament\Admin\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\Action;
use App\Services\ZatcaService;
use App\Services\AccountingReversalService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Filament\Traits\HasResourcePermissions;


class InvoiceResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Invoice::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'الفواتير';
    protected static ?string $modelLabel = 'فاتورة';
    protected static ?string $pluralModelLabel = 'الفواتير';
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?int $navigationSort = 1;


    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات · Sales';
    }

    public static function infolist(Schema $schema): Schema
    {
        return InvoiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getRecordActions(): array
    {
        return [

            // ══ زاتكا — الديمو السعودي ══════════════════════════
            Action::make('submit_zatca')
                ->label('إرسال إلى زاتكا')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('إرسال الفاتورة إلى زاتكا')
                ->modalDescription('اختيار Test يستخدم بيانات الاختبار. اختيار Production يستهدف البيئة الفعلية، لكنه يظل محظورًا ما لم يتم تفعيل السماح به صراحةً على خادم الإنتاج.')
                ->modalSubmitActionLabel('تأكيد الإرسال')
                ->form([
                    Select::make('environment')
                        ->label('بيئة الإرسال')
                        ->options([
                            'test' => 'Test — بيئة الاختبار',
                            'production' => 'Production — البيئة الفعلية',
                        ])
                        ->default('test')
                        ->helperText('الإرسال إلى Production معطّل افتراضيًا. لا تفعّله على Staging.')
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $environment = (string) ($data['environment'] ?? 'test');
                    $result = app(ZatcaService::class)->submitInvoice($record, $environment);
                    $environmentLabel = $environment === 'production' ? 'Production' : 'Test';

                    if (!empty($result['already_sent'])) {
                        Notification::make()
                            ->title('الفاتورة مرسلة بالفعل')
                            ->body(($result['error'] ?? 'تم الإرسال من قبل') . ' — البيئة: ' . $environmentLabel)
                            ->warning()
                            ->send();
                        return;
                    }

                    if (!empty($result['in_progress'])) {
                        Notification::make()
                            ->title('الإرسال قيد التنفيذ')
                            ->body(($result['error'] ?? 'هناك محاولة إرسال قائمة') . ' — البيئة: ' . $environmentLabel)
                            ->warning()
                            ->send();
                        return;
                    }

                    if (!empty($result['success'])) {
                        Notification::make()
                            ->title('تم قبول إرسال الفاتورة من زاتكا')
                            ->body('UUID: ' . ($result['uuid'] ?? '-') . ' | البيئة: ' . $environmentLabel)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('فشل إرسال الفاتورة إلى زاتكا')
                            ->body(($result['error'] ?? 'خطأ غير معروف') . ' | البيئة: ' . $environmentLabel)
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn ($record) =>
                    in_array($record->type, ['sale'], true)
                    && in_array($record->status, ['confirmed', 'partial', 'paid'], true)
                ),


            // ══ مرتجع شراء مرتبط بالفاتورة الأصلية ════════════════════════
            Action::make('purchase_return')
                ->label('إنشاء مرتجع شراء')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->url(
                    fn ($record) => static::getUrl(
                        'purchase-return',
                        ['record' => $record]
                    )
                )
                ->visible(
                    fn ($record) =>
                        $record->type === 'purchase'
                        && in_array($record->status, ['confirmed', 'partial', 'paid'], true)
                ),

            // ══ عكس / إلغاء الفاتورة المرحّلة ═══════════════════════════════
            Action::make('reverse_invoice')
                ->label('عكس / إلغاء الفاتورة')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('عكس الفاتورة المرحّلة')
                ->modalDescription(
                    'لن يتم حذف الفاتورة أو القيود الأصلية. يجب عكس أي سند قبض/صرف مرتبط أولاً. سيتم إنشاء REV مستقل وعكس القيود والمخزون الموثق فقط.'
                )
                ->modalSubmitActionLabel('تأكيد عكس الفاتورة')
                ->form([
                    Textarea::make('reason')
                        ->label('سبب الإلغاء / العكس')
                        ->required()
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->visible(
                    fn ($record) =>
                        in_array($record->status, ['confirmed', 'partial', 'paid'], true)
                )
                ->action(function ($record, array $data) {
                    try {
                        $reversal = app(AccountingReversalService::class)
                            ->reverse($record, $data['reason']);

                        $record->refresh();

                        Notification::make()
                            ->title('تم عكس الفاتورة بنجاح')
                            ->body(
                                $reversal->reversal_number .
                                ' — ' .
                                ($reversal->reversalJournalEntry?->entry_number ?? '')
                            )
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('تعذر عكس الفاتورة')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('pdf')
                ->label('طباعة PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn($record) => route('invoices.pdf', $record))
                ->openUrlInNewTab(),

            Action::make('whatsapp')
                ->label('إرسال واتساب')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('إرسال تأكيد الفاتورة على واتساب')
                ->modalDescription('سيتم إرسال تفاصيل الفاتورة لرقم العميل')
                ->action(function ($record) {
                    $whatsapp = new \App\Services\WhatsAppService();
                    $result   = $whatsapp->sendInvoiceConfirmation($record);
                    if ($result['success']) {
                        \Filament\Notifications\Notification::make()
                            ->title('تم إرسال الرسالة بنجاح')
                            ->success()->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('فشل الإرسال: ' . $result['error'])
                            ->danger()->send();
                    }
                })
                ->visible(fn($record) => !empty($record->customer?->phone)),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view'            => ViewInvoice::route('/{record}'),
            'edit'            => EditInvoice::route('/{record}/edit'),
            'purchase-return' => PurchaseReturn::route('/{record}/purchase-return'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        // الكاشير يشوف فواتير اليوم بس
        if (auth()->user()?->hasRole('cashier')) {
            $query->whereDate('created_at', today());
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }
}