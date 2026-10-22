<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.admin.pages.settings-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public function getTitle(): string
    {
        return 'إعدادات النظام';
    }

    public function mount(): void
    {
        $this->form->fill($this->loadSettings());
    }

    protected function loadSettings(): array
    {
        $keys = [
            'company_name', 'company_name_en', 'company_tax_number',
            'company_cr', 'company_phone', 'company_email', 'company_website',
            'company_country', 'company_city', 'company_district',
            'company_street', 'company_building', 'company_postal',
            'zatca_sending_mode', 'zatca_cert_path', 'zatca_key_path', 'zatca_secret', 'zatca_secret_path',
            'currency_code', 'currency_symbol',
        ];

        $data = [];

        foreach ($keys as $key) {
            $data[$key] = Setting::get($key, '');
        }

        if (blank($data['company_country'] ?? null)) {
            $data['company_country'] = 'SA';
        }

        if (blank($data['zatca_sending_mode'] ?? null)) {
            $data['zatca_sending_mode'] = 'simulation';
        }

        if (blank($data['currency_code'] ?? null)) {
            $data['currency_code'] = 'SAR';
        }

        if (blank($data['currency_symbol'] ?? null)) {
            $data['currency_symbol'] = 'ر.س';
        }

        return $data;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('settings_tabs')
                    ->tabs([
                        Tab::make('بيانات الشركة')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('هوية المنشأة')
                                    ->description('البيانات الأساسية التي تظهر في الفواتير والتقارير والمستندات الرسمية.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('company_name')
                                                ->label('اسم الشركة بالعربية')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('company_name_en')
                                                ->label('اسم الشركة بالإنجليزية')
                                                ->maxLength(255),
                                            TextInput::make('company_tax_number')
                                                ->label('الرقم الضريبي')
                                                ->helperText('مطلوب لفواتير زاتكا ويجب أن يتكون من 15 رقمًا.')
                                                ->maxLength(15),
                                            TextInput::make('company_cr')
                                                ->label('السجل التجاري')
                                                ->maxLength(50),
                                        ]),
                                    ]),

                                Section::make('بيانات التواصل')
                                    ->description('وسائل التواصل الرسمية الخاصة بالمنشأة.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('company_phone')
                                                ->label('هاتف الشركة')
                                                ->tel()
                                                ->maxLength(20),
                                            TextInput::make('company_email')
                                                ->label('البريد الإلكتروني')
                                                ->email()
                                                ->maxLength(255),
                                            TextInput::make('company_website')
                                                ->label('الموقع الإلكتروني')
                                                ->url()
                                                ->maxLength(255)
                                                ->columnSpanFull(),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('عنوان الشركة')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('العنوان الوطني للمنشأة')
                                    ->description('استخدم العنوان الرسمي المسجل للمنشأة لضمان اتساق بيانات الفوترة.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('company_country')
                                                ->label('الدولة')
                                                ->options([
                                                    'SA' => 'المملكة العربية السعودية',
                                                    'EG' => 'مصر',
                                                    'AE' => 'الإمارات',
                                                    'KW' => 'الكويت',
                                                    'QA' => 'قطر',
                                                    'BH' => 'البحرين',
                                                    'OM' => 'سلطنة عُمان',
                                                ])
                                                ->default('SA')
                                                ->required(),
                                            TextInput::make('company_city')
                                                ->label('المدينة')
                                                ->required()
                                                ->maxLength(100),
                                            TextInput::make('company_district')
                                                ->label('الحي')
                                                ->maxLength(100),
                                            TextInput::make('company_street')
                                                ->label('اسم الشارع')
                                                ->maxLength(255),
                                            TextInput::make('company_building')
                                                ->label('رقم المبنى')
                                                ->maxLength(20),
                                            TextInput::make('company_postal')
                                                ->label('الرمز البريدي')
                                                ->maxLength(10),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('إعدادات زاتكا')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Placeholder::make('zatca_brand')
                                    ->label('')
                                    ->content(fn () => $this->getZatcaBrandCard()),

                                Section::make('بيئة الإرسال')
                                    ->description('اختر بيئة زاتكا الحالية. يفضّل إنهاء الاختبارات قبل الانتقال إلى البيئة الإنتاجية.')
                                    ->schema([
                                        Select::make('zatca_sending_mode')
                                            ->label('بيئة الإرسال')
                                            ->options([
                                                'simulation' => 'تجريبية (Simulation)',
                                                'compliance' => 'امتثال (Compliance)',
                                                'production' => 'إنتاجية (Production)',
                                            ])
                                            ->required()
                                            ->helperText('ابدأ بالبيئة التجريبية ثم اختبر الامتثال قبل الإنتاج.'),
                                    ]),

                                Section::make('بيانات المصادقة والشهادات')
                                    ->description('يمكن استخدام Secret مباشر أو مسار ملف secret.txt. لا يلزم استخدام الطريقتين معًا.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('zatca_secret')
                                                ->label('Secret مباشر')
                                                ->password()
                                                ->revealable()
                                                ->maxLength(500)
                                                ->helperText('اختياري عند استخدام ملف secret.txt.'),
                                            TextInput::make('zatca_secret_path')
                                                ->label('مسار ملف secret.txt')
                                                ->placeholder('D:\\ZatcaIntegration\\cert\\secret.txt'),
                                            TextInput::make('zatca_cert_path')
                                                ->label('مسار الشهادة Certificate (.pem)')
                                                ->placeholder(storage_path('app/zatca/cert.pem'))
                                                ->helperText('المسار الكامل للملف على السيرفر')
                                                ->columnSpanFull(),
                                            TextInput::make('zatca_key_path')
                                                ->label('مسار المفتاح الخاص Private Key (.key/.pem)')
                                                ->placeholder(storage_path('app/zatca/private.key'))
                                                ->helperText('المسار الكامل للملف على السيرفر')
                                                ->columnSpanFull(),
                                        ]),
                                    ]),

                                Section::make('حالة التكامل')
                                    ->description('قراءة فورية لأهم متطلبات الربط على السيرفر الحالي.')
                                    ->schema([
                                        Placeholder::make('zatca_status_board')
                                            ->label('')
                                            ->content(fn () => $this->getZatcaStatusBoard()),
                                    ]),
                            ]),

                        Tab::make('إعدادات عامة')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('العملة والعرض المالي')
                                    ->description('العملة الأساسية المستخدمة في واجهة النظام والتقارير.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('currency_code')
                                                ->label('العملة الأساسية')
                                                ->options([
                                                    'SAR' => 'ريال سعودي (SAR)',
                                                    'EGP' => 'جنيه مصري (EGP)',
                                                    'AED' => 'درهم إماراتي (AED)',
                                                    'USD' => 'دولار أمريكي (USD)',
                                                ])
                                                ->required(),
                                            TextInput::make('currency_symbol')
                                                ->label('رمز العملة المعروض')
                                                ->placeholder('ر.س')
                                                ->maxLength(10)
                                                ->helperText('مثال: ر.س أو ج.م أو $'),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        foreach (['company', 'address', 'zatca', 'general'] as $group) {
            Setting::clearGroupCache($group);
        }

        $this->form->fill($this->loadSettings());

        Notification::make()
            ->title('تم حفظ إعدادات النظام')
            ->body('تم تحديث بيانات الشركة والتكاملات والإعدادات العامة بنجاح.')
            ->success()
            ->send();
    }

    public function reloadSettings(): void
    {
        $this->form->fill($this->loadSettings());

        Notification::make()
            ->title('تم إلغاء التغييرات غير المحفوظة')
            ->info()
            ->send();
    }

    public function checkZatcaSetup(): void
    {
        $state = $this->data ?? [];

        $vat = preg_replace('/\D+/', '', (string) ($state['company_tax_number'] ?? ''));
        $vatOk = strlen($vat) === 15;

        $certPath = trim((string) ($state['zatca_cert_path'] ?? ''));
        $keyPath = trim((string) ($state['zatca_key_path'] ?? ''));
        $secretPath = trim((string) ($state['zatca_secret_path'] ?? ''));
        $secret = trim((string) ($state['zatca_secret'] ?? ''));

        $certOk = $certPath !== '' && file_exists($certPath);
        $keyOk = $keyPath !== '' && file_exists($keyPath);
        $secretOk = $secret !== '' || ($secretPath !== '' && file_exists($secretPath));
        $dllOk = extension_loaded('com_dotnet');

        $checks = [
            'الرقم الضريبي' => $vatOk,
            'الشهادة' => $certOk,
            'المفتاح الخاص' => $keyOk,
            'Secret' => $secretOk,
            'COM DLL' => $dllOk,
        ];

        $failed = collect($checks)
            ->filter(fn (bool $ok) => !$ok)
            ->keys()
            ->values();

        if ($failed->isEmpty()) {
            Notification::make()
                ->title('إعدادات زاتكا الأساسية جاهزة')
                ->body('تم التحقق من الرقم الضريبي والملفات وCOM DLL على السيرفر الحالي.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('إعدادات زاتكا تحتاج مراجعة')
            ->body('العناصر غير الجاهزة: ' . $failed->implode('، '))
            ->warning()
            ->send();
    }

    public function getSettingsHealth(): array
    {
        $state = $this->data ?? [];

        $companyKeys = [
            'company_name', 'company_name_en', 'company_tax_number',
            'company_cr', 'company_phone', 'company_email',
        ];

        $completed = collect($companyKeys)
            ->filter(fn (string $key) => filled($state[$key] ?? null))
            ->count();

        $completion = (int) round(($completed / count($companyKeys)) * 100);

        $countries = [
            'SA' => 'السعودية',
            'EG' => 'مصر',
            'AE' => 'الإمارات',
            'KW' => 'الكويت',
            'QA' => 'قطر',
            'BH' => 'البحرين',
            'OM' => 'عُمان',
        ];

        $mode = $state['zatca_sending_mode'] ?? 'simulation';
        $modeLabels = [
            'simulation' => 'Simulation',
            'compliance' => 'Compliance',
            'production' => 'Production',
        ];

        return [
            'company_completion' => $completion,
            'address' => collect([
                $state['company_city'] ?? null,
                $countries[$state['company_country'] ?? 'SA'] ?? ($state['company_country'] ?? null),
            ])->filter()->implode('، '),
            'zatca_mode' => $mode,
            'zatca_mode_label' => $modeLabels[$mode] ?? ucfirst((string) $mode),
            'currency_code' => $state['currency_code'] ?? 'SAR',
            'currency_symbol' => $state['currency_symbol'] ?? 'ر.س',
            'country_label' => $countries[$state['company_country'] ?? 'SA'] ?? ($state['company_country'] ?? 'السعودية'),
            'zatca_readiness' => $this->getZatcaReadiness(),
        ];
    }

    public function getZatcaReadiness(): int
    {
        $state = $this->data ?? [];
        $vat = preg_replace('/\D+/', '', (string) ($state['company_tax_number'] ?? ''));

        $checks = [
            strlen($vat) === 15,
            filled($state['zatca_cert_path'] ?? null) && file_exists((string) $state['zatca_cert_path']),
            filled($state['zatca_key_path'] ?? null) && file_exists((string) $state['zatca_key_path']),
            filled($state['zatca_secret'] ?? null) || (filled($state['zatca_secret_path'] ?? null) && file_exists((string) $state['zatca_secret_path'])),
            extension_loaded('com_dotnet'),
        ];

        return (int) round((collect($checks)->filter()->count() / count($checks)) * 100);
    }

    public function getZatcaBrandCard(): HtmlString
    {
        $state = $this->data ?? [];
        $mode = (string) ($state['zatca_sending_mode'] ?? 'simulation');
        $labels = [
            'simulation' => 'Simulation',
            'compliance' => 'Compliance',
            'production' => 'Production',
        ];
        $label = $labels[$mode] ?? ucfirst($mode);
        $readiness = $this->getZatcaReadiness();
        $logo = 'https://www.zatca.gov.sa/ar/MediaCenter/AuthorityIdentity/PublishingImages/dark-bg.png';

        return new HtmlString(
            '<div class="zatca-brand-card">'
            . '<div class="zatca-brand-copy">'
            . '<div class="zatca-brand-kicker">E-INVOICING INTEGRATION</div>'
            . '<div class="zatca-brand-title">تكامل هيئة الزكاة والضريبة والجمارك</div>'
            . '<div class="zatca-brand-sub">إدارة بيئة الإرسال، بيانات المصادقة، الشهادة والمفتاح الخاص من نقطة واحدة.</div>'
            . '<div class="zatca-brand-meta"><span class="zatca-env-tag">' . e($label) . '</span><span class="zatca-readiness-tag">جاهزية الربط ' . $readiness . '%</span></div>'
            . '</div>'
            . '<div class="zatca-logo-wrap">'
            . '<img src="' . e($logo) . '" alt="ZATCA" class="zatca-official-logo" referrerpolicy="no-referrer" loading="lazy" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'block\';">'
            . '<div class="zatca-logo-fallback" style="display:none">ZATCA<div>هيئة الزكاة والضريبة والجمارك</div></div>'
            . '</div>'
            . '</div>'
        );
    }

    public function getZatcaStatusBoard(): HtmlString
    {
        $state = $this->data ?? [];
        $vat = preg_replace('/\D+/', '', (string) ($state['company_tax_number'] ?? ''));
        $certPath = trim((string) ($state['zatca_cert_path'] ?? ''));
        $keyPath = trim((string) ($state['zatca_key_path'] ?? ''));
        $secretPath = trim((string) ($state['zatca_secret_path'] ?? ''));
        $secret = trim((string) ($state['zatca_secret'] ?? ''));

        $items = [
            ['الرقم الضريبي', strlen($vat) === 15, strlen($vat) === 15 ? '15 رقم' : 'يحتاج مراجعة'],
            ['Certificate', $certPath !== '' && file_exists($certPath), $certPath !== '' && file_exists($certPath) ? basename($certPath) : 'غير جاهز'],
            ['Private Key', $keyPath !== '' && file_exists($keyPath), $keyPath !== '' && file_exists($keyPath) ? basename($keyPath) : 'غير جاهز'],
            ['Secret', $secret !== '' || ($secretPath !== '' && file_exists($secretPath)), $secret !== '' ? 'Direct Secret' : (($secretPath !== '' && file_exists($secretPath)) ? basename($secretPath) : 'غير جاهز')],
            ['COM DLL', extension_loaded('com_dotnet'), extension_loaded('com_dotnet') ? 'Enabled' : 'Disabled'],
        ];

        $html = '<div class="zatca-status-grid">';
        foreach ($items as [$name, $ok, $detail]) {
            $class = $ok ? 'ready' : 'missing';
            $status = $ok ? 'جاهز' : 'مراجعة';
            $html .= '<div class="zatca-status-item ' . $class . '">'
                . '<div class="zatca-status-top"><span class="zatca-status-dot"></span><span class="zatca-status-state">' . $status . '</span></div>'
                . '<div class="zatca-status-name">' . e($name) . '</div>'
                . '<div class="zatca-status-detail">' . e($detail) . '</div>'
                . '</div>';
        }
        $html .= '</div>';

        return new HtmlString($html);
    }

    protected function getCertStatus(): string
    {
        $path = trim((string) ($this->data['zatca_cert_path'] ?? Setting::get('zatca_cert_path', '')));

        if ($path === '') {
            return 'لم يتم تحديد مسار الشهادة';
        }

        if (!file_exists($path)) {
            return 'الملف غير موجود';
        }

        return 'موجود: ' . basename($path);
    }

    protected function getKeyStatus(): string
    {
        $path = trim((string) ($this->data['zatca_key_path'] ?? Setting::get('zatca_key_path', '')));

        if ($path === '') {
            return 'لم يتم تحديد مسار المفتاح';
        }

        if (!file_exists($path)) {
            return 'الملف غير موجود';
        }

        return 'موجود: ' . basename($path);
    }

    protected function getSecretStatus(): string
    {
        $directSecret = trim((string) ($this->data['zatca_secret'] ?? Setting::get('zatca_secret', '')));
        $path = trim((string) ($this->data['zatca_secret_path'] ?? Setting::get('zatca_secret_path', '')));

        if ($directSecret !== '') {
            return 'Secret مباشر محفوظ';
        }

        if ($path === '') {
            return 'لم يتم إدخال Secret أو مسار ملف';
        }

        if (!file_exists($path)) {
            return 'ملف Secret غير موجود';
        }

        return 'ملف Secret موجود';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->submit('save')
                ->color('primary'),
        ];
    }
}
