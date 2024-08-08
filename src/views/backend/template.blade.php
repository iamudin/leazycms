@extends('views::backend.layout.app', ['title' => 'Template'])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 style="font-weight:normal"> <i class="fa fa-paint-brush" aria-hidden="true"></i> Template
                @if (request('select'))
                    <div class="pull-right">
                        <a href="{{ url()->current() }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip"
                            title="Kembali"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
                        @if (get_option('template') != $detail->path ||
                                !file_exists(resource_path('views/template/' . get_option('template') . '/modules.php')))
                            <form action="{{ url()->full() }}" method="post" style="display:inline">
                                @csrf
                                <button onclick="return confirm('Terapkan Tema ?')" name="apply"
                                    value="{{ request('select') }}" class="btn btn-outline-primary btn-sm"
                                    data-toggle="tooltip" title="Terapkan"> <i class="fa fa-paint-brush" aria-hidden></i>
                                    Terapkan</button>
                            </form>
                        @endif
                    </div>
                @else
                    <button href="" class="btn btn-outline-primary btn-sm pull-right" data-toggle="tooltip"
                        title="Upload Template Baru"> <i class="fa fa-upload" aria-hidden></i> Upload</button>
                @endif
            </h3>
            <br>
            @if (request('select'))
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <img src="{{ $detail->thumb }}" class="w-100 rounded" alt="">
                    </div>
                    <div class="col-lg-4">
                        <ul class="list-group">
                            <li class="list-group-item "> <i class="fa fa-info" aria-hidden="true"></i> Informasi</li>
                            <li class="list-group-item text-muted">Nama <span
                                    class="pull-right"><b>{{ $detail->name }}</b></span></li>
                            <li class="list-group-item text-muted">Versi <span
                                    class="pull-right"><b>{{ $detail->version }}</b></span></li>
                            <li class="list-group-item text-muted">Type <span
                                    class="pull-right"><b>{{ $detail->type }}</b></span></li>
                            <li class="list-group-item text-muted">Author <span
                                    class="pull-right"><b>{{ $detail->author->name }}</b></span></li>
                            <li class="list-group-item text-muted">Email <span
                                    class="pull-right"><b>{{ $detail->author->email }}</b></span></li>
                            <li class="list-group-item text-muted">Rating <span class="pull-right"><i
                                        class="fa fa-star text-warning" aria-hidden="true"></i>
                                    <i class="fa fa-star text-warning" aria-hidden="true"></i> <i
                                        class="fa fa-star text-warning" aria-hidden="true"></i> <i
                                        class="fa fa-star text-warning" aria-hidden="true"></i> <i
                                        class="fa fa-star text-warning" aria-hidden="true"></i> 5.0
                                </span></li>


                        </ul>
                    </div>
                </div>
            @else
                <div class="jumbotron">
                    <small class="badge badge-danger">Beta</small>
                    <h1 class="display-3">Selamat Datang ! </h1>
                    <p>Temukan dan dapatkan template website sesuai kebutuhan Website Resmi milik anda.</p>
                </div>
                <div class="row mt-4">
                    <div class="col-lg-2 mb-4">
                        <div class="list-group">

                            @foreach (['Semua', 'Profile Organisasi', 'Instansi Pemerintah', 'Desa', 'Sekolah', 'Ponpes', 'Masjid'] as $row)
                                <a class="list-group-item list-group-item-action {{ !request('type') && $loop->first ? 'active' : '' }} {{ request('type') && request('type') == Str::slug($row) ? 'active' : '' }}"
                                    href="{{ url()->current() . '?type=' . Str::slug($row) }}"><small>{{ $row }}

                                        </span></small></a>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-10">
                        @if ($templates->count())
                            <div style="border-left:3px solid green" class="alert alert-success">
                                Menampilkan hasil template
                            </div>
                        @endif
                        <div class="row">

                            @forelse($templates as $row)
                                <div class="col-lg-4 mb-4">
                                    <div class="card">
                                        <h4 class="card-header">{{ $row->name }}</h4>
                                        <img style="height: 200px; width: 100%; display: block;" src="{{ $row->thumb }}"
                                            alt="Card image">
                                        <div class="card-body">

                                            @if (
                                                $row->path == get_option('template') &&
                                                    file_exists(resource_path('views/template/' . get_option('template') . '/modules.php')))
                                                <span class="text-success"> <i class="fa fa-check-circle"
                                                        aria-hidden="true"></i> Diterapkan </span>
                                            @else
                                                <a class="btn btn-sm btn-outline-warning" href="#">Terapkan</a>
                                            @endif <a class="btn btn-sm btn-outline-info  pull-right "
                                                href="{{ url()->current() . '?type=' . $row->type . '&select=' . $row->id }}">Detail</a>
                                            <span class="pull-right"></span>
                                        </div>
                                        <div class="card-footer text-muted">Author : {{ $row->author->name }} <span
                                                class="pull-right">Versi {{ $row->version }}</span></div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-lg-12 mb-4">
                                    <div style="border-left:3px solid orange" class="alert alert-warning">
                                        Template Belum Tersedia
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
