<?php

namespace App\Filament\Admin\Pages;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\Warehouse;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class SetupWizard extends Page
{

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'إعداد النظام';
    protected static ?string $title           = 'معالج إعداد النظام';
    protected static ?int    $navigationSort  = 1;
    protected string         $view            = 'filament.admin.pages.setup-wizard';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات · Settings';
    }

    // ── Step tracking ─────────────────────────────────────────────
    public int  $currentStep = 1;
    public bool $completed   = false;

    // ── Step 1 — بيانات الشركة ────────────────────────────────────
    public string $companyName    = '';
    public string $companyNameEn  = '';
    public string $companyAddress = '';
    public string $companyPhone   = '';
    public string $companyEmail   = '';
    public string $companyTax     = '';
    public string $companyCr      = '';
    public string $companyWebsite = '';

    // ── Step 2 — العنوان ──────────────────────────────────────────
    public string $companyCountry  = 'SA';
    public string $companyCity     = '';
    public string $companyDistrict = '';
    public string $companyStreet   = '';
    public string $companyBuilding = '';
    public string $companyPostal   = '';

    // ── Step 3 — الفرع والمخزن ───────────────────────────────────
    public string $branchName    = 'الفرع الرئيسي';
    public string $warehouseName = 'المخزن الرئيسي';

    // ── Step 4 — شجرة الحسابات (تلقائي) ─────────────────────────

    // ── Step 5 — Demo Data ────────────────────────────────────────
    public bool $loadDemoData = false;

    public function mount(): void
    {
        // حمّل القيم الموجودة من settings
        $this->companyName     = Setting::get('company_name', '');
        $this->companyNameEn   = Setting::get('company_name_en', '');
        $this->companyPhone    = Setting::get('company_phone', '');
        $this->companyEmail    = Setting::get('company_email', '');
        $this->companyTax      = Setting::get('company_tax_number', '');
        $this->companyCr       = Setting::get('company_cr', '');
        $this->companyWebsite  = Setting::get('company_website', '');
        $this->companyCountry  = Setting::get('company_country', 'SA');
        $this->companyCity     = Setting::get('company_city', '');
        $this->companyDistrict = Setting::get('company_district', '');
        $this->companyStreet   = Setting::get('company_street', '');
        $this->companyBuilding = Setting::get('company_building', '');
        $this->companyPostal   = Setting::get('company_postal', '');
    }

    public function nextStep(): void
    {
        // ── Step 1: بيانات الشركة ────────────────────────────────
        if ($this->currentStep === 1) {
            if (empty(trim($this->companyName))) {
                Notification::make()->title('أدخل اسم الشركة أولاً')->warning()->send();
                return;
            }

            Setting::set('company_name',       $this->companyName);
            Setting::set('company_name_en',     $this->companyNameEn);
            Setting::set('company_phone',       $this->companyPhone);
            Setting::set('company_email',       $this->companyEmail);
            Setting::set('company_tax_number',  $this->companyTax);
            Setting::set('company_cr',          $this->companyCr);
            Setting::set('company_website',     $this->companyWebsite);

            Setting::clearGroupCache('company');
        }

        // ── Step 2: عنوان الشركة ─────────────────────────────────
        if ($this->currentStep === 2) {
            Setting::set('company_country',  $this->companyCountry);
            Setting::set('company_city',     $this->companyCity);
            Setting::set('company_district', $this->companyDistrict);
            Setting::set('company_street',   $this->companyStreet);
            Setting::set('company_building', $this->companyBuilding);
            Setting::set('company_postal',   $this->companyPostal);

            Setting::clearGroupCache('address');
        }

        // ── Step 3: الفرع والمخزن ────────────────────────────────
        if ($this->currentStep === 3) {
            if (empty(trim($this->branchName))) {
                Notification::make()->title('أدخل اسم الفرع')->warning()->send();
                return;
            }

            $branch = Branch::firstOrCreate(
                ['name' => $this->branchName],
                ['code' => 'MAIN', 'is_active' => true, 'is_default' => true]
            );

            Warehouse::firstOrCreate(
                ['name' => $this->warehouseName],
                ['code' => 'MAIN', 'branch_id' => $branch->id, 'is_active' => true, 'is_default' => true]
            );
        }

        // ── Step 4: شجرة الحسابات ────────────────────────────────
        if ($this->currentStep === 4) {
            try {
                Artisan::call('db:seed', ['--class' => 'AccountsSeeder', '--force' => true]);
            } catch (\Exception $e) {
                Notification::make()->title('خطأ في شجرة الحسابات: ' . $e->getMessage())->danger()->send();
                return;
            }
        }

        // ── Step 5: Demo Data + إنهاء ─────────────────────────────
        if ($this->currentStep === 5) {
            if ($this->loadDemoData) {
                try {
                    Artisan::call('db:seed', ['--class' => 'DemoDataSeeder', '--force' => true]);
                } catch (\Exception $e) {
                    Notification::make()->title('خطأ في البيانات التجريبية')->warning()->send();
                }
            }

            $this->completed = true;
            Notification::make()->title('🎉 تم إعداد النظام بنجاح!')->success()->send();
            return;
        }

        $this->currentStep++;
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
}