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
                                    <tr class="plugin-row" 
                                        data-plugin="{{ $plugin['name'] }}" 
                                        data-repository="{{ $plugin['repository'] ?? '' }}" 
                                        data-version="{{ $plugin['version'] ?? '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $plugin['title'] }}</strong>
                                            @if(!empty($plugin['version']))
                                                <br><small class="text-muted">Versi: {{ $plugin['version'] }}</small>
                                            @endif
                                            <div class="update-info mt-1 text-info" style="display:none; font-size: 12px;"></div>
                                        </td>
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
                                            
                                            <!-- Form update tersembunyi -->
                                            <form action="{{ route('admin.plugins.update') }}" method="POST" class="d-inline form-update-plugin" id="form-update-{{ $plugin['name'] }}">
                                                @csrf
                                                <input type="hidden" name="plugin_name" value="{{ $plugin['name'] }}">
                                                <button type="submit" class="btn btn-sm btn-info btn-do-update" style="display: none;"
                                                    onclick="return confirm('Update plugin ini? File lama akan tertimpa.')">
                                                    <i class="fa fa-refresh"></i> Update
                                                </button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.plugin-row');
            
            // Fungsi membandingkan versi (misal v1.0.1 > v1.0.0)
            function compareVersions(v1, v2) {
                const cleanV1 = v1.replace(/^v/, '');
                const cleanV2 = v2.replace(/^v/, '');
                const parts1 = cleanV1.split('.').map(Number);
                const parts2 = cleanV2.split('.').map(Number);
                
                for (let i = 0; i < Math.max(parts1.length, parts2.length); i++) {
                    const num1 = parts1[i] || 0;
                    const num2 = parts2[i] || 0;
                    if (num1 > num2) return 1;
                    if (num1 < num2) return -1;
                }
                return 0;
            }

            rows.forEach(row => {
                const plugin = row.getAttribute('data-plugin');
                const repo = row.getAttribute('data-repository');
                const currentVersion = row.getAttribute('data-version');
                
                if (repo && currentVersion) {
                    fetch(`https://api.github.com/repos/${repo}/tags`)
                        .then(response => {
                            if (!response.ok) throw new Error('Cannot fetch tags');
                            return response.json();
                        })
                        .then(tags => {
                            if (tags && tags.length > 0) {
                                const latestTag = tags[0].name;
                                if (compareVersions(latestTag, currentVersion) > 0) {
                                    const updateInfo = row.querySelector('.update-info');
                                    updateInfo.innerHTML = `Update tersedia: <strong>${latestTag}</strong>`;
                                    updateInfo.style.display = 'block';
                                    
                                    const updateBtn = row.querySelector('.btn-do-update');
                                    updateBtn.style.display = 'inline-block';
                                    updateBtn.innerHTML = `<i class="fa fa-refresh"></i> Update ke ${latestTag}`;
                                }
                            }
                        })
                        .catch(error => console.error('Error fetching github tags for ' + plugin + ':', error));
                }
            });
        });
    </script>
@endsection