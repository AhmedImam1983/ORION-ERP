<x-filament-panels::page>
    <div style="direction:rtl;">
        <div style="display:grid;grid-template-columns:minmax(0,1fr) 390px;gap:18px;align-items:start;">

            {{-- Search area --}}
            <section>
                <div style="border:1px solid rgba(96,165,250,.20);border-radius:18px;background:linear-gradient(145deg,#0f1b2e,#111827);padding:20px;box-shadow:0 10px 35px rgba(0,0,0,.20);">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px;">
                        <div>
                            <div style="font-size:22px;font-weight:850;color:#f8fafc;">ابحث... وبيع</div>
                            <div style="margin-top:5px;color:#94a3b8;font-size:13px;">ابحث في الكود، الاسم، رقم المرجع، الباركود أو العلامة التجارية</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="padding:7px 10px;border-radius:10px;background:#172554;color:#93c5fd;font-size:12px;font-weight:700;">بحث فوري</span>
                            <span style="padding:7px 10px;border-radius:10px;background:#172554;color:#a78bfa;font-size:12px;font-weight:700;">بيع سريع</span>
                        </div>
                    </div>

                    <div style="position:relative;">
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="search"
                            autofocus
                            placeholder="اكتب رقم القطعة أو الاسم أو الباركود..."
                            style="width:100%;height:62px;border-radius:14px;border:1px solid #334155;background:#0b1424;color:#f8fafc;padding:0 56px 0 18px;font-size:17px;outline:none;"
                        >
                        <div style="position:absolute;right:18px;top:18px;font-size:24px;color:#a78bfa;">⌕</div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:12px;">
                        <select wire:model.live="brandId" style="height:44px;border-radius:10px;border:1px solid #334155;background:#111827;color:#e5e7eb;padding:0 12px;">
                            <option value="">كل العلامات التجارية</option>
                            @foreach($this->getBrands() as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="categoryId" style="height:44px;border-radius:10px;border:1px solid #334155;background:#111827;color:#e5e7eb;padding:0 12px;">
                            <option value="">كل المجموعات</option>
                            @foreach($this->getCategories() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="warehouseId" style="height:44px;border-radius:10px;border:1px solid #334155;background:#111827;color:#e5e7eb;padding:0 12px;">
                            <option value="">كل المخازن</option>
                            @foreach($this->getWarehouses() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin:18px 2px 10px;">
                    <div style="color:#94a3b8;font-size:13px;">
                        @if(trim($search) !== '' || $brandId || $categoryId || $warehouseId)
                            النتائج المطابقة: <strong style="color:#e2e8f0;">{{ $this->getProducts()->count() }}</strong>
                        @else
                            ابدأ بالبحث عن صنف
                        @endif
                    </div>
                    <a href="/admin/products" style="color:#93c5fd;text-decoration:none;font-size:13px;">إدارة الأصناف ←</a>
                </div>

                @if(trim($search) === '' && !$brandId && !$categoryId && !$warehouseId)
                    <div style="min-height:370px;border:1px dashed #334155;border-radius:18px;display:flex;align-items:center;justify-content:center;text-align:center;background:rgba(15,23,42,.45);">
                        <div>
                            <div style="width:74px;height:74px;border-radius:20px;background:#1e1b4b;color:#a78bfa;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:34px;">⌕</div>
                            <div style="font-size:20px;font-weight:800;color:#e5e7eb;">جاهز للبحث</div>
                            <div style="margin-top:7px;color:#94a3b8;font-size:13px;">اكتب أي جزء من رقم القطعة أو الاسم وستظهر النتائج لحظيًا</div>
                        </div>
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
                        @forelse($this->getProducts() as $product)
                            @php
                                $location = $this->getLocationSummary($product);
                                $vehicle = $this->getVehicleSummary($product);
                            @endphp
                            <div style="border:1px solid rgba(96,165,250,.18);border-radius:16px;background:#111827;overflow:hidden;transition:.2s;">
                                <div style="height:110px;background:linear-gradient(135deg,#111827,#0b1424);display:flex;align-items:center;justify-content:center;">
                                    @if($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    @else
                                        <div style="font-size:42px;opacity:.45;">📦</div>
                                    @endif
                                </div>

                                <div style="padding:14px;">
                                    <div style="display:flex;justify-content:space-between;gap:8px;">
                                        <div style="font-size:12px;color:#64748b;">{{ $product->code }}</div>
                                        @if($product->brand)
                                            <span style="background:#1e293b;color:#93c5fd;padding:4px 7px;border-radius:7px;font-size:11px;">{{ $product->brand->name }}</span>
                                        @endif
                                    </div>

                                    <div style="margin-top:8px;font-size:15px;font-weight:800;color:#f8fafc;line-height:1.5;min-height:45px;">
                                        {{ $product->name }}
                                    </div>

                                    @if($product->part_number)
                                        <div style="margin-top:6px;color:#c4b5fd;font-size:12px;">{{ $product->part_number }}</div>
                                    @endif

                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
                                        <div style="padding:9px;border-radius:10px;background:#0b1424;">
                                            <div style="color:#64748b;font-size:10px;">الرصيد</div>
                                            <div style="color:#34d399;font-size:16px;font-weight:850;">{{ number_format($product->quantity, 3) }}</div>
                                        </div>
                                        <div style="padding:9px;border-radius:10px;background:#0b1424;">
                                            <div style="color:#64748b;font-size:10px;">سعر البيع</div>
                                            <div style="color:#f8fafc;font-size:15px;font-weight:800;">{{ number_format($product->sale_price, 2) }} ر.س</div>
                                        </div>
                                    </div>

                                    @if($location)
                                        <div style="margin-top:9px;color:#fbbf24;font-size:11px;">📍 {{ $location }}</div>
                                    @endif

                                    @if($vehicle)
                                        <div style="margin-top:7px;color:#94a3b8;font-size:11px;line-height:1.6;">🚗 {{ $vehicle }}</div>
                                    @endif

                                    <button
                                        wire:click="addToCart({{ $product->id }})"
                                        style="width:100%;margin-top:13px;height:42px;border:0;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#9333ea);color:#fff;font-weight:800;cursor:pointer;"
                                    >
                                        + إضافة للبيع
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div style="grid-column:1/-1;padding:60px;text-align:center;border:1px dashed #334155;border-radius:16px;color:#94a3b8;">لا توجد نتائج مطابقة.</div>
                        @endforelse
                    </div>
                @endif
            </section>

            {{-- Cart --}}
            <aside style="position:sticky;top:88px;border:1px solid rgba(167,139,250,.25);border-radius:18px;background:linear-gradient(155deg,#111827,#0b1220);overflow:hidden;">
                <div style="padding:18px 18px 14px;border-bottom:1px solid #273449;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:19px;font-weight:850;color:#f8fafc;">سلة البيع</div>
                        <div style="margin-top:4px;font-size:12px;color:#64748b;">{{ $this->cartCount }} قطعة</div>
                    </div>
                    @if($this->cartCount > 0)
                        <button wire:click="clearCart" style="background:transparent;border:0;color:#f87171;cursor:pointer;font-size:12px;">تفريغ</button>
                    @endif
                </div>

                <div style="padding:12px;max-height:440px;overflow:auto;">
                    @forelse($cart as $item)
                        <div style="padding:13px 0;border-bottom:1px solid rgba(51,65,85,.55);">
                            <div style="display:flex;justify-content:space-between;gap:10px;">
                                <div style="min-width:0;">
                                    <div style="font-size:13px;font-weight:800;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['name'] }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">{{ $item['brand'] ?: '—' }} · {{ $item['part_number'] ?: $item['code'] }}</div>
                                </div>
                                <button wire:click="removeCartItem({{ $item['product_id'] }})" style="border:0;background:none;color:#64748b;cursor:pointer;">×</button>
                            </div>

                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <button wire:click="decrementCartItem({{ $item['product_id'] }})" style="width:28px;height:28px;border:1px solid #334155;border-radius:8px;background:#0f172a;color:#e2e8f0;cursor:pointer;">−</button>
                                    <strong style="color:#f8fafc;min-width:20px;text-align:center;">{{ $item['quantity'] }}</strong>
                                    <button wire:click="incrementCartItem({{ $item['product_id'] }})" style="width:28px;height:28px;border:1px solid #334155;border-radius:8px;background:#0f172a;color:#e2e8f0;cursor:pointer;">+</button>
                                </div>
                                <strong style="color:#34d399;">{{ number_format($item['total'],2) }} ر.س</strong>
                            </div>
                        </div>
                    @empty
                        <div style="padding:70px 20px;text-align:center;color:#64748b;">
                            <div style="font-size:42px;opacity:.35;">🛒</div>
                            <div style="margin-top:10px;color:#94a3b8;font-weight:700;">السلة فاضية</div>
                            <div style="margin-top:5px;font-size:12px;">اختار صنف من نتائج البحث</div>
                        </div>
                    @endforelse
                </div>

                <div style="padding:15px;border-top:1px solid #273449;background:#0b1424;">
                    <div style="display:flex;justify-content:space-between;color:#94a3b8;font-size:12px;">
                        <span>عدد الأصناف</span>
                        <strong style="color:#e5e7eb;">{{ count($cart) }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                        <span style="font-size:14px;color:#cbd5e1;">الإجمالي</span>
                        <strong style="font-size:25px;color:#a78bfa;">{{ number_format($this->cartTotal,2) }} ر.س</strong>
                    </div>
                    <button
                        wire:click="notifySale"
                        style="width:100%;height:48px;margin-top:14px;border:0;border-radius:12px;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-size:15px;font-weight:900;cursor:pointer;"
                    >
                        إتمام البيع →
                    </button>
                    <div style="margin-top:8px;text-align:center;color:#64748b;font-size:10px;">المبيعات والفاتورة النهائية من Orion</div>
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
