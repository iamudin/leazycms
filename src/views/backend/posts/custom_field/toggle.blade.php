@php
    $fieldName = _us($r[0]);
    $currentVal = old($fieldName, isset($field[$fieldName]) ? $field[$fieldName] : ($r[1]->default ?? 'false'));
    $onLabel = is_array($r[1] ?? null) ? ($r[1]['on'] ?? $r[1]['data_on'] ?? $r[1]['on_label'] ?? 'True') : ($r[1]->on ?? $r[1]->data_on ?? $r[1]->on_label ?? 'True');
    $offLabel = is_array($r[1] ?? null) ? ($r[1]['off'] ?? $r[1]['data_off'] ?? $r[1]['off_label'] ?? 'False') : ($r[1]->off ?? $r[1]->data_off ?? $r[1]->off_label ?? 'False');
    $onLower = strtolower(trim((string)$onLabel));
    $valLower = strtolower(trim((string)$currentVal));
    $isChecked = in_array($valLower, ['true', '1', 'on', 'yes', $onLower], true) || $currentVal === true;
    $onStyle = is_array($r[1] ?? null) ? ($r[1]['onstyle'] ?? 'outline-success') : ($r[1]->onstyle ?? 'outline-success');
    $offStyle = is_array($r[1] ?? null) ? ($r[1]['offstyle'] ?? 'outline-danger') : ($r[1]->offstyle ?? 'outline-danger');
    $disabled = \Illuminate\Support\Facades\Route::is('*.show') ? 'disabled' : '';
    $maxLen = max(mb_strlen((string)$onLabel), mb_strlen((string)$offLabel));
    $toggleWidth = is_array($r[1] ?? null) && !empty($r[1]['width']) ? $r[1]['width'] : max(80, ($maxLen * 10) + 42);
@endphp
<small for="{{ $fieldName }}">{{ $r[0] }}</small>
<div class="mt-1">
    <input type="hidden" name="{{ $fieldName }}" value="false">
    <input type="checkbox"
        id="toggle_{{ $fieldName }}"
        name="{{ $fieldName }}"
        value="true"
        data-toggle="toggle"
        data-on="{{ $onLabel }}"
        data-off="{{ $offLabel }}"
        data-onstyle="{{ $onStyle }}"
        data-offstyle="{{ $offStyle }}"
        data-size="sm"
        data-width="{{ $toggleWidth }}"
        {{ $disabled }}
        {{ $isChecked ? 'checked' : '' }}
        {{ isset($r[1]->required) && $r[1]->required ? 'required' : '' }}>
</div>
@if(!empty($r[1]->helper))
    <small class="form-text text-muted mt-1">{{ $r[1]->helper }}</small>
@endif

@once
@push('styles')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
    <style>
        .toggle.btn {
            min-height: 30px;
        }
        .toggle.btn-sm {
            min-height: 28px;
        }
        .toggle.btn .toggle-on,
        .toggle.btn .toggle-off {
            font-size: 11.5px !important;
            font-weight: 600 !important;
            line-height: 1.4 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
        }
        .toggle.btn .toggle-on {
            padding: 4px 18px 4px 12px !important;
        }
        .toggle.btn .toggle-off {
            padding: 4px 12px 4px 18px !important;
        }
    </style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script>
        $(function () {
            if (typeof $.fn.bootstrapToggle === 'function') {
                $('input[data-toggle="toggle"]').each(function () {
                    if (!$(this).parent().hasClass('toggle')) {
                        $(this).bootstrapToggle();
                    }
                    var w = $(this).data('width');
                    if (w) {
                        $(this).parent('.toggle').css('width', w + 'px');
                    }
                });
            }
        });
    </script>
@endpush
@endonce
