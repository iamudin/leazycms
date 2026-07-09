@extends('cms::backend.layout.app', ['title' => 'Cloud Template Store'])

@section('content')
    <!-- Hidden form for activating cloud template -->
    <form id="activate-template-form" action="{{ route('appearance.activate_template') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="slug" id="activate-template-slug">
    </form>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Cloud Template Store</h4>
        <a href="{{ route('appearance') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Tampilan
        </a>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form action="{{ route('appearance.template_store') }}" method="GET" class="row">
            <div class="col-md-5 mb-2">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau deskripsi template..." value="{{ request('search') }}">
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
    @if(count($paginatedTemplates) > 0)
        @foreach($paginatedTemplates as $template)
            @php
                $slug = $template['slug'] ?? Str::slug($template['name']);
                $isInstalled = \Illuminate\Support\Facades\File::isDirectory(resource_path('views/template/' . $slug));
                $isActive = (template() === $slug);
            @endphp
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm position-relative">
                    @if($isActive)
                        <div class="position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                            <span class="badge badge-primary shadow"><i class="fa fa-check-circle"></i> SEDANG AKTIF</span>
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
                            <a href="{{ route('appearance.template_detail', $template['id']) }}" class="btn btn-outline-info w-50 mr-1">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                            @if(isset($template['is_premium']) && $template['is_premium'])
                                @if(isset($template['is_purchased']) && $template['is_purchased'])
                                    @if($isActive)
                                        <button type="button" class="btn btn-secondary w-50 ml-1" disabled>
                                            Aktif
                                        </button>
                                    @elseif($isInstalled)
                                        <button type="button" class="btn btn-success w-50 ml-1 activate-template-btn" data-slug="{{ $slug }}">
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
                                    <button type="button" class="btn btn-success w-50 ml-1 activate-template-btn" data-slug="{{ $slug }}">
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
            <p class="text-muted">Tidak ada template ditemukan di cloud.</p>
        </div>
    @endif
</div>

<!-- Pagination Links -->
<div class="row">
    <div class="col-12 d-flex justify-content-center">
        {{ $paginatedTemplates->links('pagination::bootstrap-4') }}
    </div>
</div>

<form id="install-cloud-form" action="{{ route('appearance.install_cloud') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="url" id="install-cloud-url">
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.install-cloud-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Yakin ingin mendownload dan menginstall template ini?')) {
                document.getElementById('install-cloud-url').value = this.dataset.url;
                const origHTML = this.innerHTML;
                this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menginstall...';
                this.disabled = true;
                document.getElementById('install-cloud-form').submit();
            }
        });
    });
    $('.activate-template-btn').click(function() {
        var slug = $(this).data('slug');
        if (confirm('Yakin ingin mengaktifkan template ini?')) {
            $('#activate-template-slug').val(slug);
            $('#activate-template-form').submit();
        }
    });
});
</script>
@include('cms::backend.layout.js')
@endpush
@endsection
