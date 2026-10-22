@php
    $attachments = \App\Models\Attachment::where('attachable_type', $modelType)
        ->where('attachable_id', $modelId)
        ->with('uploadedBy')
        ->orderByDesc('created_at')
        ->get();
@endphp

<div style="background:#0d1117;border:1px solid #1e2d45;border-radius:10px;padding:16px;margin-top:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:#94a3b8;display:flex;align-items:center;gap:6px">
            📎 المرفقات
            @if($attachments->count() > 0)
            <span style="background:rgba(124,109,255,.15);color:#a78bfa;padding:2px 8px;border-radius:10px;font-size:11px">{{ $attachments->count() }}</span>
            @endif
        </div>
        <button onclick="document.getElementById('att-file-{{ $modelId }}').click()"
            style="background:rgba(124,109,255,.1);border:1px solid rgba(124,109,255,.2);color:#a78bfa;padding:6px 14px;border-radius:7px;cursor:pointer;font-size:12px;font-weight:700;font-family:Cairo,sans-serif">
            + إضافة مرفق
        </button>
    </div>

    {{-- Upload Input --}}
    <input type="file" id="att-file-{{ $modelId }}" style="display:none"
        accept=".pdf,.jpg,.jpeg,.png,.gif,.xlsx,.xls,.doc,.docx"
        onchange="uploadAttachment(this, '{{ $modelType }}', {{ $modelId }}, '{{ csrf_token() }}')">

    {{-- Upload Progress --}}
    <div id="att-progress-{{ $modelId }}" style="display:none;margin-bottom:10px">
        <div style="background:#1e2d45;border-radius:4px;height:4px;overflow:hidden">
            <div id="att-bar-{{ $modelId }}" style="height:100%;background:#7c6dff;width:0%;transition:width .3s"></div>
        </div>
        <div style="font-size:11px;color:#475569;margin-top:4px">جاري الرفع...</div>
    </div>

    {{-- Attachments List --}}
    <div id="att-list-{{ $modelId }}">
        @forelse($attachments as $att)
        <div id="att-item-{{ $att->id }}" style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:#161c2d;border:1px solid #1a2440;border-radius:8px;margin-bottom:6px">
            <div style="font-size:22px;flex-shrink:0">{{ $att->icon }}</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;color:#e2e8f0;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $att->file_name }}</div>
                <div style="font-size:10px;color:#334155;margin-top:2px">
                    {{ $att->file_size_formatted }} · {{ $att->uploadedBy?->name ?? 'System' }} · {{ $att->created_at->diffForHumans() }}
                    @if($att->description) · <span style="color:#475569">{{ $att->description }}</span>@endif
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0">
                <a href="/attachments/{{ $att->id }}/download" target="_blank"
                    style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#4ade80;padding:5px 10px;border-radius:6px;font-size:11px;text-decoration:none;font-weight:700">
                    ⬇️ تحميل
                </a>
                @if($att->file_type === 'image' || $att->file_type === 'pdf')
                <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                    style="background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.2);color:#60a5fa;padding:5px 10px;border-radius:6px;font-size:11px;text-decoration:none;font-weight:700">
                    👁️ عرض
                </a>
                @endif
                <button onclick="deleteAttachment({{ $att->id }}, '{{ csrf_token() }}')"
                    style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#f87171;padding:5px 10px;border-radius:6px;font-size:11px;cursor:pointer;font-family:Cairo,sans-serif;font-weight:700">
                    🗑️
                </button>
            </div>
        </div>
        @empty
        <div id="att-empty-{{ $modelId }}" style="text-align:center;padding:20px;color:#1e2d45;font-size:13px">
            لا توجد مرفقات — اضغط "إضافة مرفق" لرفع ملف
        </div>
        @endforelse
    </div>
</div>

<script>
function uploadAttachment(input, modelType, modelId, token) {
    var file = input.files[0];
    if (!file) return;

    var progress = document.getElementById('att-progress-' + modelId);
    var bar      = document.getElementById('att-bar-' + modelId);
    var list     = document.getElementById('att-list-' + modelId);
    var empty    = document.getElementById('att-empty-' + modelId);

    progress.style.display = 'block';

    var form = new FormData();
    form.append('file', file);
    form.append('attachable_type', modelType);
    form.append('attachable_id', modelId);
    form.append('_token', token);

    var xhr = new XMLHttpRequest();
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            bar.style.width = Math.round(e.loaded / e.total * 100) + '%';
        }
    };

    xhr.onload = function() {
        progress.style.display = 'none';
        bar.style.width = '0%';
        input.value = '';

        if (xhr.status === 200) {
            var att = JSON.parse(xhr.responseText);
            if (empty) empty.style.display = 'none';

            var html = '<div id="att-item-' + att.id + '" style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:#161c2d;border:1px solid #1a2440;border-radius:8px;margin-bottom:6px">' +
                '<div style="font-size:22px;flex-shrink:0">' + att.icon + '</div>' +
                '<div style="flex:1;min-width:0">' +
                '<div style="font-size:13px;color:#e2e8f0;font-weight:600">' + att.file_name + '</div>' +
                '<div style="font-size:10px;color:#334155;margin-top:2px">' + att.file_size + '</div>' +
                '</div>' +
                '<div style="display:flex;gap:6px">' +
                '<a href="/attachments/' + att.id + '/download" target="_blank" style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#4ade80;padding:5px 10px;border-radius:6px;font-size:11px;text-decoration:none;font-weight:700">⬇️ تحميل</a>' +
                '<button onclick="deleteAttachment(' + att.id + ', \'' + token + '\')" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#f87171;padding:5px 10px;border-radius:6px;font-size:11px;cursor:pointer;font-weight:700">🗑️</button>' +
                '</div></div>';

            list.insertAdjacentHTML('afterbegin', html);
        } else {
            alert('خطأ في رفع الملف');
        }
    };

    xhr.open('POST', '/attachments/upload');
    xhr.send(form);
}

function deleteAttachment(id, token) {
    if (!confirm('حذف المرفق؟')) return;

    fetch('/attachments/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'}
    }).then(function(r) {
        if (r.ok) {
            var el = document.getElementById('att-item-' + id);
            if (el) el.remove();
        }
    });
}
</script>