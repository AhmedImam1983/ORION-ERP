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
            // Legacy settings are retained for compatibility while the sender is migrated.
            'zatca_sending_mode', 'zatca_cert_path', 'zatca_key_path',
            'zatca_secret', 'zatca_secret_path', 'zatca_root_path',
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

        $legacyMode = (string) $data['zatca_sending_mode'];
        $legacyIsProduction = $legacyMode === 'production';
        $legacyRoot = trim((string) ($data['zatca_root_path'] ?? ''));
        if ($legacyRoot === '') {
            $legacyRoot = 'D:\\ZatcaIntegration';
        }
        $rootBase = rtrim($legacyRoot, "\\/");

        // Backward-compatible defaults: existing credentials are assigned only to
        // the environment that was active in the old single-environment settings.
        $testModeDefault = in_array($legacyMode, ['simulation', 'compliance'], true)
            ? $legacyMode
            : 'simulation';

        $data['zatca_test_mode'] = Setting::get('zatca_test_mode', $testModeDefault);
        $data['zatca_test_cert_path'] = Setting::get(
            'zatca_test_cert_path',
            $legacyIsProduction ? '' : (string) ($data['zatca_cert_path'] ?? '')
        );
        $data['zatca_test_key_path'] = Setting::get(
            'zatca_test_key_path',
            $legacyIsProduction ? '' : (string) ($data['zatca_key_path'] ?? '')
        );
        $data['zatca_test_secret'] = Setting::get(
            'zatca_test_secret',
            $legacyIsProduction ? '' : (string) ($data['zatca_secret'] ?? '')
        );
        $data['zatca_test_secret_path'] = Setting::get(
            'zatca_test_secret_path',
            $legacyIsProduction ? '' : (string) ($data['zatca_secret_path'] ?? '')
        );
        $data['zatca_test_root_path'] = Setting::get(
            'zatca_test_root_path',
            $legacyIsProduction ? $rootBase . DIRECTORY_SEPARATOR . 'Test' : $legacyRoot
        );

        $data['zatca_production_cert_path'] = Setting::get(
            'zatca_production_cert_path',
            $legacyIsProduction ? (string) ($data['zatca_cert_path'] ?? '') : ''
        );
        $data['zatca_production_key_path'] = Setting::get(
            'zatca_production_key_path',
            $legacyIsProduction ? (string) ($data['zatca_key_path'] ?? '') : ''
        );
        $data['zatca_production_secret'] = Setting::get(
            'zatca_production_secret',
            $legacyIsProduction ? (string) ($data['zatca_secret'] ?? '') : ''
        );
        $data['zatca_production_secret_path'] = Setting::get(
            'zatca_production_secret_path',
            $legacyIsProduction ? (string) ($data['zatca_secret_path'] ?? '') : ''
        );
        $data['zatca_production_root_path'] = Setting::get(
            'zatca_production_root_path',
            $legacyIsProduction ? $legacyRoot : $rootBase . DIRECTORY_SEPARATOR . 'Production'
        );

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

                                Section::make('البيئة التجريبية (Test)')
                                    ->description('إعدادات مستقلة للاختبار. لن تُستخدم الشهادة أو بيانات المصادقة هنا عند الإرسال إلى البيئة الفعلية.')
                                    ->schema([
                                        Select::make('zatca_test_mode')
                                            ->label('نمط الربط التجريبي')
                                            ->options([
                                                'simulation' => 'Simulation — تجريبي',
                                                'compliance' => 'Compliance — امتثال',
                                            ])
                                            ->required()
                                            ->helperText('يحدد مسار الربط داخل إعدادات الاختبار فقط.'),
                                        Grid::make(2)->schema([
                                            TextInput::make('zatca_test_secret')
                                                ->label('Secret التجريبي')
                                                ->password()
                                                ->revealable()
                                                ->maxLength(500)
                                                ->helperText('اختياري عند استخدام ملف secret.txt.'),
                                            TextInput::make('zatca_test_secret_path')
                                                ->label('مسار ملف Secret التجريبي')
                                                ->placeholder('D:\\ZatcaIntegration\\Test\\cert\\secret.txt'),
                                            TextInput::make('zatca_test_cert_path')
                                                ->label('مسار شهادة الاختبار Certificate (.pem)')
                                                ->placeholder(storage_path('app/zatca/test/cert.pem'))
                                                ->helperText('المسار الكامل للشهادة التجريبية على السيرفر')
                                                ->columnSpanFull(),
                                            TextInput::make('zatca_test_key_path')
                                                ->label('مسار المفتاح الخاص للاختبار (.key/.pem)')
                                                ->placeholder(storage_path('app/zatca/test/private.key'))
                                                ->helperText('المسار الكامل للمفتاح التجريبي على السيرفر')
                                                ->columnSpanFull(),
                                            TextInput::make('zatca_test_root_path')
                                                ->label('مجلد ملفات XML ونتائج الاختبار')
                                                ->placeholder('D:\\ZatcaIntegration\\Test')
                                                ->required()
                                                ->columnSpanFull(),
                                        ]),
                                    ]),

                                Section::make('البيئة الفعلية (Production)')
                                    ->description('إعدادات مستقلة للإنتاج الحقيقي. راجع بيانات الشهادة والـ Secret قبل الإرسال إلى الهيئة.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('zatca_production_secret')
                                                ->label('Secret الإنتاج')
                                                ->password()
                                                ->revealable()
                                                ->maxLength(500)
                                                ->helperText('اختياري عند استخدام ملف secret.txt.'),
                                            TextInput::make('zatca_production_secret_path')
                                                ->label('مسار ملف Secret للإنتاج')
                                                ->placeholder('D:\\ZatcaIntegration\\Production\\cert\\secret.txt'),
                                            TextInput::make('zatca_production_cert_path')
                                                ->label('مسار شهادة الإنتاج Certificate (.pem)')
                                                ->placeholder(storage_path('app/zatca/production/cert.pem'))
                                                ->helperText('المسار الكامل لشهادة الإنتاج على السيرفر')
                                                ->columnSpanFull(),
                                            TextInput::make('zatca_production_key_path')
                                                ->label('مسار المفتاح الخاص للإنتاج (.key/.pem)')
                                                ->placeholder(storage_path('app/zatca/production/private.key'))
                                                ->helperText('المسار الكامل لمفتاح الإنتاج على السيرفر')
                                                ->columnSpanFull(),
                                            TextInput::make('zatca_production_root_path')
                                                ->label('مجلد ملفات XML ونتائج الإنتاج')
                                                ->placeholder('D:\\ZatcaIntegration\\Production')
                                                ->required()
                                                ->columnSpanFull(),
                                        ]),
                                    ]),

                                Section::make('حالة التكامل لكل بيئة')
                                    ->description('يعرض حالة الشهادات وبيانات المصادقة بشكل مستقل لكل بيئة.')
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
        $vat = preg_replace('/\\D+/', '', (string) ($state['company_tax_number'] ?? ''));

        $checks = [
            'الرقم الضريبي للمنشأة' => strlen($vat) === 15,
            'PHP COM extension' => extension_loaded('com_dotnet'),
        ];

        foreach ([
            'zatca_test' => 'البيئة التجريبية',
            'zatca_production' => 'البيئة الفعلية',
        ] as $prefix => $label) {
            $certPath = trim((string) ($state[$prefix . '_cert_path'] ?? ''));
            $keyPath = trim((string) ($state[$prefix . '_key_path'] ?? ''));
            $secretPath = trim((string) ($state[$prefix . '_secret_path'] ?? ''));
            $secret = trim((string) ($state[$prefix . '_secret'] ?? ''));
            $rootPath = trim((string) ($state[$prefix . '_root_path'] ?? ''));

            $checks[$label . ' — الشهادة'] = $certPath !== '' && file_exists($certPath);
            $checks[$label . ' — المفتاح الخاص'] = $keyPath !== '' && file_exists($keyPath);
            $checks[$label . ' — Secret'] = $secret !== '' || ($secretPath !== '' && file_exists($secretPath));
            $checks[$label . ' — مجلد النتائج'] = $rootPath !== '';
        }

        $testMode = (string) ($state['zatca_test_mode'] ?? '');
        $checks['نمط الربط التجريبي'] = in_array($testMode, ['simulation', 'compliance'], true);

        $failed = collect($checks)
            ->filter(fn (bool $ok) => !$ok)
            ->keys()
            ->values();

        if ($failed->isEmpty()) {
            Notification::make()
                ->title('إعدادات زاتكا للبيئتين جاهزة مبدئيًا')
                ->body('تم التحقق من الرقم الضريبي وملفات الشهادة والمفتاح وبيانات المصادقة ومجلد النتائج في البيئتين.')
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

        $mode = (string) ($state['zatca_test_mode'] ?? 'simulation');
        $modeLabels = [
            'simulation' => 'Test — Simulation',
            'compliance' => 'Test — Compliance',
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
        $test = $this->getEnvironmentReadiness('test');
        $production = $this->getEnvironmentReadiness('production');

        return (int) round(($test + $production) / 2);
    }

    protected function getEnvironmentReadiness(string $environment): int
    {
        $state = $this->data ?? [];
        $prefix = $environment === 'test' ? 'zatca_test' : 'zatca_production';
        $vat = preg_replace('/\\D+/', '', (string) ($state['company_tax_number'] ?? ''));
        $certPath = trim((string) ($state[$prefix . '_cert_path'] ?? Setting::get($prefix . '_cert_path', '')));
        $keyPath = trim((string) ($state[$prefix . '_key_path'] ?? Setting::get($prefix . '_key_path', '')));
        $secretPath = trim((string) ($state[$prefix . '_secret_path'] ?? Setting::get($prefix . '_secret_path', '')));
        $secret = trim((string) ($state[$prefix . '_secret'] ?? Setting::get($prefix . '_secret', '')));
        $rootPath = trim((string) ($state[$prefix . '_root_path'] ?? Setting::get($prefix . '_root_path', '')));

        $checks = [
            strlen($vat) === 15,
            $certPath !== '' && file_exists($certPath),
            $keyPath !== '' && file_exists($keyPath),
            $secret !== '' || ($secretPath !== '' && file_exists($secretPath)),
            $rootPath !== '',
            extension_loaded('com_dotnet'),
        ];

        if ($environment === 'test') {
            $checks[] = in_array(
                (string) ($state['zatca_test_mode'] ?? Setting::get('zatca_test_mode', 'simulation')),
                ['simulation', 'compliance'],
                true
            );
        }

        return (int) round((collect($checks)->filter()->count() / count($checks)) * 100);
    }

    public function getZatcaBrandCard(): HtmlString
    {
        $state = $this->data ?? [];
        $testMode = (string) ($state['zatca_test_mode'] ?? 'simulation');
        $modeLabels = [
            'simulation' => 'Simulation',
            'compliance' => 'Compliance',
        ];
        $testReadiness = $this->getEnvironmentReadiness('test');
        $productionReadiness = $this->getEnvironmentReadiness('production');
        $logo = 'https://www.zatca.gov.sa/ar/MediaCenter/AuthorityIdentity/PublishingImages/dark-bg.png';

        return new HtmlString(
            '<div class="zatca-brand-card">'
            . '<div class="zatca-brand-copy">'
            . '<div class="zatca-brand-kicker">E-INVOICING INTEGRATION</div>'
            . '<div class="zatca-brand-title">تكامل هيئة الزكاة والضريبة والجمارك</div>'
            . '<div class="zatca-brand-sub">إعدادات مستقلة لكل من البيئة التجريبية والبيئة الفعلية. اختر البيئة المطلوبة من شاشة الفواتير غير المرسلة.</div>'
            . '<div class="zatca-brand-meta">'
            . '<span class="zatca-env-tag">Test: ' . e($modeLabels[$testMode] ?? ucfirst($testMode)) . '</span>'
            . '<span class="zatca-readiness-tag">جاهزية التجريبي ' . $testReadiness . '%</span>'
            . '<span class="zatca-readiness-tag">جاهزية الفعلي ' . $productionReadiness . '%</span>'
            . '</div>'
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
        $vat = preg_replace('/\\D+/', '', (string) ($state['company_tax_number'] ?? ''));
        $profiles = [
            'zatca_test' => 'البيئة التجريبية',
            'zatca_production' => 'البيئة الفعلية (Production)',
        ];

        $html = '<div class="zatca-environments-status">';

        foreach ($profiles as $prefix => $title) {
            $certPath = trim((string) ($state[$prefix . '_cert_path'] ?? ''));
            $keyPath = trim((string) ($state[$prefix . '_key_path'] ?? ''));
            $secretPath = trim((string) ($state[$prefix . '_secret_path'] ?? ''));
            $secret = trim((string) ($state[$prefix . '_secret'] ?? ''));
            $rootPath = trim((string) ($state[$prefix . '_root_path'] ?? ''));
            $items = [
                ['الرقم الضريبي', strlen($vat) === 15, strlen($vat) === 15 ? '15 رقم' : 'يحتاج مراجعة'],
                ['Certificate', $certPath !== '' && file_exists($certPath), $certPath !== '' && file_exists($certPath) ? basename($certPath) : 'غير جاهز'],
                ['Private Key', $keyPath !== '' && file_exists($keyPath), $keyPath !== '' && file_exists($keyPath) ? basename($keyPath) : 'غير جاهز'],
                ['Secret', $secret !== '' || ($secretPath !== '' && file_exists($secretPath)), $secret !== '' ? 'Secret مباشر' : (($secretPath !== '' && file_exists($secretPath)) ? basename($secretPath) : 'غير جاهز')],
                ['مجلد النتائج', $rootPath !== '', $rootPath !== '' ? $rootPath : 'غير محدد'],
                ['COM DLL', extension_loaded('com_dotnet'), extension_loaded('com_dotnet') ? 'Enabled' : 'Disabled'],
            ];

            if ($prefix === 'zatca_test') {
                $mode = (string) ($state['zatca_test_mode'] ?? 'simulation');
                $modeReady = in_array($mode, ['simulation', 'compliance'], true);
                $items[] = ['نمط الربط', $modeReady, $modeReady ? strtoupper($mode) : 'غير محدد'];
            }

            $html .= '<div class="zatca-environment-status-section">'
                . '<h4 class="zatca-environment-status-title">' . e($title) . '</h4>'
                . '<div class="zatca-status-grid">';

            foreach ($items as [$name, $ok, $detail]) {
                $class = $ok ? 'ready' : 'missing';
                $statusLabel = $ok ? 'جاهز' : 'مراجعة';
                $html .= '<div class="zatca-status-item ' . $class . '">'
                    . '<div class="zatca-status-top"><span class="zatca-status-dot"></span><span class="zatca-status-state">' . $statusLabel . '</span></div>'
                    . '<div class="zatca-status-name">' . e($name) . '</div>'
                    . '<div class="zatca-status-detail">' . e($detail) . '</div>'
                    . '</div>';
            }

            $html .= '</div></div>';
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
