@extends('cms::backend.layout.app', ['title' => 'Data Web'])
@section('content')
    <form class="" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal"> <i class="fa fa-list-alt" aria-hidden="true"></i> {{str($slug)->headline()}}
                    <div class="btn-group pull-right"><button name="save_setting" value="true"
                            class="btn btn-primary btn-sm"> <i class="fa fa-save" aria-hidden></i> Simpan</button>
                        <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-sm"> <i class="fa fa-undo"
                                aria-hidden></i> Kembali</a>
                    </div>
                </h3>

                    @foreach ($data as $field)
                        @if ($field[1] == 'file')
                            <span>{{ str($field[0])->headline() }}</span><br>

                                @if (media_exists(get_option(_us($field[0]))))
                                    <a href="{{get_option(_us($field[0])) }}"
                                        class="btn btn-sm btn-outline-primary mb-2">{{ basename(get_option(_us($field[0]))) }}</a> <i
                                        title="Hapus data" class="fa fa-trash text-danger pointer"
                                        onclick="media_destroy('{{ get_option(_us($field[0])) }}')"></i><br>
                                @else
                                    <input @if (isset($field[2])) required @endif type="file" accept="{{ $field[3] ?? null }}"
                                        class="compress-image form-control-sm form-control-file mb-2" name="{{ _us($field[0]) }}">
                                @endif
                        @elseif($field[1] == 'textarea')
                            <span>{{ str($field[0])->headline() }}</span><br>

                            <textarea @if (isset($field[2])) required @endif class="form-control form-control-sm" name="{{_us($field[0])}}">
                                {{ get_option(_us($field[0])) }}
                            </textarea>
                        @elseif($field[1] == 'break')
                        <br>
                            <span style="font-weight:bold;margin-bottom:0;line-height:0px">{{ str($field[0])->headline() }}</span><br>

                            <hr style="margin-top:0;margin-bottom:10px;border:1px dashed #000">
                        @else
                            <span>{{ str($field[0])->headline() }}</span><br>

                                <input @if (isset($field[2])) required @endif type="{{ $field[1]  }}"
                                    class="form-control form-control-sm mb-2" name="{{ _us($field[0]) }}"
                                    placeholder="Masukkan {{ $field[0] }}" value="{{ get_option(_us($field[0])) }}">
                        @endif
                    @endforeach
            </div>
        </div>
    </form>
    @include('cms::backend.layout.js')
@endsection
