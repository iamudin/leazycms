@extends('cms::backend.layout.app',['title'=>'Backup & Restore'])
@section('content')
<div class="row">
    <div class="col-lg-12 mb-3">
        <h3 style="font-weight:normal">
            <i class="fa fa-database"></i> Backup & Restore
            <div class="btn-group pull-right">
                <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>
        </h3>
        <div class="alert alert-info">
            Backup akan diekspor sebagai file <b>.zip</b> berisi <b>database.json</b> (data + relasi) dan folder <b>storage/</b> (file fisik).
            Import akan membuat ulang data dengan ID baru lalu menghubungkan relasi otomatis.
            <div style="margin-top:6px">
                <b>Scope:</b> {{ $scope ?? '-' }} @if(!empty($tenant)) • <b>Tenant:</b> {{ $tenant->domain ?? $tenant->name ?? $tenant->id }} @endif • <b>Host:</b> {{ $host ?? request()->getHost() }}
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card" style="padding:15px">
            <h4 style="margin-top:0;margin-bottom:15px"><i class="fa fa-download"></i> Export</h4>
            <form action="{{ URL::full() }}" method="post">
                @csrf
                <input type="hidden" name="action" value="export">
                <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-download"></i> Download Backup</button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card" style="padding:15px">
            <h4 style="margin-top:0;margin-bottom:15px"><i class="fa fa-upload"></i> Import</h4>
            <form action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action" value="import">
                <div class="form-group">
                    <label>File Backup (.zip)</label>
                    <input type="file" name="backup_file" class="form-control" required>
                </div>
                <div class="form-group">
                    <label style="display:block">
                        <input type="checkbox" name="replace" value="1" checked>
                        Replace data scope ini sebelum import
                    </label>
                    @if(empty($tenant))
                        <label style="display:block;margin-top:6px">
                            <input type="checkbox" name="replace_non_tenant" value="1">
                            Replace juga tabel non-tenant (induk) sebelum import
                        </label>
                    @endif
                </div>
                <button class="btn btn-success btn-sm" type="submit" onclick="return confirm('Yakin import backup ini? Data akan dibuat ulang dan relasi akan disambungkan otomatis.')"><i class="fa fa-upload"></i> Jalankan Import</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    @include('cms::backend.layout.js')
@endpush
@endsection
