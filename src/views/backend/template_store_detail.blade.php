@extends('cms::backend.layout.app', ['title' => 'Detail Template: ' . $template['name']])

@section('content')
@php
    $slug = $template['slug'] ?? Str::slug($template['name']);
    $isInstalled = \Illuminate\Support\Facades\File::isDirectory(resource_path('views/template/' . $slug));
    $isActive = (template() === $slug);
@endphp
    <!-- Hidden form for activating cloud template -->
    <form id="activate-template-form" action="{{ route('appearance.activate_template') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="slug" id="activate-template-slug">
    </form>

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('appearance.template_store') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali ke Template Cloud
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <!-- Carousel -->
                <div id="templateCarousel" class="carousel slide mb-4" data-ride="carousel">
                    <div class="carousel-inner rounded" style="background: #e9ecef;">
                        <!-- Thumbnail Utama -->
                        <div class="carousel-item active">
                            <img src="{{ $template['thumbnail'] }}" class="d-block w-100" alt="Thumbnail" style="max-height: 500px; object-fit: contain;">
                        </div>
                        
                        <!-- Screenshots -->
                        @if(!empty($template['screenshots']) && is_array($template['screenshots']))
                            @foreach($template['screenshots'] as $screenshot)
                                <div class="carousel-item">
                                    <img src="{{ $screenshot }}" class="d-block w-100" alt="Screenshot" style="max-height: 500px; object-fit: contain;">
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    @if(!empty($template['screenshots']) && count($template['screenshots']) > 0)
                    <a class="carousel-control-prev" href="#templateCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#templateCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                    @endif
                </div>

                <h3 class="mb-2">
                    {{ $template['name'] }}
                    @if($template['is_premium'])
                        <span class="badge badge-warning text-dark ml-2" style="font-size: 0.5em; vertical-align: middle;"><i class="fa fa-star"></i> PREMIUM</span>
                        @if(isset($template['price']) && $template['price'] > 0)
                            <span class="badge badge-dark ml-1" style="font-size: 0.5em; vertical-align: middle;">Rp {{ number_format($template['price'], 0, ',', '.') }}</span>
                        @endif
                    @else
                        <span class="badge badge-success ml-2" style="font-size: 0.5em; vertical-align: middle;"><i class="fa fa-gift"></i> GRATIS</span>
                    @endif
                </h3>
                <p class="text-muted mb-2">v{{ $template['version'] ?? '1.0' }} | Oleh: <strong>{{ $template['author'] ?? 'Unknown' }}</strong></p>
                    
                @if(!empty($template['category']))
                    <div class="mb-3">
                        <span class="badge badge-info px-3 py-2" style="font-size: 0.9rem;">{{ $template['category'] }}</span>
                    </div>
                @endif

                <h5 class="mt-4">Deskripsi:</h5>
                <p class="text-justify" style="white-space: pre-line;">{{ $template['description'] ?? 'Tidak ada deskripsi.' }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm sticky-top" style="top: 80px;">
            <div class="card-body text-center">
                @if($template['is_premium'])
                    <p class="text-muted mb-1">Harga Template</p>
                    <h3 class="text-primary font-weight-bold mb-4">Rp {{ number_format($template['price'] ?? 0, 0, ',', '.') }}</h3>
                @else
                    <h3 class="text-success font-weight-bold mb-4">GRATIS</h3>
                @endif

                @if(!empty($template['demo_url']))
                    <a href="{{ $template['demo_url'] }}" target="_blank" class="btn btn-outline-dark btn-block mb-3">
                        <i class="fa fa-desktop"></i> Lihat Live Demo
                    </a>
                @endif

                <hr>

                @if($template['is_premium'])
                    @if($template['is_purchased'])
                        <div class="alert alert-success py-2 mb-3">
                            <i class="fa fa-check-circle"></i> Anda sudah membeli template ini
                        </div>
                        @if($isActive)
                            <button type="button" class="btn btn-secondary btn-lg btn-block" disabled>
                                <i class="fa fa-check-circle"></i> Sedang Aktif
                            </button>
                        @elseif($isInstalled)
                            <button type="button" class="btn btn-success btn-lg btn-block activate-template-btn" data-slug="{{ $slug }}">
                                Aktifkan Template
                            </button>
                        @else
                            <button type="button" class="btn btn-success btn-lg btn-block install-cloud-btn" data-url="{{ $template['url'] }}">
                                <i class="fa fa-download"></i> Install Sekarang
                            </button>
                        @endif
                    @else
                        <a href="{{ $template['checkout_url'] }}?return_url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-primary btn-lg btn-block">
                            <i class="fa fa-shopping-cart"></i> Beli Sekarang
                        </a>
                        <small class="text-muted d-block mt-2">Lisensi ini berlaku untuk domain: <strong>{{ request()->getHost() }}</strong></small>
                    @endif
                @else
                    @if($isActive)
                        <button type="button" class="btn btn-secondary btn-lg btn-block" disabled>
                            <i class="fa fa-check-circle"></i> Sedang Aktif
                        </button>
                    @elseif($isInstalled)
                        <button type="button" class="btn btn-success btn-lg btn-block activate-template-btn" data-slug="{{ $slug }}">
                            Aktifkan Template
                        </button>
                    @else
                        <button type="button" class="btn btn-success btn-lg btn-block install-cloud-btn" data-url="{{ $template['url'] }}">
                            <i class="fa fa-download"></i> Install Sekarang
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Form Install Template -->
<form id="install-cloud-form" action="{{ route('appearance.install_cloud') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="url" id="install-cloud-url">
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.install-cloud-btn').click(function() {
        var url = $(this).data('url');
        if (confirm('Lanjutkan install template ini?')) {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menginstall...');
            $('#install-cloud-url').val(url);
            $('#install-cloud-form').submit();
        }
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
@endpush
