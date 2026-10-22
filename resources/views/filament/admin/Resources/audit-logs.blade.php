<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important}
.audit-wrap{background:#0d1117;border:1px solid #1e2d45;border-radius:12px;overflow:hidden}
.audit-filters{display:flex;gap:10px;padding:14px 16px;border-bottom:1px solid #1e2d45;background:#161c2d;flex-wrap:wrap}
.audit-search{flex:1;min-width:200px;background:rgba(255,255,255,.06);border:1px solid #1e2d45;border-radius:8px;padding:8px 12px;color:#e2e8f0;font-size:13px;font-family:Cairo,sans-serif;outline:none}
.audit-select{background:rgba(255,255,255,.06);border:1px solid #1e2d45;border-radius:8px;padding:8px 12px;color:#e2e8f0;font-size:13px;font-family:Cairo,sans-serif;outline:none}
.audit-table{width:100%;border-collapse:collapse;font-size:12px}
.audit-table th{text-align:right;padding:10px 14px;font-weight:700;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px;letter-spacing:.3px;background:#0d1117}
.audit-table td{padding:10px 14px;border-bottom:1px solid #0a0f1a;vertical-align:top}
.audit-table tr:hover td{background:rgba(255,255,255,.015)}
.event-badge{padding:3px 8px;border-radius:5px;font-size:11px;font-weight:700;white-space:nowrap}
.ev-created{background:rgba(34,197,94,.12);color:#4ade80}
.ev-updated{background:rgba(59,130,246,.12);color:#60a5fa}
.ev-deleted{background:rgba(239,68,68,.12);color:#f87171}
.ev-restored{background:rgba(168,85,247,.12);color:#d8b4fe}
.ev-login{background:rgba(251,191,36,.12);color:#fbbf24}
.changes-box{background:#0a0f1a;border-radius:6px;padding:6px 8px;margin-top:4px;font-size:10px;font-family:monospace}
.change-row{display:flex;gap:6px;margin-bottom:2px}
.change-field{color:#475569;min-width:80px}
.change-old{color:#f87171;text-decoration:line-through}
.change-arrow{color:#334155}
.change-new{color:#4ade80}
.empty-state{text-align:center;padding:60px;color:#1e2d45;font-size:14px}
</style>

<div class="audit-wrap">
    {{-- Filters --}}
    <div class="audit-filters">
        <input type="text" wire:model.live="search" class="audit-search" placeholder="🔍 بحث بالمستخدم أو السجل...">
        <select wire:model.live="filterEvent" class="audit-select">
            <option value="">كل العمليات</option>
            <option value="created">✅ إنشاء</option>
            <option value="updated">✏️ تعديل</option>
            <option value="deleted">🗑️ حذف</option>
            <option value="restored">♻️ استعادة</option>
            <option value="login">🔐 دخول</option>
        </select>
        <select wire:model.live="filterModel" class="audit-select">
            <option value="">كل النماذج</option>
            <option value="Invoice">فواتير</option>
            <option value="Product">أصناف</option>
            <option value="Customer">عملاء</option>
            <option value="Supplier">موردين</option>
            <option value="JournalEntry">قيود</option>
            <option value="Employee">موظفين</option>
        </select>
    </div>

    {{-- Table --}}
    @php $logs = $this->getLogs(); @endphp

    @if($logs->isEmpty())
    <div class="empty-state">لا توجد سجلات بعد</div>
    @else
    <div style="overflow-x:auto">
    <table class="audit-table">
        <thead>
            <tr>
                <th>التوقيت</th>
                <th>المستخدم</th>
                <th>العملية</th>
                <th>النموذج</th>
                <th>السجل</th>
                <th>التغييرات</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td style="color:#475569;white-space:nowrap">
                    <div>{{ $log->created_at->format('d/m/Y') }}</div>
                    <div style="color:#334155;font-size:10px">{{ $log->created_at->format('H:i:s') }}</div>
                    <div style="font-size:10px;color:#1e2d45">{{ $log->created_at->diffForHumans() }}</div>
                </td>
                <td>
                    <div style="color:#e2e8f0;font-weight:600">{{ $log->user_name ?? 'System' }}</div>
                </td>
                <td>
                    @php
                        $evClass = match($log->event) {
                            'created'  => 'ev-created',
                            'updated'  => 'ev-updated',
                            'deleted'  => 'ev-deleted',
                            'restored' => 'ev-restored',
                            'login'    => 'ev-login',
                            default    => '',
                        };
                    @endphp
                    <span class="event-badge {{ $evClass }}">{{ $log->event_label }}</span>
                </td>
                <td style="color:#64748b;white-space:nowrap">{{ $log->model_label }}</td>
                <td>
                    <div style="color:#7c6dff;font-weight:700;font-family:monospace;font-size:12px">{{ $log->auditable_label }}</div>
                </td>
                <td>
                    @if($log->old_values || $log->new_values)
                    <div class="changes-box">
                        @foreach(array_keys(array_merge($log->old_values ?? [], $log->new_values ?? [])) as $field)
                        @php
                            $old = $log->old_values[$field] ?? null;
                            $new = $log->new_values[$field] ?? null;
                            if ($old === $new) continue;
                        @endphp
                        <div class="change-row">
                            <span class="change-field">{{ $field }}:</span>
                            @if($old !== null)<span class="change-old">{{ Str::limit((string)$old, 20) }}</span><span class="change-arrow">→</span>@endif
                            @if($new !== null)<span class="change-new">{{ Str::limit((string)$new, 20) }}</span>@endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <span style="color:#1e2d45;font-size:11px">—</span>
                    @endif
                </td>
                <td style="color:#334155;font-size:11px;font-family:monospace">{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    <div style="padding:12px 16px;border-top:1px solid #1e2d45;display:flex;justify-content:space-between;align-items:center">
        <div style="font-size:12px;color:#475569">
            إجمالي {{ $logs->total() }} سجل · صفحة {{ $logs->currentPage() }} من {{ $logs->lastPage() }}
        </div>
        <div style="display:flex;gap:6px">
            @if($logs->onFirstPage())
            <span style="padding:5px 12px;border-radius:6px;background:rgba(255,255,255,.04);color:#334155;font-size:12px">← السابق</span>
            @else
            <button wire:click="previousPage" style="padding:5px 12px;border-radius:6px;background:rgba(124,109,255,.1);border:1px solid rgba(124,109,255,.2);color:#a78bfa;font-size:12px;cursor:pointer;font-family:Cairo,sans-serif">← السابق</button>
            @endif

            @if($logs->hasMorePages())
            <button wire:click="nextPage" style="padding:5px 12px;border-radius:6px;background:rgba(124,109,255,.1);border:1px solid rgba(124,109,255,.2);color:#a78bfa;font-size:12px;cursor:pointer;font-family:Cairo,sans-serif">التالي →</button>
            @else
            <span style="padding:5px 12px;border-radius:6px;background:rgba(255,255,255,.04);color:#334155;font-size:12px">التالي →</span>
            @endif
        </div>
    </div>
    @endif
</div>
</x-filament-panels::page>