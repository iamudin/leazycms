@extends('cms::backend.layout.app', ['title' => str($slug)->headline()])
@section('content')
    <form class="" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="tile p-4 shadow-sm mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                        <h4 class="m-0 font-weight-bold text-dark" style="font-size: 18px;">
                            <i class="fa fa-sliders text-primary mr-1"></i> {{ str($slug)->headline() }}
                        </h4>
                        <div class="btn-group">
                            <button name="save_setting" value="true" class="btn btn-primary btn-sm px-3 font-weight-bold">
                                <i class="fa fa-save mr-1"></i> Simpan Pengaturan
                            </button>
                            <a href="{{ route('panel.dashboard') }}" class="btn btn-secondary btn-sm px-3">
                                <i class="fa fa-undo mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    @foreach ($data as $field)
                        @php
                            $fieldName = $field[0] ?? '';
                            $fieldType = $field[1] ?? 'text';
                            $fieldExtra = $field[2] ?? null;
                            $fieldSlug = _us($fieldName);
                            $savedVal = get_option($fieldSlug);
                            $fallbackDefault = (isset($fieldExtra) && is_string($fieldExtra) && $fieldType !== 'file' && $fieldType !== 'break') ? $fieldExtra : '';
                            $displayVal = ($savedVal !== null && $savedVal !== '') ? $savedVal : $fallbackDefault;
                        @endphp

                        @if($fieldType === 'break')
                            <div class="sub-section-header mt-4 mb-3 pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <div class="badge badge-primary px-3 py-1.5 mr-2 font-weight-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.5px; border-radius: 6px;">
                                        <i class="fa fa-layer-group mr-1.5"></i> {{ str($fieldName)->headline() }}
                                    </div>
                                    <div class="flex-grow-1" style="height: 2px; background: linear-gradient(to right, rgba(0,123,255,0.4), rgba(0,123,255,0.05));"></div>
                                </div>
                                @if(!empty($fieldExtra))
                                    <small class="text-muted d-block mt-1 pl-1">{{ $fieldExtra }}</small>
                                @endif
                            </div>
                        @elseif(is_array($fieldType))
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small mb-1">{{ str($fieldName)->headline() }}</label>
                                <select name="{{ $fieldSlug }}" class="form-control form-control-sm" style="max-width: 320px;" @if(isset($fieldExtra)) required @endif>
                                    <option value="">-- Pilih --</option>
                                    @foreach($fieldType as $row)
                                        <option value="{{ $row }}" {{ $displayVal == $row ? 'selected' : '' }}>{{ $row }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif ($fieldType === 'file')
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small mb-1">{{ str($fieldName)->headline() }}</label>
                                @if (media_exists($savedVal))
                                    <div class="media-preview-wrapper mb-2">
                                        <a href="{{ $savedVal }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-file mr-1"></i> {{ basename($savedVal) }}
                                        </a>
                                        <i title="Hapus File" class="fa fa-trash text-danger pointer btn-remove-media ml-2" data-field="{{ $fieldSlug }}"></i>
                                    </div>
                                @endif
                                <div class="media-input-wrapper" style="{{ media_exists($savedVal) ? 'display:none;' : '' }}">
                                    <input @if (isset($field[3])) required @endif type="file" accept="{{ $fieldExtra ?? 'image/*' }}" class="compress-image form-control-sm form-control-file" name="{{ $fieldSlug }}">
                                </div>
                            </div>
                        @elseif($fieldType === 'color')
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small mb-1">{{ str($fieldName)->headline() }}</label><br>
                                <input type="color" name="{{ $fieldSlug }}" class="form-control form-control-sm" style="width: 80px; height: 35px; padding: 2px;" value="{{ $displayVal ?: '#000000' }}">
                            </div>
                        @elseif($fieldType === 'textarea')
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small mb-1">{{ str($fieldName)->headline() }}</label>
                                <textarea @if(isset($field[3])) required @endif class="form-control form-control-sm" name="{{ $fieldSlug }}" rows="3" placeholder="Masukkan {{ $fieldName }}">{{ $displayVal }}</textarea>
                            </div>
                        @else
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small mb-1">{{ str($fieldName)->headline() }}</label>
                                <input @if(isset($field[3])) required @endif type="{{ $fieldType }}" class="form-control form-control-sm" name="{{ $fieldSlug }}" placeholder="Masukkan {{ $fieldName }}" value="{{ $displayVal }}">
                            </div>
                        @endif
                    @endforeach

                    <div class="border-top pt-3 mt-4">
                        <button name="save_setting" value="true" class="btn btn-primary font-weight-bold px-4">
                            <i class="fa fa-save mr-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @include('cms::backend.layout.js')
@endsection
