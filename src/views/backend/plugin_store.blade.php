@extends('cms::backend.layout.app', ['title' => 'Plugin Store'])

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-0"><i class="fa fa-plug text-primary"></i> Plugin Store</h4>
        <p class="text-muted">Temukan dan install plugin terbaik untuk website Anda</p>
    </div>
    <div class="col-md-6 text-md-right">
        <a href="{{ route('admin.plugins') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar Plugin
        </a>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.plugins.store') }}" method="GET" class="row">
            <div class="col-md-5 mb-2">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau deskripsi plugin..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 mb-2">
                <select name="category" class="form-control">
                    <option value="">Semua Kategori</option>
                    @php
                        $reqCategory = request('category');
                        $availableCategories = isset($categories) && is_array($categories) ? $categories : [];
                    @endphp
                    @foreach($availableCategories as $cat)
                        <option value="{{ $cat }}" {{ $reqCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                    @if($reqCategory && !in_array($reqCategory, $availableCategories))
                        <option value="{{ $reqCategory }}" selected>{{ $reqCategory }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i> Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    @php
        $installedPlugins = array_map('basename', File::directories(resource_path('plugins')));
        $activePlugins = array_diff($installedPlugins, get_disabled_plugins());
    @endphp

    @if(isset($paginatedTemplates) && count($paginatedTemplates) > 0)
        @foreach($paginatedTemplates as $template)
            @php
                $slug = $template['slug'] ?? Str::slug($template['name']);
                $isInstalled = in_array($slug, $installedPlugins);
                $isActive = in_array($slug, $activePlugins);
            @endphp
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm position-relative">
                    @if($isActive)
                        <div class="position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                            <span class="badge badge-primary shadow"><i class="fa fa-check-circle"></i> AKTIF</span>
                        </div>
                    @elseif($isInstalled)
                        <div class="position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                            <span class="badge badge-success shadow"><i class="fa fa-check"></i> TERINSTALL</span>
                        </div>
                    @endif
                    @if(isset($template['is_premium']) && $template['is_premium'])
                        <div class="position-absolute text-right" style="top: 10px; right: 10px; z-index: 10;">
                            <span class="badge badge-warning shadow"><i class="fa fa-star"></i> PREMIUM</span>
                            @if(isset($template['price']) && $template['price'] > 0)
                                <div class="mt-1">
                                    <span class="badge badge-dark shadow">Rp {{ number_format($template['price'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="position-absolute text-right" style="top: 10px; right: 10px; z-index: 10;">
                            <span class="badge badge-success shadow"><i class="fa fa-gift"></i> GRATIS</span>
                        </div>
                    @endif
                    <img src="{{ $template['thumbnail'] }}" class="card-img-top" alt="{{ $template['name'] }}" style="height: 180px; object-fit: cover;">
                    <div class="card-body text-center p-3 d-flex flex-column">
                        <h5 class="card-title mb-1">{{ $template['name'] }}</h5>
                        @if(!empty($template['category']))
                            <div class="mb-2">
                                <span class="badge badge-info">{{ $template['category'] }}</span>
                            </div>
                        @endif
                        <p class="text-muted small mb-2">v{{ $template['version'] ?? '1.0' }} | {{ $template['author'] ?? 'Unknown' }}</p>
                        @if(isset($template['description']) && $template['description'])
                            <p class="text-muted small mb-3 flex-grow-1">{{ Str::limit($template['description'], 80) }}</p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        <div class="mt-auto d-flex justify-content-between">
                            <a href="{{ route('admin.plugins.detail', $template['id']) }}" class="btn btn-outline-info w-50 mr-1">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                            @if(isset($template['is_premium']) && $template['is_premium'])
                                @if(isset($template['is_purchased']) && $template['is_purchased'])
                                    @if($isActive)
                                        <button type="button" class="btn btn-secondary w-50 ml-1" disabled>
                                            Aktif
                                        </button>
                                    @elseif($isInstalled)
                                        <button type="button" class="btn btn-success w-50 ml-1 toggle-plugin-btn" data-slug="{{ $slug }}" data-action="enable">
                                            Aktifkan
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success w-50 ml-1 install-cloud-btn" data-url="{{ $template['url'] }}">
                                            <i class="fa fa-download"></i> Install
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ $template['checkout_url'] }}?return_url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-primary w-50 ml-1">
                                        <i class="fa fa-shopping-cart"></i> Beli
                                    </a>
                                @endif
                            @else
                                @if($isActive)
                                    <button type="button" class="btn btn-secondary w-50 ml-1" disabled>
                                        Aktif
                                    </button>
                                @elseif($isInstalled)
                                    <button type="button" class="btn btn-success w-50 ml-1 toggle-plugin-btn" data-slug="{{ $slug }}" data-action="enable">
                                        Aktifkan
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary w-50 ml-1 install-cloud-btn" data-url="{{ $template['url'] }}">
                                        <i class="fa fa-download"></i> Install
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12 text-center my-5">
            <p class="text-muted">Tidak ada plugin ditemukan di cloud.</p>
        </div>
    @endif
</div>

<!-- Pagination Links -->
<div class="row">
    <div class="col-12 d-flex justify-content-center">
        {{ $paginatedTemplates->links('pagination::bootstrap-4') }}
    </div>
</div>

<form id="install-cloud-form" action="{{ route('admin.plugins.install_cloud') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="url" id="install-cloud-url">
</form>

<form id="toggle-plugin-form" action="{{ route('admin.plugins') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="plugin_name" id="toggle-plugin-name">
    <input type="hidden" name="action" id="toggle-plugin-action">
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.install-cloud-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Yakin ingin mendownload dan menginstall plugin ini?')) {
                document.getElementById('install-cloud-url').value = this.dataset.url;
                const origHTML = this.innerHTML;
                this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menginstall...';
                this.disabled = true;
                document.getElementById('install-cloud-form').submit();
            }
        });
    });
    $('.toggle-plugin-btn').click(function() {
        var slug = $(this).data('slug');
        var action = $(this).data('action');
        if (confirm('Yakin ingin ' + (action == 'enable' ? 'mengaktifkan' : 'menonaktifkan') + ' plugin ini?')) {
            $('#toggle-plugin-name').val(slug);
            $('#toggle-plugin-action').val(action);
            $('#toggle-plugin-form').submit();
        }
    });
});
</script>
@include('cms::backend.layout.js')
@endpush
@endsection
