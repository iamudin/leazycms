@php
    $tracker_id = $tracker_id ?? ('lz-tracker-' . $module . '-' . uniqid());
    $action = $action ?? post_track_url($module);
    $title = $title ?? ('Tracking & Cek Data ' . ($mod->title ?? ucfirst($module)));
    $description = $description ?? 'Masukkan data identifikasi (seperti NIK, NISN, atau NIP) untuk melakukan pengecekan status data.';
    $by = $by ?? ($field ?? '');
    $byString = is_array($by) ? implode(',', $by) : $by;
    $field_label = $field_label ?? 'Nomor Identitas (NIK / NIP / NISN / No. Pendaftaran)';
    $placeholder = $placeholder ?? 'Masukkan nomor identitas...';
    $submit_text = $submit_text ?? 'Cek Status';
    $ajax = $ajax ?? true;
    $show_fields = $show_fields ?? [];
    $container_class = $class ?? '';
@endphp

<div class="lz-tracker-wrapper {{ $container_class }}" id="{{ $tracker_id }}-wrapper">
    <style>
        .lz-tracker-card {
            background-color: var(--lz-track-bg, #ffffff);
            border: 1px solid var(--lz-track-border, #e2e8f0);
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            font-family: inherit;
        }
        .dark .lz-tracker-card, [data-theme="dark"] .lz-tracker-card {
            background-color: var(--lz-track-bg-dark, #1e293b);
            border-color: var(--lz-track-border-dark, #334155);
            color: #f8fafc;
        }
        .lz-tracker-header {
            margin-bottom: 1.5rem;
        }
        .lz-tracker-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 0.375rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .lz-tracker-title, [data-theme="dark"] .lz-tracker-title {
            color: #f1f5f9;
        }
        .lz-tracker-desc {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }
        .dark .lz-tracker-desc, [data-theme="dark"] .lz-tracker-desc {
            color: #94a3b8;
        }
        .lz-tracker-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .lz-tracker-input-group {
                flex-direction: row;
                align-items: stretch;
            }
        }
        .lz-tracker-input-wrap {
            position: relative;
            flex: 1;
        }
        .lz-tracker-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            font-size: 0.9375rem;
            color: #1e293b;
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.75rem;
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .dark .lz-tracker-input, [data-theme="dark"] .lz-tracker-input {
            background-color: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }
        .lz-tracker-input:focus {
            background-color: #ffffff;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }
        .dark .lz-tracker-input:focus, [data-theme="dark"] .lz-tracker-input:focus {
            background-color: #0f172a;
        }
        .lz-tracker-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.25rem;
            height: 1.25rem;
            color: #94a3b8;
            pointer-events: none;
        }
        .lz-tracker-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #ffffff;
            background-color: #0d9488;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.25);
            white-space: nowrap;
        }
        .lz-tracker-btn:hover {
            background-color: #0f766e;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(13, 148, 136, 0.35);
        }
        .lz-tracker-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .lz-tracker-result {
            margin-top: 1.5rem;
            animation: lzFadeIn 0.3s ease-out;
        }
        @keyframes lzFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .lz-result-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .dark .lz-result-card, [data-theme="dark"] .lz-result-card {
            background: #0f172a;
            border-color: #334155;
        }
        .lz-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.3125rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lz-badge-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .dark .lz-badge-success, [data-theme="dark"] .lz-badge-success {
            background-color: rgba(6, 95, 70, 0.3);
            color: #6ee7b7;
            border-color: #065f46;
        }
        .lz-badge-warning {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .dark .lz-badge-warning, [data-theme="dark"] .lz-badge-warning {
            background-color: rgba(146, 64, 14, 0.3);
            color: #fcd34d;
            border-color: #78350f;
        }
        .lz-badge-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .dark .lz-badge-danger, [data-theme="dark"] .lz-badge-danger {
            background-color: rgba(153, 27, 27, 0.3);
            color: #fca5a5;
            border-color: #991b1b;
        }
        .lz-badge-info {
            background-color: #f0fdfa;
            color: #0f766e;
            border: 1px solid #99f6e4;
        }
        .dark .lz-badge-info, [data-theme="dark"] .lz-badge-info {
            background-color: rgba(15, 118, 110, 0.3);
            color: #5eead4;
            border-color: #115e59;
        }
        .lz-track-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        .lz-track-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .lz-track-table tr, [data-theme="dark"] .lz-track-table tr {
            border-bottom-color: #1e293b;
        }
        .lz-track-table td {
            padding: 0.625rem 0.25rem;
        }
        .lz-track-label {
            color: #64748b;
            width: 38%;
            font-weight: 500;
        }
        .dark .lz-track-label, [data-theme="dark"] .lz-track-label {
            color: #94a3b8;
        }
        .lz-track-val {
            font-weight: 600;
            color: #0f172a;
        }
        .dark .lz-track-val, [data-theme="dark"] .lz-track-val {
            color: #f1f5f9;
        }
    </style>

    <div class="lz-tracker-card">
        @if($title)
            <div class="lz-tracker-header">
                <h3 class="lz-tracker-title">
                    <svg style="width: 1.35rem; height: 1.35rem; color: #0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>{{ $title }}</span>
                </h3>
                @if($description)
                    <p class="lz-tracker-desc">{{ $description }}</p>
                @endif
            </div>
        @endif

        <form action="{{ $action }}" method="GET" id="{{ $tracker_id }}-form" data-ajax="{{ $ajax ? 'true' : 'false' }}">
            @if($byString)
                <input type="hidden" name="by" value="{{ $byString }}">
            @endif
            @if(!empty($show_fields))
                <input type="hidden" name="show" value="{{ is_array($show_fields) ? implode(',', $show_fields) : $show_fields }}">
            @endif

            <label for="{{ $tracker_id }}-input" style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.375rem; color: #475569;">
                {{ $field_label }}
            </label>
            <div class="lz-tracker-input-group">
                <div class="lz-tracker-input-wrap">
                    <svg class="lz-tracker-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        name="q" 
                        id="{{ $tracker_id }}-input" 
                        placeholder="{{ $placeholder }}" 
                        value="{{ request('q') ?? request('keyword') ?? '' }}" 
                        class="lz-tracker-input" 
                        required 
                        autocomplete="off"
                    >
                </div>
                <button type="submit" class="lz-tracker-btn" id="{{ $tracker_id }}-btn">
                    <span class="lz-btn-text">{{ $submit_text }}</span>
                    <span class="lz-btn-spinner" style="display: none;">
                        <svg style="width: 1.125rem; height: 1.125rem; animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none">
                            <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mengecek...
                    </span>
                </button>
            </div>
        </form>

        <!-- Result Container -->
        <div class="lz-tracker-result" id="{{ $tracker_id }}-result" style="{{ (session('track_result') || session('track_error')) ? '' : 'display: none;' }}">
            @if(session('track_error'))
                <div class="lz-alert lz-alert-danger" style="margin-top: 1rem;">
                    <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>{{ session('track_error') }}</span>
                </div>
            @endif

            @if(session('track_result'))
                {!! session('track_result') !!}
            @endif
        </div>
    </div>

    @if($ajax)
        <script>
            (function() {
                var form = document.getElementById('{{ $tracker_id }}-form');
                if (!form) return;
                var resultBox = document.getElementById('{{ $tracker_id }}-result');
                var submitBtn = document.getElementById('{{ $tracker_id }}-btn');
                var btnText = submitBtn ? submitBtn.querySelector('.lz-btn-text') : null;
                var btnSpinner = submitBtn ? submitBtn.querySelector('.lz-btn-spinner') : null;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var q = form.querySelector('[name="q"]').value.trim();
                    if (!q) return;

                    if (submitBtn) submitBtn.disabled = true;
                    if (btnText) btnText.style.display = 'none';
                    if (btnSpinner) btnSpinner.style.display = 'inline-flex';

                    var url = new URL(form.action, window.location.origin);
                    url.searchParams.set('q', q);
                    var by = form.querySelector('[name="by"]');
                    if (by && by.value) url.searchParams.set('by', by.value);
                    var show = form.querySelector('[name="show"]');
                    if (show && show.value) url.searchParams.set('show', show.value);

                    fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        resultBox.style.display = 'block';
                        if (data.status === 'success' && data.found) {
                            resultBox.innerHTML = data.html;
                        } else {
                            var errMsg = data.message || 'Data tidak ditemukan. Silakan periksa kembali nomor yang Anda masukkan.';
                            resultBox.innerHTML = '<div class="lz-alert lz-alert-danger" style="margin-top: 1rem;"><svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg><span class="lz-err-msg"></span></div>';
                            resultBox.querySelector('.lz-err-msg').textContent = errMsg;
                        }
                    })
                    .catch(function(err) {
                        resultBox.style.display = 'block';
                        resultBox.innerHTML = '<div class="lz-alert lz-alert-danger" style="margin-top: 1rem;"><span>Gagal memeriksa data. Silakan coba kembali.</span></div>';
                    })
                    .finally(function() {
                        if (submitBtn) submitBtn.disabled = false;
                        if (btnText) btnText.style.display = 'inline';
                        if (btnSpinner) btnSpinner.style.display = 'none';
                    });
                });
            })();
        </script>
    @endif
</div>
