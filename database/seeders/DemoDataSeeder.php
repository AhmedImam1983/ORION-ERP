<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Employee;
use App\Models\Attendance;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 بدء إضافة بيانات الديمو...');

        $this->seedCategories();
        $this->seedProducts();
        $this->seedCustomers();
        $this->seedSuppliers();
        $this->seedEmployees();
        $this->seedInvoices();
        $this->seedAttendances();

        $this->command->info('✅ تم إضافة بيانات الديمو بنجاح!');
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'إلكترونيات',      'name_en' => 'Electronics',    'code' => 'ELEC'],
            ['name' => 'ملابس وأزياء',    'name_en' => 'Fashion',        'code' => 'FASH'],
            ['name' => 'أجهزة منزلية',   'name_en' => 'Home Appliances', 'code' => 'HOME'],
            ['name' => 'مواد غذائية',     'name_en' => 'Food',           'code' => 'FOOD'],
            ['name' => 'أدوات ومعدات',    'name_en' => 'Tools',          'code' => 'TOOL'],
            ['name' => 'مستحضرات تجميل', 'name_en' => 'Cosmetics',      'code' => 'COSM'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['code' => $cat['code']], array_merge($cat, ['is_active' => true]));
        }
        $this->command->info('   ✓ المجموعات: ' . count($categories));
    }

    private function seedProducts(): void
    {
        $products = [
            // إلكترونيات
            ['code'=>'EL-001','name'=>'شاشة LCD 24 بوصة','name_en'=>'LCD Monitor 24"','cat'=>'ELEC','unit'=>'قطعة','cost'=>2500,'sale'=>3200,'min'=>5],
            ['code'=>'EL-002','name'=>'لاب توب Core i5','name_en'=>'Laptop Core i5','cat'=>'ELEC','unit'=>'قطعة','cost'=>12000,'sale'=>15000,'min'=>3],
            ['code'=>'EL-003','name'=>'طابعة ليزر','name_en'=>'Laser Printer','cat'=>'ELEC','unit'=>'قطعة','cost'=>3500,'sale'=>4500,'min'=>2],
            ['code'=>'EL-004','name'=>'كيبورد وماوس لاسلكي','name_en'=>'Wireless Keyboard & Mouse','cat'=>'ELEC','unit'=>'طقم','cost'=>350,'sale'=>550,'min'=>10],
            ['code'=>'EL-005','name'=>'فلاشة USB 64GB','name_en'=>'USB Flash 64GB','cat'=>'ELEC','unit'=>'قطعة','cost'=>80,'sale'=>150,'min'=>20],
            // ملابس
            ['code'=>'FA-001','name'=>'تيشيرت قطن رجالي','name_en'=>'Men Cotton T-Shirt','cat'=>'FASH','unit'=>'قطعة','cost'=>80,'sale'=>150,'min'=>30],
            ['code'=>'FA-002','name'=>'بنطلون جينز','name_en'=>'Jeans Pants','cat'=>'FASH','unit'=>'قطعة','cost'=>200,'sale'=>380,'min'=>20],
            ['code'=>'FA-003','name'=>'فستان سواريه','name_en'=>'Evening Dress','cat'=>'FASH','unit'=>'قطعة','cost'=>450,'sale'=>850,'min'=>10],
            // أجهزة منزلية
            ['code'=>'HO-001','name'=>'غسالة أوتوماتيك 7 كيلو','name_en'=>'Washing Machine 7KG','cat'=>'HOME','unit'=>'قطعة','cost'=>4500,'sale'=>6500,'min'=>2],
            ['code'=>'HO-002','name'=>'ثلاجة نوفروست 16 قدم','name_en'=>'No Frost Fridge 16ft','cat'=>'HOME','unit'=>'قطعة','cost'=>7000,'sale'=>9500,'min'=>2],
            ['code'=>'HO-003','name'=>'مكيف سبليت 1.5 حصان','name_en'=>'Split AC 1.5HP','cat'=>'HOME','unit'=>'قطعة','cost'=>8000,'sale'=>11000,'min'=>3],
            // مواد غذائية
            ['code'=>'FO-001','name'=>'زيت ذرة 1 لتر','name_en'=>'Corn Oil 1L','cat'=>'FOOD','unit'=>'علبة','cost'=>35,'sale'=>55,'min'=>50],
            ['code'=>'FO-002','name'=>'أرز بسمتي 5 كيلو','name_en'=>'Basmati Rice 5KG','cat'=>'FOOD','unit'=>'كيس','cost'=>80,'sale'=>120,'min'=>40],
            ['code'=>'FO-003','name'=>'سكر ناعم 1 كيلو','name_en'=>'Fine Sugar 1KG','cat'=>'FOOD','unit'=>'كيس','cost'=>22,'sale'=>35,'min'=>60],
            // أدوات
            ['code'=>'TO-001','name'=>'طقم مفاتيح ربط','name_en'=>'Wrench Set','cat'=>'TOOL','unit'=>'طقم','cost'=>150,'sale'=>280,'min'=>15],
            ['code'=>'TO-002','name'=>'مثقاب كهربائي','name_en'=>'Electric Drill','cat'=>'TOOL','unit'=>'قطعة','cost'=>450,'sale'=>750,'min'=>5],
        ];

        $catIds = Category::pluck('id', 'code');
        foreach ($products as $p) {
            Product::firstOrCreate(['code' => $p['code']], [
                'name'         => $p['name'],
                'name_en'      => $p['name_en'],
                'category_id'  => $catIds[$p['cat']] ?? null,
                'unit'         => $p['unit'],
                'cost_price'   => $p['cost'],
                'sale_price'   => $p['sale'],
                'sale_price_2' => $p['sale'] * 0.95,
                'sale_price_3' => $p['sale'] * 0.90,
                'min_quantity' => $p['min'],
                'is_active'    => true,
            ]);
        }
        $this->command->info('   ✓ الأصناف: ' . count($products));
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['code'=>'CU-001','name'=>'شركة النيل للتجارة','phone'=>'01001234567','email'=>'info@nile.com','type'=>'company','address'=>'القاهرة، مصر الجديدة','tax_number'=>'123456789','credit_limit'=>50000],
            ['code'=>'CU-002','name'=>'مؤسسة الأهرام','phone'=>'01112345678','email'=>'sales@ahram.com','type'=>'company','address'=>'الجيزة، الدقي','tax_number'=>'987654321','credit_limit'=>30000],
            ['code'=>'CU-003','name'=>'محمد أحمد علي','phone'=>'01223456789','email'=>'mohamed@gmail.com','type'=>'individual','address'=>'الإسكندرية','credit_limit'=>5000],
            ['code'=>'CU-004','name'=>'شركة الوطن للمقاولات','phone'=>'01334567890','email'=>'info@watan.com','type'=>'company','address'=>'القاهرة، المعادي','tax_number'=>'456789123','credit_limit'=>100000],
            ['code'=>'CU-005','name'=>'أحمد محمود حسن','phone'=>'01445678901','email'=>'ahmed@hotmail.com','type'=>'individual','address'=>'الجيزة','credit_limit'=>3000],
            ['code'=>'CU-006','name'=>'مجموعة الفجر التجارية','phone'=>'01556789012','email'=>'info@fagr.com','type'=>'company','address'=>'القاهرة، مدينة نصر','tax_number'=>'789123456','credit_limit'=>75000],
            ['code'=>'CU-007','name'=>'سارة محمد إبراهيم','phone'=>'01667890123','email'=>'sara@gmail.com','type'=>'individual','address'=>'الإسكندرية، سيدي بشر','credit_limit'=>2000],
            ['code'=>'CU-008','name'=>'شركة الربيع للتوزيع','phone'=>'01778901234','email'=>'info@rabie.com','type'=>'company','address'=>'الإسكندرية','tax_number'=>'321654987','credit_limit'=>60000],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(['code' => $c['code']], array_merge($c, ['is_active' => true, 'opening_balance' => 0, 'balance_type' => 'debit']));
        }
        $this->command->info('   ✓ العملاء: ' . count($customers));
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['code'=>'SU-001','name'=>'شركة سامسونج مصر','phone'=>'0222345678','email'=>'info@samsung-eg.com','address'=>'القاهرة، النزهة','tax_number'=>'111222333'],
            ['code'=>'SU-002','name'=>'مصنع الغزل والنسيج','phone'=>'0333456789','email'=>'sales@textile.com','address'=>'المحلة الكبرى','tax_number'=>'444555666'],
            ['code'=>'SU-003','name'=>'شركة كارفور للتوريد','phone'=>'0444567890','email'=>'supply@carrefour.com','address'=>'القاهرة، التجمع','tax_number'=>'777888999'],
            ['code'=>'SU-004','name'=>'مستودع الأدوات الكهربائية','phone'=>'0555678901','email'=>'info@tools.com','address'=>'الجيزة، فيصل','tax_number'=>'222333444'],
            ['code'=>'SU-005','name'=>'شركة LG للإلكترونيات','phone'=>'0666789012','email'=>'info@lg-eg.com','address'=>'القاهرة، مصر الجديدة','tax_number'=>'555666777'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['code' => $s['code']], array_merge($s, ['is_active' => true, 'opening_balance' => 0, 'balance_type' => 'credit']));
        }
        $this->command->info('   ✓ الموردين: ' . count($suppliers));
    }

    private function seedEmployees(): void
    {
        $employees = [
            ['code'=>'EM-001','name'=>'أحمد محمد السيد','name_en'=>'Ahmed Mohamed','national_id'=>'29501010100011','phone'=>'01001111111','dept'=>'الإدارة','job'=>'مدير عام','salary'=>15000,'type'=>'full_time'],
            ['code'=>'EM-002','name'=>'محمد علي حسن','name_en'=>'Mohamed Ali','national_id'=>'29602020200022','phone'=>'01112222222','dept'=>'المحاسبة','job'=>'محاسب أول','salary'=>8000,'type'=>'full_time'],
            ['code'=>'EM-003','name'=>'سارة أحمد إبراهيم','name_en'=>'Sara Ahmed','national_id'=>'29703030300033','phone'=>'01223333333','dept'=>'المبيعات','job'=>'مندوب مبيعات','salary'=>6000,'type'=>'full_time'],
            ['code'=>'EM-004','name'=>'خالد محمود عبدالله','name_en'=>'Khaled Mahmoud','national_id'=>'29804040400044','phone'=>'01334444444','dept'=>'المخازن','job'=>'أمين مخزن','salary'=>5500,'type'=>'full_time'],
            ['code'=>'EM-005','name'=>'نورا حسن محمد','name_en'=>'Nora Hassan','national_id'=>'29905050500055','phone'=>'01445555555','dept'=>'المبيعات','job'=>'كاشير','salary'=>4500,'type'=>'full_time'],
            ['code'=>'EM-006','name'=>'عمر فاروق إسماعيل','name_en'=>'Omar Farouk','national_id'=>'30006060600066','phone'=>'01556666666','dept'=>'تقنية المعلومات','job'=>'مدير تقنية','salary'=>12000,'type'=>'full_time'],
            ['code'=>'EM-007','name'=>'منى سعد الدين','name_en'=>'Mona Saad','national_id'=>'30107070700077','phone'=>'01667777777','dept'=>'الموارد البشرية','job'=>'مدير HR','salary'=>9000,'type'=>'full_time'],
        ];

        foreach ($employees as $e) {
            Employee::firstOrCreate(['code' => $e['code']], [
                'name'            => $e['name'],
                'name_en'         => $e['name_en'],
                'national_id'     => $e['national_id'],
                'phone'           => $e['phone'],
                'department'      => $e['dept'],
                'job_title'       => $e['job'],
                'basic_salary'    => $e['salary'],
                'employment_type' => $e['type'],
                'hire_date'       => now()->subMonths(rand(6,36)),
                'status'          => 'active',
                'working_hours'   => 8,
            ]);
        }
        $this->command->info('   ✓ الموظفين: ' . count($employees));
    }

  private function seedInvoices(): void
    {
        $customers = Customer::all();
        $products  = Product::all();
        $count     = 0;

        // ابدأ من أعلى رقم موجود + 1
        $lastInv  = Invoice::withoutGlobalScopes()->where('type', 'sale')->orderByDesc('id')->first();
        $startNum = $lastInv ? (intval(substr($lastInv->invoice_number, -6)) + 1) : 1;

        for ($i = 0; $i < 20; $i++) {
            $invoiceNumber = 'INV-' . str_pad($startNum + $i, 6, '0', STR_PAD_LEFT);

            // تخطى لو موجود
            if (Invoice::withoutGlobalScopes()->where('invoice_number', $invoiceNumber)->exists()) {
                continue;
            }

            $customer    = $customers->random();
            $invoiceDate = now()->subDays(rand(1, 60));
            $payType     = ['cash', 'credit', 'cash', 'cash'][rand(0, 3)];
            $status      = $payType === 'cash' ? 'paid' : ['confirmed', 'partial'][rand(0, 1)];

            $invoice = Invoice::create([
                'invoice_number'   => $invoiceNumber,
                'type'             => 'sale',
                'payment_type'     => $payType,
                'customer_id'      => $customer->id,
                'invoice_date'     => $invoiceDate,
                'subtotal'         => 0,
                'discount_amount'  => 0,
                'discount_percent' => 0,
                'tax_amount'       => 0,
                'tax_percent'      => 0,
                'total'            => 0,
                'paid_amount'      => 0,
                'remaining_amount' => 0,
                'status'           => $status,
                'created_by'       => 1,
                'created_at'       => $invoiceDate,
            ]);

            $subtotal         = 0;
            $numItems         = rand(1, 4);
            $selectedProducts = $products->random($numItems);

            foreach ($selectedProducts as $product) {
                $qty   = rand(1, 10);
                $price = $product->sale_price;
                $total = $qty * $price;
                $subtotal += $total;

                InvoiceItem::create([
                    'invoice_id'   => $invoice->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'unit'         => $product->unit,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'cost_price'   => $product->cost_price,
                    'total'        => $total,
                ]);
            }

            $paidAmount = $status === 'paid' ? $subtotal : ($status === 'partial' ? $subtotal * 0.5 : 0);

            $invoice->update([
                'subtotal'         => $subtotal,
                'total'            => $subtotal,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $subtotal - $paidAmount,
            ]);

            $count++;
        }

        $this->command->info("   ✓ الفواتير: {$count}");
    }

    private function seedAttendances(): void
    {
        $employees = Employee::all();
        $count     = 0;

        foreach ($employees as $employee) {
            for ($day = 29; $day >= 0; $day--) {
                $date = now()->subDays($day)->toDateString();
                $dayOfWeek = now()->subDays($day)->dayOfWeek;

                if ($dayOfWeek === 5) continue; // الجمعة إجازة

                $rand   = rand(1, 10);
                $status = $rand <= 7 ? 'present' : ($rand === 8 ? 'late' : ($rand === 9 ? 'vacation' : 'absent'));
                $checkIn  = $status === 'present' ? '09:00:00' : ($status === 'late' ? '09:'.rand(15,59).':00' : null);
                $checkOut = $checkIn ? '17:00:00' : null;
                $workHours = $checkIn ? 8 : 0;

                Attendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date],
                    ['check_in' => $checkIn, 'check_out' => $checkOut, 'working_hours' => $workHours, 'status' => $status, 'late_minutes' => $status === 'late' ? rand(15,59) : 0]
                );
                $count++;
            }
        }
        $this->command->info("   ✓ سجلات الحضور: {$count}");
    }
}