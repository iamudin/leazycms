@extends('cms::backend.layout.app', ['title' => 'File Manager'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-folder"></i> File Manager
            </h3>
            <div class="pull-right btn-group">

                <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>
        </div>
        <div class="col-lg-12 mb-3">
            @php
                $totalFiles = \Leazycms\FLC\Models\File::count();
                $totalSize = \Leazycms\FLC\Models\File::sum('file_size');
                
                $formattedSize = '0 B';
                if ($totalSize > 0) {
                    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $power = $totalSize > 0 ? floor(log($totalSize, 1024)) : 0;
                    $formattedSize = number_format($totalSize / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
                }
                
                $extCounts = \Leazycms\FLC\Models\File::pluck('file_name')->map(function($name) {
                    return strtolower(pathinfo($name, PATHINFO_EXTENSION));
                })->filter()->countBy()->sortDesc();
            @endphp
            <div class="alert alert-info py-2 mb-0 shadow-sm border-0" style="background-color: #e9f7fe; border-left: 4px solid #17a2b8 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <strong><i class="fa fa-chart-pie text-info mr-2"></i> Statistik Media:</strong> 
                        Terdapat <b>{{ number_format($totalFiles, 0, ',', '.') }}</b> file terunggah.
                    </div>
                    <div class="text-right">
                        <span class="badge badge-info px-3 py-2" style="font-size: 14px;">Total Ukuran: {{ $formattedSize }}</span>
                    </div>
                </div>
                
                @if($extCounts->count() > 0)
                <div class="mt-2 pt-2" style="border-top: 1px dashed rgba(23, 162, 184, 0.3);">
                    <span style="font-size: 12px;" class="text-muted mr-2">Berdasarkan Ekstensi:</span>
                    @foreach($extCounts as $ext => $count)
                        <span class="badge badge-light border mr-1 mb-1" style="font-size: 11px;">
                            <span class="text-info font-weight-bold">{{ strtoupper($ext) }}</span> : {{ $count }}
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        <div class="col-lg-12">
            {{ flc_file_manager() }}
        </div>
    </div>
@include('cms::backend.layout.js')
@endsection
