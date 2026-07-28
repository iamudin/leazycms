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
            Backup akan diekspor sebagai file <b>.zip</b> berisi file SQL (Smart SQL) dan media <b>storage/</b> ter-embed.
            Import akan mengekstrak file ZIP dan merestore data secara statis (menggunakan ID asli).
            <div style="margin-top:6px">
                <b>Scope:</b> {{ $scope ?? '-' }} @if(!empty($tenant)) • <b>Tenant:</b> {{ $tenant->domain ?? $tenant->name ?? $tenant->id }} @endif • <b>Host:</b> {{ $host ?? request()->getHost() }}
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card" style="padding:15px">
            <h4 style="margin-top:0;margin-bottom:15px"><i class="fa fa-download"></i> Export</h4>
            @if(in_array(($exportStatus['state'] ?? null), ['queued', 'running'], true))
                <div class="alert alert-warning" style="margin-bottom:10px">
                    <b>Status:</b> Sedang diproses ({{ ($exportStatus['state'] ?? '') }}). {{ $exportStatus['message'] ?? '' }}
                    @if(!empty($exportStatus['queued_at']))<div><small>Queued: {{ $exportStatus['queued_at'] }}</small></div>@endif
                    @if(!empty($exportStatus['started_at']))<div><small>Started: {{ $exportStatus['started_at'] }}</small></div>@endif
                    <div><small>Queue: {{ $exportStatus['queue_connection'] ?? '-' }} / {{ $exportStatus['queue_name'] ?? '-' }} • Pending: {{ $exportStatus['pending_jobs'] ?? '-' }}</small></div>
                </div>
            @elseif(($exportStatus['state'] ?? null) === 'done')
                <div class="alert alert-success" style="margin-bottom:10px">
                    <b>Status:</b> Selesai. {{ $exportStatus['message'] ?? '' }} <a href="{{ route('backup.download') }}">Unduh file backup</a>
                </div>
            @elseif(($exportStatus['state'] ?? null) === 'failed')
                <div class="alert alert-danger" style="margin-bottom:10px">
                    <b>Status:</b> Gagal. {{ $exportStatus['message'] ?? '' }}
                    <div><small>Queue: {{ $exportStatus['queue_connection'] ?? '-' }} / {{ $exportStatus['queue_name'] ?? '-' }} • Pending: {{ $exportStatus['pending_jobs'] ?? '-' }}</small></div>
                </div>
            @endif
            <form action="{{ URL::full() }}" method="post">
                @csrf
                <input type="hidden" name="action" value="export">
                @if(empty($tenant))
                    <div class="form-group" style="margin-bottom:10px">
                        <label style="display:block">
                            <input type="checkbox" name="include_users" value="1" checked>
                            Include tabel users
                        </label>
                    </div>
                @endif
                <button class="btn btn-primary btn-sm" type="submit" {{ in_array(($exportStatus['state'] ?? null), ['queued', 'running'], true) ? 'disabled' : '' }}><i class="fa fa-download"></i> Proses Export (Queue)</button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card" style="padding:15px">
            <h4 style="margin-top:0;margin-bottom:15px"><i class="fa fa-upload"></i> Import</h4>
            @if(in_array(($importStatus['state'] ?? null), ['queued', 'running'], true))
                <div class="alert alert-warning" style="margin-bottom:10px">
                    <b>Status:</b> Sedang diproses ({{ ($importStatus['state'] ?? '') }}). {{ $importStatus['message'] ?? '' }}
                    @if(!empty($importStatus['queued_at']))<div><small>Queued: {{ $importStatus['queued_at'] }}</small></div>@endif
                    @if(!empty($importStatus['started_at']))<div><small>Started: {{ $importStatus['started_at'] }}</small></div>@endif
                    <div><small>Queue: {{ $importStatus['queue_connection'] ?? '-' }} / {{ $importStatus['queue_name'] ?? '-' }} • Pending: {{ $importStatus['pending_jobs'] ?? '-' }}</small></div>
                </div>
            @elseif(($importStatus['state'] ?? null) === 'done')
                <div class="alert alert-success" style="margin-bottom:10px">
                    <b>Status:</b> Selesai. {{ $importStatus['message'] ?? '' }}
                </div>
            @elseif(($importStatus['state'] ?? null) === 'failed')
                <div class="alert alert-danger" style="margin-bottom:10px">
                    <b>Status:</b> Gagal. {{ $importStatus['message'] ?? '' }}
                    <div><small>Queue: {{ $importStatus['queue_connection'] ?? '-' }} / {{ $importStatus['queue_name'] ?? '-' }} • Pending: {{ $importStatus['pending_jobs'] ?? '-' }}</small></div>
                </div>
            @endif
            <form id="importBackupForm" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action" value="import">
                <div class="form-group">
                    <label>File Backup (.zip)</label>
                    <input type="file" name="backup_file" id="backupFileInput" accept=".zip" class="form-control" required {{ in_array(($importStatus['state'] ?? null), ['queued', 'running'], true) ? 'disabled' : '' }}>
                </div>

                <div class="progress mb-3" id="importProgressContainer" style="display:none; height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="importProgressBar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>

                <div class="form-group">
                    <label style="display:block">
                        <input type="checkbox" name="replace" value="1" checked>
                        Replace data scope ini sebelum import
                    </label>
                    @if(empty($scope) || $scope === 'induk')
                        <label style="display:block">
                            <input type="checkbox" name="replace_non_tenant" value="1">
                            Replace seluruh table non-tenant (semua data hilang)
                        </label>
                        <label style="display:block">
                            <input type="checkbox" name="overwrite_users" value="1">
                            Timpa data Users dengan yang ada di backup
                        </label>
                    @endif
                </div>
                <button class="btn btn-success btn-sm" id="importSubmitBtn" type="submit" {{ in_array(($importStatus['state'] ?? null), ['queued', 'running'], true) ? 'disabled' : '' }}><i class="fa fa-upload"></i> Jalankan Import (Queue)</button>
            </form>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-6">
        <div class="card" style="padding:15px">
            <h4 style="margin-top:0;margin-bottom:15px"><i class="fa fa-folder-open"></i> Daftar Backup Lokal</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-nowrap table-sm">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Tanggal</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($localBackups ?? [] as $backup)
                            <tr>
                                <td>{{ $backup['name'] }}</td>
                                <td>{{ number_format($backup['size'] / 1048576, 2) }} MB</td>
                                <td>{{ date('Y-m-d H:i:s', $backup['time']) }}</td>
                                <td>
                                    <a href="{{ route('backup.download.local', ['file' => $backup['name']]) }}" class="btn btn-sm btn-primary" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">Belum ada file backup lokal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Backup lokal otomatis dibersihkan setiap 7 hari.</small>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card" style="padding:15px">
            <h4 style="margin-top:0;margin-bottom:15px"><i class="fab fa-google-drive"></i> Daftar Backup Google Drive</h4>
            @if(get_option('google_drive_client_id') && get_option('google_drive_client_secret') && get_option('google_drive_refresh_token'))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Ukuran</th>
                                <th>Tanggal</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gdriveBackups ?? [] as $backup)
                                <tr>
                                    <td>{{ $backup['name'] }}</td>
                                    <td>{{ number_format($backup['size'] / 1048576, 2) }} MB</td>
                                    <td>{{ date('Y-m-d H:i:s', $backup['time']) }}</td>
                                    <td>
                                        <a href="{{ route('backup.download.gdrive', ['id' => $backup['id']]) }}" class="btn btn-sm btn-primary" title="Download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">Belum ada file backup di Google Drive.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Backup Google Drive otomatis dibersihkan setiap 7 hari.</small>
            @else
                <div class="alert alert-warning">
                    Google Drive belum dikonfigurasi. Silakan konfigurasi melalui menu <b>Setting -> Website</b> pada tab Google Drive.
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    @include('cms::backend.layout.js')
    @if(in_array(($exportStatus['state'] ?? null), ['queued', 'running'], true) || in_array(($importStatus['state'] ?? null), ['queued', 'running'], true))
        <script>
            setTimeout(function () {
                window.location.reload();
            }, 5000);
        </script>
    @endif

@endpush
@endsection
