@extends('cms::backend.layout.app', ['title' => 'Polling Opsional'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-list aria-hidden="true"></i> Opsional
            </h3>
            <div class="pull-right">

                <a href="{{route('polling')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>
        </div>
        <div class="col-lg-4">
            @include('cms::backend.layout.error')
            @include('cms::backend.polling.option.form')
        </div>
        <div class="col-lg-8">
            <div class="alert alert-info">
               Polling : {{ $polling->title }}
            </div>
            <table  class="display table table-hover table-bordered" style="background:#f7f7f7;width:100%;font-size:small">
                <thead style="text-transform:uppercase;color:#444">
                    <tr>

                        <th style="vertical-align: middle;">Gambar</th>
                        <th style="vertical-align: middle">Nama</th>
                        <th style="vertical-align: middle" width="10px">Urutan</th>
                        <th style="vertical-align: middle" width="10px">Aksi</th>
                    </tr>
                </thead>
                <tbody style="background:#fff">
                    @foreach($data as $option)
                    <tr>
                        <td width="80"> <img src="{{ $option->image && media_exists($option->image)? $option->image : noimage() }}" width="80" alt="" class="src"> </td>
                        <td>{{ $option->name }}</td>
                        <td>{{ $option->sort }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('polling.option.edit',[$polling->id,$option->id]) }}" class="btn btn-sm btn-warning"> <i class="fa fa-edit"></i> </a>
                                <button  class="btn btn-sm btn-danger" onclick="deleteAlert('{{ route('polling.option.destroy',$option->id) }}')"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

@include('cms::backend.layout.js')
@endsection
