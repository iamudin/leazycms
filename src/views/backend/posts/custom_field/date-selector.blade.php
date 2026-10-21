@php
    $fieldName = _us($r[0]);
    $meta = is_array($r[1] ?? null) ? $r[1] : (is_object($r[1] ?? null) ? (array)$r[1] : []);
    $defaultVal = $meta['default'] ?? [];
    $currentVal = old($fieldName, isset($field[$fieldName]) ? $field[$fieldName] : $defaultVal);

    if (is_string($currentVal) && str_starts_with(trim($currentVal), '[') && str_ends_with(trim($currentVal), ']')) {
        $decoded = json_decode($currentVal, true);
        if (is_array($decoded)) {
            $currentVal = $decoded;
        }
    }
    $selectedValues = is_array($currentVal) ? $currentVal : ($currentVal !== '' && $currentVal !== null ? (array)$currentVal : []);
    $selectedValues = array_values(array_filter(array_map('trim', array_map('strval', $selectedValues))));

    $options = get_date_selector_options($meta, $selectedValues);
    $placeholder = $meta['placeholder'] ?? ('-- Pilih ' . $r[0] . ' --');
    $isRequired = !empty($meta['required']);
    $hasPredefined = !empty($meta['dates']) || !empty($meta['options']) || !empty($meta['data']) || !empty($meta['values']) || !empty($meta['start_date']) || !empty($meta['from']);
    $allowTags = isset($meta['tags']) ? (bool)$meta['tags'] : (isset($meta['allow_custom']) ? (bool)$meta['allow_custom'] : true);
    $disabled = \Illuminate\Support\Facades\Route::is('*.show') ? 'disabled' : '';
@endphp

<small for="{{ $fieldName }}">{{ $r[0] }}</small>
<div class="mt-1 d-flex align-items-center">
    <div class="flex-grow-1" style="min-width: 0;">
        <input type="hidden" name="{{ $fieldName }}" value="">
        <select name="{{ $fieldName }}[]"
            id="field_{{ $fieldName }}"
            class="form-control select2-date-selector-form"
            multiple="multiple"
            data-placeholder="{{ $placeholder }}"
            data-tags="{{ $allowTags ? 'true' : 'false' }}"
            style="width: 100%;"
            {{ $disabled }}
            {{ $isRequired ? 'required' : '' }}>
            @foreach($options as $val => $lbl)
                <option value="{{ $val }}" {{ in_array((string)$val, $selectedValues, true) ? 'selected' : '' }}>
                    {{ $lbl }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="ml-1 position-relative">
        <button type="button"
            class="btn btn-outline-primary btn-date-picker-trigger px-2.5 py-1.5"
            title="Buka Kalender (Tambah Tanggal Bebas)"
            style="height: 36px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #fff;"
            {{ $disabled }}>
            <i class="fa fa-calendar"></i>
        </button>
        <input type="date"
            class="hidden-native-datepicker"
            value=""
            style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; pointer-events: none; z-index: -1;"
            tabindex="-1">
    </div>
</div>
@if(!empty($meta['helper']))
    <small class="form-text text-muted mt-1">{{ $meta['helper'] }}</small>
@endif

<script>
    if (typeof window.initDateSelectorForm === 'function') {
        window.initDateSelectorForm('#field_{{ $fieldName }}');
    }
</script>

@once
@push('scripts')
<script>
    if (typeof window.initDateSelectorForm !== 'function') {
        window.initDateSelectorForm = function (context) {
            if (typeof $ === 'undefined' || typeof $.fn.select2 !== 'function') return;

            var $scope = context ? $(context).find('.select2-date-selector-form') : $('.select2-date-selector-form');
            if (!$scope.length && context && $(context).hasClass('select2-date-selector-form')) {
                $scope = $(context);
            }

            $scope.each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    try {
                        $el.select2('destroy');
                    } catch (e) {}
                }
                var isTags = $el.data('tags') === true || $el.data('tags') === 'true';
                $el.select2({
                    width: '100%',
                    placeholder: $el.data('placeholder') || '-- Pilih Tanggal --',
                    allowClear: !$el.prop('disabled'),
                    tags: isTags,
                    closeOnSelect: false,
                    disabled: $el.prop('disabled')
                });
            });
        };
    }

    $(function () {
        window.initDateSelectorForm();
    });

    $(document).on('click', '.btn-date-picker-trigger', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $picker = $btn.siblings('.hidden-native-datepicker');
        if ($picker.length) {
            try {
                if (typeof $picker[0].showPicker === 'function') {
                    $picker[0].showPicker();
                } else {
                    $picker.focus().click();
                }
            } catch (err) {
                $picker.focus().click();
            }
        }
    });

    $(document).on('change', '.hidden-native-datepicker', function () {
        var dateVal = this.value;
        if (!dateVal) return;
        var $select = $(this).closest('.d-flex').find('.select2-date-selector-form');
        if ($select.length) {
            var exists = $select.find('option[value="' + dateVal + '"]').length > 0;
            if (!exists) {
                var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                var parts = dateVal.split('-');
                var label = dateVal;
                if (parts.length === 3) {
                    var mIdx = parseInt(parts[1], 10) - 1;
                    label = parts[2] + ' ' + (months[mIdx] || parts[1]) + ' ' + parts[0];
                }
                var newOpt = new Option(label, dateVal, true, true);
                $select.append(newOpt);
            }
            var curVals = $select.val() || [];
            if (!Array.isArray(curVals)) {
                curVals = curVals ? [curVals] : [];
            }
            if (curVals.indexOf(dateVal) === -1) {
                curVals.push(dateVal);
            }
            $select.val(curVals).trigger('change');
        }
        this.value = '';
    });
</script>
@endpush
@endonce
