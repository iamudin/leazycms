@extends('cms::backend.layout.app', ['title' => 'Detail Template: ' . $template['name']])

@section('content')
@php
    $slug = $template['slug'] ?? Str::slug($template['name']);
    $isInstalled = \Illuminate\Support\Facades\File::isDirectory(resource_path('views/template/' . $slug));
    $isActive = (template() === $slug);
@endphp
@push('styles')
<style>
    .detail-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        background: #fff;
        overflow: hidden;
    }
    .carousel-inner {
        border-radius: 20px 20px 0 0;
        background: #f8fafc;
    }
    .carousel-item img {
        width: 100%;
        height: 450px;
        object-fit: contain;
        background-color: #f1f5f9;
    }
    .carousel-control-prev, .carousel-control-next {
        width: 8%;
        opacity: 0;
        transition: opacity 0.3s;
    }
    #templateCarousel:hover .carousel-control-prev, 
    #templateCarousel:hover .carousel-control-next {
        opacity: 1;
    }
    .carousel-control-prev-icon, .carousel-control-next-icon {
        background-color: rgba(0,0,0,0.5);
        border-radius: 50%;
        padding: 20px;
        background-size: 50%;
    }
    .detail-body {
        padding: 40px;
    }
    .detail-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.5px;
    }
    .badge-modern {
        padding: 8px 16px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        vertical-align: middle;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .badge-premium { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #000; }
    .badge-free { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #fff; }
    .badge-price { background: #1e293b; color: #fff; }
    
    .meta-text {
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
    }
    .desc-title {
        font-weight: 700;
        font-size: 1.2rem;
        color: #334155;
        margin-top: 30px;
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }
    .desc-title::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 40px;
        height: 3px;
        background: var(--theme-primary, #4361ee);
        border-radius: 2px;
    }
    .desc-content {
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.7;
        white-space: pre-line;
    }
    .sidebar-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        background: #fff;
        padding: 30px;
    }
    .price-display {
        font-size: 2.5rem;
        font-weight: 900;
        background: linear-gradient(135deg, var(--theme-primary, #4361ee) 0%, var(--theme-primary-dark, #3a0ca3) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }
    .price-display-free {
        font-size: 2.5rem;
        font-weight: 900;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }
    .btn-action-main {
        padding: 15px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
        border: none;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .btn-action-main:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(0,0,0,0.15);
    }
    .btn-gradient-primary {
        background: linear-gradient(135deg, var(--theme-primary, #4361ee) 0%, var(--theme-primary-dark, #3a0ca3) 100%);
        color: white;
    }
    .btn-gradient-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    .btn-gradient-secondary {
        background: #e2e8f0;
        color: #64748b;
        box-shadow: none;
    }
    .btn-gradient-secondary:hover {
        transform: none;
        box-shadow: none;
        background: #cbd5e1;
    }
    .btn-outline-action {
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 600;
        border: 2px solid #e2e8f0;
        color: #475569;
        transition: all 0.2s;
        background: transparent;
    }
    .btn-outline-action:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
    }
    .status-alert {
        border-radius: 12px;
        border: none;
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        font-weight: 600;
        padding: 15px;
    }
</style>
@endpush

<!-- Hidden form for activating cloud template -->
<form id="activate-template-form" action="{{ route('appearance.activate_template') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="slug" id="activate-template-slug">
</form>

<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('appearance.template_store') }}" class="btn btn-light shadow-sm" style="border-radius: 30px; padding: 8px 20px; font-weight: 600;">
            <i class="fa fa-arrow-left mr-2"></i> Kembali ke Template Store
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="detail-card">
            <!-- Carousel -->
            <div id="templateCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <!-- Thumbnail Utama -->
                    <div class="carousel-item active">
                        <img src="{{ $template['thumbnail'] }}" alt="Thumbnail">
                    </div>
                    
                    <!-- Screenshots -->
                    @if(!empty($template['screenshots']) && is_array($template['screenshots']))
                        @foreach($template['screenshots'] as $screenshot)
                            <div class="carousel-item">
                                <img src="{{ $screenshot }}" alt="Screenshot">
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

            <div class="detail-body">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                    <h1 class="detail-title mb-0">{{ $template['name'] }}</h1>
                    <div class="mt-2 mt-md-0">
                        @if($template['is_premium'])
                            <span class="badge-modern badge-premium"><i class="fa fa-star"></i> PREMIUM</span>
                            @if(isset($template['price']) && $template['price'] > 0)
                                <span class="badge-modern badge-price ml-1">Rp {{ number_format($template['price'], 0, ',', '.') }}</span>
                            @endif
                        @else
                            <span class="badge-modern badge-free"><i class="fa fa-gift"></i> GRATIS</span>
                        @endif
                    </div>
                </div>
                
                <p class="meta-text mb-4">
                    <i class="fa fa-code-fork mr-1"></i> Versi {{ $template['version'] ?? '1.0' }} 
                    <span class="mx-2">&bull;</span> 
                    <i class="fa fa-user-circle-o mr-1"></i> Dibuat oleh <strong>{{ $template['author'] ?? 'Unknown' }}</strong>
                </p>
                    
                @if(!empty($template['category']))
                    <div class="mb-4">
                        <span class="badge badge-light px-3 py-2 border text-secondary" style="border-radius: 8px; font-size: 0.9rem;">
                            <i class="fa fa-tag mr-1"></i> {{ $template['category'] }}
                        </span>
                    </div>
                @endif

                <h4 class="desc-title">Deskripsi Template</h4>
                <div class="desc-content mt-3">
                    {{ $template['description'] ?? 'Tidak ada deskripsi rinci untuk template ini.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="sidebar-card sticky-top" style="top: 80px;">
            <div class="text-center">
                <p class="text-muted font-weight-bold text-uppercase tracking-wide mb-2" style="letter-spacing: 2px; font-size: 0.8rem;">Investasi Desain</p>
                
                @if($template['is_premium'])
                    <h2 class="price-display">Rp {{ number_format($template['price'] ?? 0, 0, ',', '.') }}</h2>
                @else
                    <h2 class="price-display-free">GRATIS</h2>
                @endif

                <hr class="my-4" style="border-color: #f1f5f9;">

                @if(!empty($template['demo_url']))
                    <a href="{{ $template['demo_url'] }}" target="_blank" class="btn btn-outline-action btn-block mb-3">
                        <i class="fa fa-desktop mr-2"></i> Lihat Live Demo
                    </a>
                @endif

                @if($template['is_premium'])
                    @if(isset($template['is_purchased']) && $template['is_purchased'])
                        <div class="status-alert mb-4">
                            <i class="fa fa-check-circle fa-lg mb-2"></i><br>
                            Anda sudah memiliki lisensi template ini
                        </div>
                        @if($isActive)
                            <button type="button" class="btn btn-action-main btn-gradient-secondary btn-block" disabled>
                                <i class="fa fa-check-circle mr-2"></i> Sedang Aktif
                            </button>
                        @elseif($isInstalled)
                            <button type="button" class="btn btn-action-main btn-gradient-success btn-block activate-template-btn" data-slug="{{ $slug }}">
                                <i class="fa fa-power-off mr-2"></i> Aktifkan Template
                            </button>
                        @else
                            <button type="button" class="btn btn-action-main btn-gradient-primary btn-block install-cloud-btn" data-url="{{ $template['url'] }}">
                                <i class="fa fa-cloud-download mr-2"></i> Install Sekarang
                            </button>
                        @endif
                    @else
                        <a href="{{ $template['checkout_url'] }}?return_url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-action-main btn-gradient-primary btn-block mb-3">
                            <i class="fa fa-shopping-cart mr-2"></i> Beli Sekarang
                        </a>
                        <div class="p-3 bg-light rounded text-left">
                            <small class="text-muted d-block text-center">
                                <i class="fa fa-lock mr-1"></i> Lisensi aman & terverifikasi untuk:<br>
                                <strong class="text-dark">{{ request()->getHost() }}</strong>
                            </small>
                        </div>
                    @endif
                @else
                    @if($isActive)
                        <button type="button" class="btn btn-action-main btn-gradient-secondary btn-block" disabled>
                            <i class="fa fa-check-circle mr-2"></i> Sedang Aktif
                        </button>
                    @elseif($isInstalled)
                        <button type="button" class="btn btn-action-main btn-gradient-success btn-block activate-template-btn" data-slug="{{ $slug }}">
                            <i class="fa fa-power-off mr-2"></i> Aktifkan Template
                        </button>
                    @else
                        <button type="button" class="btn btn-action-main btn-gradient-primary btn-block install-cloud-btn" data-url="{{ $template['url'] }}">
                            <i class="fa fa-cloud-download mr-2"></i> Install Sekarang
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
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Menginstall...');
            $('#install-cloud-url').val(url);
            $('#install-cloud-form').submit();
        }
    });

    $('.activate-template-btn').click(function() {
        var slug = $(this).data('slug');
        if (confirm('Yakin ingin mengaktifkan template ini?')) {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Mengaktifkan...');
            $('#activate-template-slug').val(slug);
            $('#activate-template-form').submit();
        }
    });
});
</script>
@endpush
