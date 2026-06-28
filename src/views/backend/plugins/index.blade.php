@extends('cms::backend.layout.app', ['title' => 'Manajemen Plugin'])
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-title-w-btn">
                    <h3 class="title">Manajemen Plugin</h3>
                    <p>
                        <button class="btn btn-primary icon-btn" type="button" data-toggle="modal"
                            data-target="#uploadPluginModal">
                            <i class="fa fa-upload"></i>Upload Plugin
                        </button>
                    </p>
                </div>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="pluginTable">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Plugin</th>
                                    <th>Deskripsi</th>
                                    <th>Direktori</th>
                                    <th width="150" class="text-center">Status</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plugins as $index => $plugin)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $plugin['title'] }}</strong></td>
                                        <td>{{  $plugin['description'] }}</td>
                                        <td><code>{{ $plugin['name'] }}</code></td>
                                        <td class="text-center">
                                            @if($plugin['status'])
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-danger">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ admin_url('plugins') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="plugin_name" value="{{ $plugin['name'] }}">
                                                @if($plugin['status'])
                                                    <input type="hidden" name="action" value="disable">
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Nonaktifkan plugin ini? Semua akses ke plugin ini akan ditutup.')">
                                                        <i class="fa fa-ban"></i> Nonaktifkan
                                                    </button>
                                                @else
                                                    <input type="hidden" name="action" value="enable">
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                        onclick="return confirm('Aktifkan kembali plugin ini?')">
                                                        <i class="fa fa-check"></i> Aktifkan
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada plugin yang ter-install.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadPluginModal" tabindex="-1" role="dialog" aria-labelledby="uploadPluginModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadPluginModalLabel">Upload Plugin (.zip)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>File ZIP Plugin</label>
                            <input type="file" name="plugin_file" class="form-control" accept=".zip" required>
                            <small class="form-text text-muted">Pastikan file ZIP berisi tepat satu folder plugin di
                                dalamnya.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload & Install</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection