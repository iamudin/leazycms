@extends('cms::backend.layout.app', ['title' => 'Plugin Store'])

@section('content')
@push('styles')
<style>
    /* Modern UI Customizations */
    .store-hero {
        background: linear-gradient(135deg, var(--theme-primary, #20c997) 0%, var(--theme-primary-dark, #11998e) 100%);
        border-radius: 16px;
        padding: 30px;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(32, 201, 151, 0.2);
    }
    .search-panel {
        background: white;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.02);
    }
    .search-panel .form-control {
        border: 1px solid #edf2f9;
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px 15px;
        transition: all 0.2s;
    }
    .search-panel .form-control:focus {
        background: white;
        border-color: var(--theme-primary, #20c997);
        box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.1);
    }
    .store-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .store-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .store-card-img-wrapper {
        position: relative;
        overflow: hidden;
        padding-top: 60%; /* 16:9 Aspect Ratio Approx */
    }
    .store-card-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .store-card:hover .store-card-img {
        transform: scale(1.05);
    }
    .glass-badge {
        position: absolute;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.2);
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .badge-tl { top: 15px; left: 15px; }
    .badge-tr { top: 15px; right: 15px; }
    
    .badge-premium { background: linear-gradient(135deg, rgba(255, 215, 0, 0.9) 0%, rgba(255, 165, 0, 0.9) 100%); color: #000; }
    .badge-free { background: linear-gradient(135deg, rgba(40, 167, 69, 0.9) 0%, rgba(32, 201, 151, 0.9) 100%); color: #fff; }
    .badge-active { background: rgba(0, 123, 255, 0.9); color: white; }
    .badge-installed { background: rgba(255, 255, 255, 0.9); color: #28a745; }
    
    .store-card-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .store-card-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #2b3445;
        margin-bottom: 5px;
    }
    .store-card-meta {
        font-size: 0.8rem;
        color: #7d879c;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .store-card-desc {
        color: #4b566b;
        font-size: 0.85rem;
        line-height: 1.5;
        margin-bottom: 20px;
        flex-grow: 1;
    }
    .store-card-footer {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }
    .btn-modern {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 15px;
        transition: all 0.2s;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border: none;
    }
    .btn-modern:hover {
        transform: translateY(-2px);
    }
    .btn-primary-gradient {
        background: linear-gradient(135deg, var(--theme-primary, #20c997) 0%, var(--theme-primary-dark, #11998e) 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(32, 201, 151, 0.2);
    }
    .btn-primary-gradient:hover {
        color: white;
        box-shadow: 0 6px 15px rgba(32, 201, 151, 0.3);
    }
    .btn-success-gradient {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(17, 153, 142, 0.2);
    }
    .btn-success-gradient:hover {
        color: white;
        box-shadow: 0 6px 15px rgba(17, 153, 142, 0.3);
    }
    .btn-outline-modern {
        background: white;
        border: 1px solid #edf2f9;
        color: #4b566b;
    }
    .btn-outline-modern:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--theme-primary, #20c997);
    }
    .price-tag {
        font-weight: 800;
        font-size: 0.85rem;
        margin-top: 4px;
        display: block;
        text-align: right;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
</style>
@endpush

<div class="store-hero d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h2 class="font-weight-bold mb-1"><i class="fa fa-plug mr-2"></i> Plugin Store</h2>
        <p class="mb-0 text-white-50">Temukan dan install plugin terbaik untuk menambah fitur website Anda</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="{{ route('admin.plugins') }}" class="btn btn-light btn-modern text-primary shadow-sm" style="border-radius: 30px; padding: 10px 20px;">
            <i class="fa fa-arrow-left"></i> Plugin Lokal
        </a>
    </div>
</div>

<div class="search-panel mb-4">
    <form action="{{ route('admin.plugins.store') }}" method="GET" class="row align-items-center">
        <div class="col-md-5 mb-2 mb-md-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0"><i class="fa fa-search text-muted"></i></span>
                </div>
                <input type="text" name="search" class="form-control border-left-0 pl-0 bg-light" placeholder="Cari nama atau deskripsi plugin..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <select name="category" class="form-control bg-light">
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
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary-gradient btn-modern w-100 py-2"><i class="fa fa-filter"></i> Filter</button>
        </div>
    </form>
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
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="store-card">
                    <div class="store-card-img-wrapper">
                        @if($isActive)
                            <div class="glass-badge badge-active badge-tl"><i class="fa fa-check-circle"></i> AKTIF</div>
                        @elseif($isInstalled)
                            <div class="glass-badge badge-installed badge-tl"><i class="fa fa-check"></i> TERINSTALL</div>
                        @endif
                        
                        @if(isset($template['is_premium']) && $template['is_premium'])
                            <div class="glass-badge badge-premium badge-tr text-right">
                                <div><i class="fa fa-star"></i> PREMIUM</div>
                                @if(isset($template['price']) && $template['price'] > 0)
                                    <span class="price-tag">Rp {{ number_format($template['price'], 0, ',', '.') }}</span>
                                @endif
                            </div>
                        @else
                            <div class="glass-badge badge-free badge-tr"><i class="fa fa-gift"></i> GRATIS</div>
                        @endif
                        
                        <img src="{{ $template['thumbnail'] }}" class="store-card-img" alt="{{ $template['name'] }}">
                    </div>
                    
                    <div class="store-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="store-card-title text-truncate" title="{{ $template['name'] }}">{{ $template['name'] }}</h5>
                        </div>
                        
                        <div class="store-card-meta">
                            <span><i class="fa fa-code-fork text-muted"></i> v{{ $template['version'] ?? '1.0' }}</span>
                            &bull;
                            <span class="text-truncate"><i class="fa fa-user-circle-o text-muted"></i> {{ $template['author'] ?? 'Unknown' }}</span>
                        </div>
                        
                        @if(!empty($template['category']))
                            <div class="mb-2">
                                <span class="badge badge-light px-2 py-1 border text-muted" style="border-radius: 6px;">{{ $template['category'] }}</span>
                            </div>
                        @endif
                        
                        <p class="store-card-desc">
                            {{ Str::limit($template['description'] ?? 'Tidak ada deskripsi', 75) }}
                        </p>

                        <div class="store-card-footer">
                            <a href="{{ route('admin.plugins.detail', $template['id']) }}" class="btn btn-outline-modern btn-modern w-50">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                            
                            @if(isset($template['is_premium']) && $template['is_premium'])
                                @if(isset($template['is_purchased']) && $template['is_purchased'])
                                    @if($isActive)
                                        <button type="button" class="btn btn-light btn-modern w-50 text-muted" disabled>Aktif</button>
                                    @elseif($isInstalled)
                                        <button type="button" class="btn btn-success-gradient btn-modern w-50 toggle-plugin-btn" data-slug="{{ $slug }}" data-action="enable">Aktifkan</button>
                                    @else
                                        <button type="button" class="btn btn-success-gradient btn-modern w-50 install-cloud-btn" data-url="{{ $template['url'] }}"><i class="fa fa-download"></i> Install</button>
                                    @endif
                                @else
                                    <a href="{{ $template['checkout_url'] }}?return_url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-primary-gradient btn-modern w-50">
                                        <i class="fa fa-shopping-cart"></i> Beli
                                    </a>
                                @endif
                            @else
                                @if($isActive)
                                    <button type="button" class="btn btn-light btn-modern w-50 text-muted" disabled>Aktif</button>
                                @elseif($isInstalled)
                                    <button type="button" class="btn btn-success-gradient btn-modern w-50 toggle-plugin-btn" data-slug="{{ $slug }}" data-action="enable">Aktifkan</button>
                                @else
                                    <button type="button" class="btn btn-primary-gradient btn-modern w-50 install-cloud-btn" data-url="{{ $template['url'] }}"><i class="fa fa-download"></i> Install</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12 text-center py-5 bg-white shadow-sm" style="border-radius: 16px;">
            <img src="{{ url('backend/images/not-found.svg') }}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/7486/7486747.png'" width="120" class="mb-3 opacity-50">
            <h5 class="text-muted font-weight-bold">Tidak Ada Plugin</h5>
            <p class="text-black-50">Silakan sesuaikan filter pencarian Anda atau periksa koneksi ke Cloud Host.</p>
        </div>
    @endif
</div>

<!-- Pagination Links -->
<div class="row mt-3">
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
                this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
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
