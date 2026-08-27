@extends('cms::backend.layout.app', ['title' => str($slug)->headline()])

@section('content')
<style>
    .option-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        padding: 16px 20px;
        margin-bottom: 16px;
        transition: all 0.2s ease;
    }
    .option-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }
    .option-section-header {
        display: flex;
        align-items: center;
        margin-top: 10px;
        margin-bottom: 20px;
        padding-top: 10px;
    }
    .option-section-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.3px;
        padding: 6px 14px;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.05);
    }
    .option-section-line {
        flex-grow: 1;
        height: 1px;
        background: linear-gradient(to right, #e2e8f0, #f8fafc);
        margin-left: 14px;
    }
    .option-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .option-control {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px !important;
        font-size: 13.5px;
        padding: 8px 12px;
        height: auto;
        color: #1e293b;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }
    .option-control:focus {
        border-color: #3b82f6;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        outline: none;
    }
    .option-input-group .input-group-text {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-right: none;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
        color: #64748b;
        font-size: 13px;
        padding: 0 14px;
    }
    .option-input-group .option-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .option-helper {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
        line-height: 1.4;
    }
    .option-media-card {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        gap: 12px;
        transition: all 0.2s;
    }
    .option-media-card:hover {
        border-color: #94a3b8;
        background: #f1f5f9;
    }
    .option-media-card img {
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }
    .option-top-bar {
        position: sticky;
        top: 60px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: 10px 16px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }
    .btn-save-option {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 13.5px;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        transition: all 0.2s;
    }
    .btn-save-option:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        transform: translateY(-1px);
    }
    .btn-back-option {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 13.5px;
        transition: all 0.2s;
    }
    .btn-back-option:hover {
        background: #e2e8f0;
        color: #1e293b;
        text-decoration: none;
    }
    .note-editor.note-frame {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 10px !important;
        overflow: hidden;
    }
    .note-editor .note-toolbar {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 10px !important;
    }
</style>

<form action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
    @csrf

    {{-- Sticky Header Bar --}}
    <div class="option-top-bar d-flex align-items-center justify-content-between">
        <div>
            <h4 class="m-0 font-weight-bold text-dark" style="font-size: 17px; letter-spacing: -0.2px;">
                <i class="fa fa-sliders text-primary mr-1.5"></i> {{ str($slug)->headline() }}
            </h4>
            <small class="text-muted">Kelola pengaturan dan konfigurasi template dengan mudah</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('panel.dashboard') }}" class="btn btn-back-option mr-2">
                <i class="fa fa-arrow-left mr-1"></i> Kembali
            </a>
            <button type="submit" name="save_setting" value="true" class="btn btn-save-option">
                <i class="fa fa-check mr-1.5"></i> Simpan Pengaturan
            </button>
        </div>
    </div>

    {{-- Form Content Card --}}
    <div class="option-card">
        <div class="row">
            @foreach ($data as $field)
                @php
                    $fieldName = $field[0] ?? '';
                    $fieldMeta = $field[1] ?? 'text';
                    
                    // Normalisasi field format
                    if (is_array($fieldMeta) && isset($fieldMeta['type'])) {
                        $fieldType = $fieldMeta['type'];
                        $fieldDefault = $fieldMeta['default'] ?? null;
                        $fieldHelper = $fieldMeta['helper'] ?? null;
                        $fieldRequired = $fieldMeta['required'] ?? false;
                        $fieldMime = $fieldMeta['mime_type'] ?? null;
                    } elseif (is_object($fieldMeta) && isset($fieldMeta->type)) {
                        $fieldType = $fieldMeta->type;
                        $fieldDefault = $fieldMeta->default ?? null;
                        $fieldHelper = $fieldMeta->helper ?? null;
                        $fieldRequired = $fieldMeta->required ?? false;
                        $fieldMime = $fieldMeta->mime_type ?? null;
                    } else {
                        $fieldType = $fieldMeta;
                        $fieldDefault = (isset($field[2]) && is_string($field[2]) && $fieldType !== 'file' && $fieldType !== 'break') ? $field[2] : null;
                        $fieldHelper = null;
                        $fieldRequired = isset($field[3]);
                        $fieldMime = ($fieldType === 'file' && isset($field[2])) ? $field[2] : null;
                    }

                    $fieldSlug = _us($fieldName);
                    $savedVal = get_option($fieldSlug);
                    $displayVal = ($savedVal !== null && $savedVal !== '') ? $savedVal : ($fieldDefault ?? '');

                    // Cek layout col: full-width atau 2-kolom
                    $isFullWidth = in_array($fieldType, ['break', 'textarea', 'rich-text']) || str_contains($fieldSlug, 'embed') || str_contains($fieldSlug, 'alamat') || str_contains($fieldSlug, 'visi') || str_contains($fieldSlug, 'misi');
                    $colClass = $isFullWidth ? 'col-12' : 'col-md-6';
                @endphp

                @if($fieldType === 'break')
                    <div class="col-12">
                        <div class="option-section-header">
                            <div class="option-section-badge">
                                <i class="fa fa-layer-group"></i> {{ str($fieldName)->headline() }}
                            </div>
                            <div class="option-section-line"></div>
                        </div>
                        @if(!empty($fieldHelper))
                            <small class="text-muted d-block mt-n2 mb-3 pl-1">{{ $fieldHelper }}</small>
                        @endif
                    </div>

                @elseif(is_array($fieldType))
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <select name="{{ $fieldSlug }}" id="{{ $fieldSlug }}" class="form-control option-control" {{ $fieldRequired ? 'required' : '' }}>
                            <option value="">-- Pilih Opsi --</option>
                            @foreach($fieldType as $row)
                                <option value="{{ $row }}" {{ $displayVal == $row ? 'selected' : '' }}>{{ $row }}</option>
                            @endforeach
                        </select>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif ($fieldType === 'file' || $fieldType === 'image')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        
                        @if (media_exists($savedVal))
                            <div class="media-preview-wrapper mb-2">
                                <div class="option-media-card">
                                    @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $savedVal))
                                        <img src="{{ media($savedVal)->url() }}" style="height: 52px; width: 72px;">
                                        <div>
                                            <div class="font-weight-bold text-dark small mb-1">{{ basename($savedVal) }}</div>
                                            <a href="{{ media($savedVal)->url() }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;">
                                                <i class="fa fa-eye mr-1"></i> Pratinjau
                                            </a>
                                            <span title="Hapus Berkas" class="fa fa-trash text-danger pointer btn-remove-media ml-2" data-field="{{ $fieldSlug }}" style="cursor: pointer; font-size: 13px;"></span>
                                        </div>
                                    @else
                                        <div class="p-2 bg-light rounded text-primary"><i class="fa fa-file-alt fa-2x"></i></div>
                                        <div>
                                            <div class="font-weight-bold text-dark small mb-1">{{ basename($savedVal) }}</div>
                                            <a href="{{ $savedVal }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;">
                                                <i class="fa fa-download mr-1"></i> Buka File
                                            </a>
                                            <span title="Hapus Berkas" class="fa fa-trash text-danger pointer btn-remove-media ml-2" data-field="{{ $fieldSlug }}" style="cursor: pointer; font-size: 13px;"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="media-input-wrapper" style="{{ media_exists($savedVal) ? 'display:none;' : '' }}">
                            <input {{ ($fieldRequired && !media_exists($savedVal)) ? 'required' : '' }} 
                                type="file" 
                                accept="{{ $fieldMime ?? ($fieldType === 'image' ? 'image/*' : allow_mime()) }}" 
                                class="compress-image form-control-file option-control" 
                                name="{{ $fieldSlug }}">
                        </div>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif($fieldType === 'phone')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <div class="input-group option-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                            </div>
                            <input {{ $fieldRequired ? 'required' : '' }}
                                type="tel"
                                id="{{ $fieldSlug }}"
                                value="{{ $displayVal }}"
                                class="form-control option-control"
                                maxlength="16"
                                minlength="9"
                                pattern="^(0|62)[0-9]{8,14}$"
                                title="Nomor wajib diawali 0 atau 62 (contoh: 081234567890)"
                                oninput="let v = this.value.replace(/[^0-9]/g, ''); if ((v.length === 1 && v !== '0' && v !== '6') || (v.length >= 2 && !v.startsWith('0') && !v.startsWith('62'))) { v = ''; } this.value = v;"
                                onblur="if (this.value === '6') this.value = '';"
                                name="{{ $fieldSlug }}"
                                placeholder="Contoh: 081234567890">
                        </div>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif($fieldType === 'url')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <div class="input-group option-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-globe"></i></span>
                            </div>
                            <input {{ $fieldRequired ? 'required' : '' }}
                                type="url"
                                id="{{ $fieldSlug }}"
                                value="{{ $displayVal }}"
                                class="form-control option-control"
                                pattern="^https?://[^\s]+$"
                                title="URL wajib diawali http:// atau https:// tanpa spasi"
                                oninput="this.value = this.value.replace(/\s+/g, '');"
                                onblur="if(this.value.trim() !== '' && !this.value.startsWith('http://') && !this.value.startsWith('https://')) { this.value = 'https://' + this.value; }"
                                name="{{ $fieldSlug }}"
                                placeholder="https://">
                        </div>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif($fieldType === 'currency')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <div class="input-group option-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold" style="color: #2563eb;">Rp</span>
                            </div>
                            <input {{ $fieldRequired ? 'required' : '' }}
                                type="text"
                                id="{{ $fieldSlug }}"
                                value="{{ $displayVal }}"
                                class="form-control option-control"
                                name="{{ $fieldSlug }}"
                                placeholder="0"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                        </div>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif($fieldType === 'color')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="input_{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <div class="input-group option-input-group" style="max-width: 260px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text p-1">
                                    <input type="color"
                                        style="width: 32px; height: 28px; border: none; background: transparent; cursor: pointer;"
                                        id="picker_{{ $fieldSlug }}"
                                        value="{{ $displayVal ?: '#3b82f6' }}"
                                        oninput="document.getElementById('input_{{ $fieldSlug }}').value = this.value">
                                </span>
                            </div>
                            <input {{ $fieldRequired ? 'required' : '' }}
                                type="text"
                                value="{{ $displayVal ?: '#3b82f6' }}"
                                class="form-control option-control text-monospace text-uppercase"
                                id="input_{{ $fieldSlug }}"
                                name="{{ $fieldSlug }}"
                                placeholder="#3B82F6"
                                maxlength="7"
                                oninput="if(this.value.length === 7 && this.value.startsWith('#')) { document.getElementById('picker_{{ $fieldSlug }}').value = this.value; }">
                        </div>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif($fieldType === 'textarea')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <textarea {{ $fieldRequired ? 'required' : '' }} 
                            class="form-control option-control" 
                            id="{{ $fieldSlug }}" 
                            name="{{ $fieldSlug }}" 
                            rows="3" 
                            placeholder="Masukkan {{ $fieldName }}">{{ $displayVal }}</textarea>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @elseif($fieldType === 'rich-text')
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="editor_{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <textarea id="editor_{{ $fieldSlug }}" class="form-control custom_summernote" name="{{ $fieldSlug }}">{{ $displayVal }}</textarea>
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>

                @else
                    <div class="{{ $colClass }} mb-3">
                        <label class="option-label" for="{{ $fieldSlug }}">
                            <span>{{ str($fieldName)->headline() }}</span>
                            @if($fieldRequired)<span class="text-danger small">*</span>@endif
                        </label>
                        <input {{ $fieldRequired ? 'required' : '' }} 
                            type="{{ in_array($fieldType, ['number', 'email', 'date', 'datetime-local']) ? $fieldType : 'text' }}" 
                            class="form-control option-control" 
                            id="{{ $fieldSlug }}" 
                            name="{{ $fieldSlug }}" 
                            placeholder="Masukkan {{ $fieldName }}" 
                            value="{{ $displayVal }}">
                        @if(!empty($fieldHelper))
                            <div class="option-helper"><i class="fa fa-info-circle text-primary"></i> {{ $fieldHelper }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        <div class="border-top pt-4 mt-4 d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="fa fa-shield-alt text-success mr-1"></i> Perubahan akan segera diterapkan pada template aktif</small>
        
        </div>
    </div>
</form>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            if ($('.custom_summernote').length) {
                $('.custom_summernote').summernote({
                    height: 220,
                    placeholder: 'Ketik konten di sini...',
                    disableDragAndDrop: true,
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough']],
                        ['para', ['paragraph', 'ul', 'ol']],
                    ]
                });
            }
        });
    </script>
@endpush

@include('cms::backend.layout.js')
@endsection
