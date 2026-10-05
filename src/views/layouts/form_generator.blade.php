@php
    $form_id = $form_id ?? ('lz-form-' . $module . '-' . uniqid());
    $action = $action ?? post_form_url($module);
    $submit_text = $submit_text ?? 'Kirim';
    $submit_class = $submit_class ?? '';
    $form_class = $form_class ?? '';
    $status = $status ?? 'publish';
    $title_field = $title_field ?? null;
    $title_as_id = $title_as_id ?? ($options['title_as_id'] ?? ($mod->form->title_as_id ?? false));
    $title_as_id_str = is_array($title_as_id) ? json_encode($title_as_id) : ($title_as_id === true ? '1' : (string) ($title_as_id ?: ''));
    $has_captcha = $has_captcha ?? (isset($options['captcha']) ? !empty($options['captcha']) : (!empty($mod->form->captcha)));
    $captcha_label = $captcha_label ?? ($options['captcha_label'] ?? ($options['captcha_title'] ?? (is_array($options['captcha'] ?? null) ? ($options['captcha']['label'] ?? $options['captcha']['title'] ?? null) : null) ?? (is_string($options['captcha'] ?? null) ? $options['captcha'] : null) ?? 'Kode Keamanan'));
    $captcha_placeholder = $captcha_placeholder ?? ($options['captcha_placeholder'] ?? (is_array($options['captcha'] ?? null) ? ($options['captcha']['placeholder'] ?? null) : null) ?? 'Ketik 5 digit kode di atas');
    $captcha_str = $has_captcha ? '1' : '0';
    $redirect = $redirect ?? null;
    $success_message = $success_message ?? 'Data berhasil disimpan.';
    $ajax = $ajax ?? false;
    $errors = $errors ?? session('errors', new \Illuminate\Support\ViewErrorBag);
@endphp

