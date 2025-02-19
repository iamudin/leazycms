@extends('cms::backend.layout.app', ['title' => 'Data Web'])
@section('content')
    <form class="" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal"> <i class="fa fa-table" aria-hidden="true"></i> Data Web
                    <div class="btn-group pull-right"><button name="save_setting" value="true"
                            class="btn btn-primary btn-sm"> <i class="fa fa-save" aria-hidden></i> Simpan</button>
                        <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-sm"> <i class="fa fa-undo"
                                aria-hidden></i> Kembali</a>
                    </div>
                </h3>
                <br>

                @foreach (collect(config('modules.config.option')) as $key => $row)
                    <label {{ !$loop->first ? 'style=margin-top:10px' : '' }}><b>{{ $key }}</b></label>
                    <hr style="margin:0;padding:0">
                    @foreach ($row as $field)
                        <small>{{ $field[0] }}</small><br>
                        @if ($field[1] != 'text' && is_array($field[1]))
                            @if (media_exists(get_option(_us($field[0]))))
                                <a href="{{get_option(_us($field[0])) }}"
                                    class="btn btn-sm btn-outline-primary">{{ basename(get_option(_us($field[0]))) }}</a> <i
                                    title="Hapus data" class="fa fa-trash text-danger pointer"
                                    onclick="media_destroy('{{ get_option(_us($field[0])) }}')"></i><br>
                            @else
                                <input @if (isset($field[2])) required @endif type="file"
                                    class="form-control-sm form-control-file" name="{{ _us($field[0]) }}">
                            @endif
                        @else
                            <input @if (isset($field[2])) required @endif type="text"
                                class="form-control form-control-sm" name="{{ _us($field[0]) }}"
                                placeholder="Masukkan {{ $field[0] }}" value="{{ get_option(_us($field[0])) }}">
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </form>
    @include('cms::backend.layout.js')
@endsection