<div class="lz-form-wrapper" id="{{ $form_id }}-wrapper">
    <style>
        .lz-form-container {
            width: 100%;
            margin: 0 auto;
            font-family: inherit;
        }
        .lz-form-card {
            background-color: var(--lz-form-bg, #ffffff);
            border: 1px solid var(--lz-form-border, #e2e8f0);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        .dark .lz-form-card, [data-theme="dark"] .lz-form-card {
            background-color: var(--lz-form-bg-dark, #1e293b);
            border-color: var(--lz-form-border-dark, #334155);
            color: #f8fafc;
        }
        .lz-form-group {
            margin-bottom: 1.25rem;
        }
        .lz-form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.375rem;
            color: var(--lz-form-text, #334155);
        }
        .dark .lz-form-label, [data-theme="dark"] .lz-form-label {
            color: #cbd5e1;
        }
        .lz-form-required {
            color: #ef4444;
            margin-left: 0.25rem;
        }
        .lz-form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: #1e293b;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-sizing: border-box;
            outline: none;
        }
        .dark .lz-form-control, [data-theme="dark"] .lz-form-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }
        .lz-form-control:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }
        .lz-form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }
        .lz-form-helper {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .dark .lz-form-helper, [data-theme="dark"] .lz-form-helper {
            color: #94a3b8;
        }
        .lz-form-error {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.25rem;
            font-weight: 500;
        }
        .lz-form-break {
            margin: 1.75rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .dark .lz-form-break, [data-theme="dark"] .lz-form-break {
            border-bottom-color: #334155;
        }
        .lz-form-break-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0d9488;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lz-form-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #ffffff;
            background-color: #0d9488;
            border: none;
            border-radius: 0.625rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.25);
            width: 100%;
        }
        .lz-form-btn-submit:hover {
            background-color: #0f766e;
            box-shadow: 0 6px 14px rgba(13, 148, 136, 0.35);
            transform: translateY(-1px);
        }
        .lz-form-btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .lz-alert {
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .lz-alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .dark .lz-alert-success, [data-theme="dark"] .lz-alert-success {
            background-color: rgba(6, 95, 70, 0.3);
            border-color: #065f46;
            color: #a7f3d0;
        }
        .lz-alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .dark .lz-alert-danger, [data-theme="dark"] .lz-alert-danger {
            background-color: rgba(153, 27, 27, 0.3);
            border-color: #991b1b;
            color: #fecaca;
        }
        .lz-form-group-captcha .captcha-form-wrapper {
            margin: 0.375rem 0 0.25rem 0 !important;
        }
        .lz-form-group-captcha .captcha-img-field {
            height: 40px !important;
            border-radius: 0.5rem !important;
            border: 1px solid #cbd5e1 !important;
            cursor: pointer !important;
            transition: opacity 0.2s ease;
        }
        .lz-form-group-captcha .captcha-img-field:hover {
            opacity: 0.85;
        }
        .lz-form-group-captcha .captcha-form-wrapper span {
            height: 40px !important;
            min-height: 40px !important;
            width: 40px !important;
            min-width: 40px !important;
            border-radius: 0.5rem !important;
            border-color: #cbd5e1 !important;
            background: #f8fafc !important;
            color: #475569 !important;
            transition: all 0.2s ease !important;
        }
        .lz-form-group-captcha .captcha-form-wrapper span:hover {
            background: #e2e8f0 !important;
            color: #0d9488 !important;
        }
        .lz-form-group-captcha .captcha-form-wrapper input {
            height: 40px !important;
            min-height: 40px !important;
            border-radius: 0.5rem !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.1em !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        .lz-form-group-captcha .captcha-form-wrapper input:focus {
            border-color: #0d9488 !important;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15) !important;
        }
        .lz-form-group-captcha .captcha-form-wrapper input.is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }
        .dark .lz-form-group-captcha .captcha-form-wrapper span,
        [data-theme="dark"] .lz-form-group-captcha .captcha-form-wrapper span {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        .dark .lz-form-group-captcha .captcha-form-wrapper span:hover,
        [data-theme="dark"] .lz-form-group-captcha .captcha-form-wrapper span:hover {
            background: #334155 !important;
            color: #2dd4bf !important;
        }
        .dark .lz-form-group-captcha .captcha-form-wrapper input,
        [data-theme="dark"] .lz-form-group-captcha .captcha-form-wrapper input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
    </style>

    <div class="lz-form-container">
        <div class="lz-form-card">
            @if(!empty($options['title']))
                <div style="margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(226, 232, 240, 0.6);">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: inherit;">
                        {{ $options['title'] }}
                    </h3>
                    @if(!empty($options['description']))
                        <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">
                            {{ $options['description'] }}
                        </p>
                    @endif
                </div>
            @endif

            <!-- Success Alert -->
            @if(session('success'))
                <div class="lz-alert lz-alert-success">
                    <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <span>{{ session('success') }}</span>
                        @if(session('post_title') && !str_contains(session('success'), session('post_title')))
                            <div style="margin-top: 0.35rem; font-size: 0.8125rem;">
                                <strong>Nomor ID / Registrasi:</strong> <span style="background: rgba(0,0,0,0.08); padding: 2px 7px; border-radius: 4px; font-family: monospace; font-size: 0.875rem; font-weight: 700;">{{ session('post_title') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Errors Alert -->
            @if($errors->any())
                <div class="lz-alert lz-alert-danger">
                    <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <div style="font-weight: 600;">Harap periksa kembali isian formulir:</div>
                        <ul style="margin: 0.25rem 0 0 1rem; padding: 0; font-size: 0.8125rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- AJAX Message Box -->
            <div id="{{ $form_id }}-alert" class="lz-alert" style="display: none;"></div>

            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="{{ $form_class }}" id="{{ $form_id }}" data-ajax="{{ $ajax ? 'true' : 'false' }}">
                @csrf
                <!-- Honeypot for Anti-Spam -->
                <div style="display: none !important; opacity: 0; position: absolute; left: -9999px;" aria-hidden="true">
                    <input type="text" name="_lz_hp" value="" tabindex="-1" autocomplete="off">
                </div>

                <!-- Hidden Controls -->
                @php
                    $exceptList = (array) ($options['except'] ?? $options['exclude'] ?? []);
                    $exceptKeys = array_map(fn($k) => _us($k), $exceptList);
                    $exceptString = implode(',', $exceptKeys);
                    $formSignature = hash_hmac('sha256', $module . '|' . $status . '|' . ($title_field ?? '') . '|' . ($redirect ?? '') . '|' . $exceptString . '|' . $title_as_id_str . '|' . $captcha_str, config('app.key'));
                @endphp
                <input type="hidden" name="_lz_sig" value="{{ $formSignature }}">
                <input type="hidden" name="_lz_ts" value="{{ time() }}">
                <input type="hidden" name="_status" value="{{ $status }}">
                @if($exceptString)
                    <input type="hidden" name="_except" value="{{ $exceptString }}">
                @endif
                @if($redirect)
                    <input type="hidden" name="_redirect" value="{{ $redirect }}">
                @endif
                @if($title_field)
                    <input type="hidden" name="_title_field" value="{{ $title_field }}">
                @endif
                @if(!empty($title_as_id_str))
                    <input type="hidden" name="_title_as_id" value="{{ $title_as_id_str }}">
                @endif
                @if($has_captcha)
                    <input type="hidden" name="_captcha" value="1">
                @endif
                <input type="hidden" name="_success_message" value="{{ $success_message }}">

                <!-- Form Fields -->
                <div class="lz-form-fields">
                    @foreach($fields as $field)
                        @php
                            $label = $field[0] ?? '';
                            $meta = $field[1] ?? [];
                            if (is_string($meta)) {
                                $meta = ['type' => $meta];
                            } elseif (is_object($meta)) {
                                $meta = (array) $meta;
                            }

                            // Sembunyikan field title sepenuhnya dari formulir jika title_as_id aktif
                            if (!empty($title_as_id) && (($meta['type'] ?? '') === 'title' || ($meta['name'] ?? '') === 'title')) {
                                continue;
                            }

                            // 1. Pengecualian field bertanda admin only / public => false
                            if (isset($meta['public']) && $meta['public'] === false) {
                                continue;
                            }
                            if (!empty($meta['admin_only']) || !empty($meta['hide_public'])) {
                                continue;
                            }

                            $checkKey = _us($meta['key'] ?? $meta['name'] ?? $label);

                            // 2. Pengecualian field yang ada di daftar except / exclude
                            $isExcluded = false;
                            foreach ($exceptKeys as $ex) {
                                if ($checkKey === $ex || \Illuminate\Support\Str::is($ex, $checkKey) || \Illuminate\Support\Str::contains($checkKey, $ex)) {
                                    $isExcluded = true;
                                    break;
                                }
                            }
                            if ($isExcluded) {
                                continue;
                            }

                            $type = $meta['type'] ?? 'text';
                            $required = !empty($meta['required']);
                            $helper = $meta['helper'] ?? '';
                            $placeholder = $meta['placeholder'] ?? ($meta['helper'] ?? '');
                            $optionsList = $meta['options'] ?? [];
                            $accept = $meta['accept'] ?? ($meta['mime_type'] ?? null);

                            // Tentukan apakah standard column post atau custom data_field
                            $isStandard = in_array($type, ['title', 'content', 'description', 'media', 'category', 'category_id', 'parent', 'parent_id']) 
                                          || in_array(($meta['name'] ?? ''), ['title', 'content', 'description', 'media', 'category_id', 'parent_id']);
                            
                            if ($isStandard) {
                                if ($type === 'title' || ($meta['name'] ?? '') === 'title') {
                                    $inputName = 'title';
                                    $errorKey = 'title';
                                } elseif ($type === 'content' || ($meta['name'] ?? '') === 'content') {
                                    $inputName = 'content';
                                    $errorKey = 'content';
                                } elseif ($type === 'description' || ($meta['name'] ?? '') === 'description') {
                                    $inputName = 'description';
                                    $errorKey = 'description';
                                } elseif ($type === 'media' || ($meta['name'] ?? '') === 'media') {
                                    $inputName = 'media';
                                    $errorKey = 'media';
                                } elseif ($type === 'category' || $type === 'category_id' || ($meta['name'] ?? '') === 'category_id') {
                                    $inputName = 'category_id';
                                    $errorKey = 'category_id';
                                } elseif ($type === 'parent' || $type === 'parent_id' || ($meta['name'] ?? '') === 'parent_id') {
                                    $inputName = 'parent_id';
                                    $errorKey = 'parent_id';
                                } else {
                                    $inputName = $meta['name'] ?? 'title';
                                    $errorKey = $inputName;
                                }
                                $fieldKey = $inputName;
                            } else {
                                $fieldKey = _us($meta['key'] ?? $label);
                                $inputName = "field[{$fieldKey}]";
                                $errorKey = "field.{$fieldKey}";
                            }

                            $inputId = "lz_{$form_id}_{$fieldKey}";
                            $oldVal = old($isStandard ? $inputName : "field.{$fieldKey}", $meta['default'] ?? '');
                        @endphp

                        @if($type === 'break')
                            <div class="lz-form-break">
                                <h4 class="lz-form-break-title">{{ $label }}</h4>
                                @if($helper)
                                    <p class="lz-form-helper">{{ $helper }}</p>
                                @endif
                            </div>
                        @elseif($type === 'hidden')
                            <input type="hidden" name="{{ $inputName }}" value="{{ $oldVal }}">
                        @else
                            <div class="lz-form-group">
                                <label for="{{ $inputId }}" class="lz-form-label">
                                    {{ $label }}
                                    @if($required)
                                        <span class="lz-form-required">*</span>
                                    @endif
                                </label>

                                @if($type === 'textarea' || $type === 'description')
                                    <textarea 
                                        name="{{ $inputName }}" 
                                        id="{{ $inputId }}" 
                                        rows="{{ $meta['rows'] ?? 4 }}" 
                                        placeholder="{{ $placeholder }}"
                                        class="lz-form-control {{ $errors->has($errorKey) ? 'is-invalid' : '' }}"
                                        {{ $required ? 'required' : '' }}
                                    >{{ $oldVal }}</textarea>

                                @elseif($type === 'rich-text' || $type === 'editor' || $type === 'content')
                                    <textarea 
                                        name="{{ $inputName }}" 
                                        id="{{ $inputId }}" 
                                        rows="{{ $meta['rows'] ?? 6 }}" 
                                        placeholder="{{ $placeholder }}"
                                        class="lz-form-control {{ $errors->has($errorKey) ? 'is-invalid' : '' }}"
                                        {{ $required ? 'required' : '' }}
                                    >{{ $oldVal }}</textarea>

                                @elseif($type === 'select' || $type === 'option' || $type === 'category' || $type === 'category_id')
                                    <select 
                                        name="{{ $inputName }}" 
                                        id="{{ $inputId }}" 
                                        class="lz-form-control {{ $errors->has($errorKey) ? 'is-invalid' : '' }}"
                                        {{ $required ? 'required' : '' }}
                                    >
                                        <option value="">-- {{ $placeholder ?: 'Pilih ' . $label }} --</option>
                                        @foreach($optionsList as $optKey => $optVal)
                                            @php
                                                $optValue = is_numeric($optKey) && is_array($optionsList) && \Illuminate\Support\Arr::isList($optionsList) ? $optVal : $optKey;
                                                $optLabel = $optVal;
                                            @endphp
                                            <option value="{{ $optValue }}" {{ (string)$oldVal === (string)$optValue ? 'selected' : '' }}>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($type === 'file' || $type === 'media')
                                    <input 
                                        type="file" 
                                        name="{{ $inputName }}" 
                                        id="{{ $inputId }}" 
                                        @if($accept) accept="{{ $accept }}" @endif
                                        class="lz-form-control {{ $errors->has($errorKey) ? 'is-invalid' : '' }}"
                                        {{ $required ? 'required' : '' }}
                                    >

                                @elseif($type === 'radio')
                                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; padding: 0.25rem 0;">
                                        @foreach($optionsList as $optKey => $optVal)
                                            @php
                                                $optValue = is_numeric($optKey) && is_array($optionsList) && \Illuminate\Support\Arr::isList($optionsList) ? $optVal : $optKey;
                                                $optLabel = $optVal;
                                            @endphp
                                            <label style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; cursor: pointer;">
                                                <input type="radio" name="{{ $inputName }}" value="{{ $optValue }}" {{ (string)$oldVal === (string)$optValue ? 'checked' : '' }} {{ $required ? 'required' : '' }}>
                                                <span>{{ $optLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($type === 'checkbox')
                                    <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                        <input type="checkbox" name="{{ $inputName }}" value="1" {{ !empty($oldVal) ? 'checked' : '' }} {{ $required ? 'required' : '' }}>
                                        <span>{{ $placeholder ?: $label }}</span>
                                    </label>

                                @else
                                    @php
                                        $inputType = 'text';
                                        if (in_array($type, ['email', 'phone', 'tel', 'number', 'date', 'time', 'datetime', 'url', 'color', 'password'])) {
                                            if ($type === 'phone') $inputType = 'tel';
                                            elseif ($type === 'datetime') $inputType = 'datetime-local';
                                            else $inputType = $type;
                                        }
                                    @endphp
                                    <input 
                                        type="{{ $inputType }}" 
                                        name="{{ $inputName }}" 
                                        id="{{ $inputId }}" 
                                        value="{{ $oldVal }}" 
                                        placeholder="{{ $placeholder }}"
                                        class="lz-form-control {{ $errors->has($errorKey) ? 'is-invalid' : '' }}"
                                        {{ $required ? 'required' : '' }}
                                    >
                                @endif

                                @if($helper && $type !== 'break')
                                    <div class="lz-form-helper">{{ $helper }}</div>
                                @endif

                                @if($errors->has($errorKey))
                                    <div class="lz-form-error">{{ $errors->first($errorKey) }}</div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Captcha Field (Optional) -->
                @if($has_captcha)
                    <div class="lz-form-group lz-form-group-captcha" style="margin-top: 1.25rem;">
                        <label class="lz-form-label" for="{{ $form_id }}-captcha">
                            {{ $captcha_label }}
                            <span class="lz-form-required">*</span>
                        </label>
                        {!! captcha_field('captcha', $captcha_placeholder) !!}
                        <div class="lz-form-helper">Ketik 5 digit kode di atas. Klik gambar atau ikon putar jika kode kurang jelas.</div>
                        @if($errors->has('captcha'))
                            <div class="lz-form-error">{{ $errors->first('captcha') }}</div>
                        @endif
                    </div>
                @endif

                <!-- Submit Button -->
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="lz-form-btn-submit {{ $submit_class }}" id="{{ $form_id }}-btn">
                        <span class="lz-btn-text">{{ $submit_text }}</span>
                        <span class="lz-btn-spinner" style="display: none;">
                            <svg style="width: 1.125rem; height: 1.125rem; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle;" viewBox="0 0 24 24" fill="none">
                                <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($ajax)
        <script>
            (function() {
                var form = document.getElementById('{{ $form_id }}');
                if (!form) return;
                var alertBox = document.getElementById('{{ $form_id }}-alert');
                var submitBtn = document.getElementById('{{ $form_id }}-btn');
                var btnText = submitBtn ? submitBtn.querySelector('.lz-btn-text') : null;
                var btnSpinner = submitBtn ? submitBtn.querySelector('.lz-btn-spinner') : null;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (submitBtn) submitBtn.disabled = true;
                    if (btnText) btnText.style.display = 'none';
                    if (btnSpinner) btnSpinner.style.display = 'inline-flex';
                    if (alertBox) alertBox.style.display = 'none';

                    // Clear previous invalid highlights
                    form.querySelectorAll('.is-invalid').forEach(function(el) {
                        el.classList.remove('is-invalid');
                    });
                    form.querySelectorAll('.lz-form-error').forEach(function(el) {
                        el.remove();
                    });

                    var formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return { status: res.status, ok: res.ok, data: data };
                        });
                    })
                    .then(function(result) {
                        if (result.ok && result.data.status === 'success') {
                            if (alertBox) {
                                alertBox.className = 'lz-alert lz-alert-success';
                                var successMsg = result.data.message || 'Data berhasil dikirim.';
                                var titleId = (result.data.data && result.data.data.title) ? result.data.data.title : (result.data.title || '');
                                if (titleId && !successMsg.includes(titleId) && form.querySelector('[name="_title_as_id"]')) {
                                    alertBox.innerHTML = '<svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><div><span>' + successMsg + '</span><div style="margin-top: 0.35rem; font-size: 0.8125rem;"><strong>Nomor ID / Registrasi:</strong> <span style="background: rgba(0,0,0,0.08); padding: 2px 7px; border-radius: 4px; font-family: monospace; font-size: 0.875rem; font-weight: 700;">' + titleId + '</span></div></div>';
                                } else {
                                    alertBox.innerHTML = '<svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="lz-alert-txt"></span>';
                                    alertBox.querySelector('.lz-alert-txt').textContent = successMsg;
                                }
                                alertBox.style.display = 'flex';
                            }
                            form.reset();

                            // Refresh captcha if present on form
                            var captchaImgSuccess = form.querySelector('.captcha-img-field') || form.querySelector('.captcha-form-wrapper img');
                            if (captchaImgSuccess) {
                                fetch('{{ url("captcha/refresh") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(function(r) { return r.json(); })
                                    .then(function(d) { if (d && d.url) captchaImgSuccess.src = d.url; })
                                    .catch(function() {});
                            }

                            if (result.data.redirect) {
                                setTimeout(function() {
                                    window.location.href = result.data.redirect;
                                }, 1500);
                            }
                        } else {
                            var errMsg = (result.data && result.data.message) ? result.data.message : 'Terjadi kesalahan saat menyimpan data.';
                            if (alertBox) {
                                alertBox.className = 'lz-alert lz-alert-danger';
                                alertBox.innerHTML = '<svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="lz-alert-txt"></span>';
                                alertBox.querySelector('.lz-alert-txt').textContent = errMsg;
                                alertBox.style.display = 'flex';
                            }

                            // Refresh captcha on submit error
                            var captchaImgErr = form.querySelector('.captcha-img-field') || form.querySelector('.captcha-form-wrapper img');
                            var captchaInputErr = form.querySelector('[name="captcha"]');
                            if (captchaImgErr) {
                                fetch('{{ url("captcha/refresh") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(function(r) { return r.json(); })
                                    .then(function(d) { if (d && d.url) captchaImgErr.src = d.url; })
                                    .catch(function() {});
                            }
                            if (captchaInputErr) {
                                captchaInputErr.value = '';
                            }

                            if (result.data && result.data.errors) {
                                for (var errField in result.data.errors) {
                                    var cleanName = errField.replace('.', '[').replace('.', ']') + (errField.includes('.') ? ']' : '');
                                    var inputEl = form.querySelector('[name="' + errField + '"]') || form.querySelector('[name="' + cleanName + '"]');
                                    if (inputEl) {
                                        inputEl.classList.add('is-invalid');
                                        var errDiv = document.createElement('div');
                                        errDiv.className = 'lz-form-error';
                                        errDiv.textContent = result.data.errors[errField][0];
                                        var container = inputEl.closest('.lz-form-group') || inputEl.parentNode;
                                        container.appendChild(errDiv);
                                    }
                                }
                            }
                        }
                    })
                    .catch(function(err) {
                        if (alertBox) {
                            alertBox.className = 'lz-alert lz-alert-danger';
                            alertBox.innerHTML = '<svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span>Terjadi gangguan koneksi. Silakan coba lagi.</span>';
                            alertBox.style.display = 'flex';
                        }
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
