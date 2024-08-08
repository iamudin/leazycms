@extends('cms::backend.layout.app',['title'=>'Tampilan'])
@section('content')

<div class="row">
<div class="col-lg-12 mb-3"><h3 style="font-weight:normal;float: left;"> <i class="fa fa-paint-brush"></i> Tampilan </h3>
    <div class="pull-right">


        <form action="" style="display:inline" method="post" enctype="multipart/form-data">
            @csrf
            <input type="file" class="template" name="template" style="display: none">
        <button type="button" onclick="$('.template').click()" class="btn btn-outline-primary btn-sm"> <i class="fa fa-upload"></i> Upload Template</button>
        <a href="" class="btn btn-outline-info btn-sm"> <i class="fa fa-download"></i> Sample</a>
        </form>
        <a href="{{route('panel.dashboard')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
    </div>

</div>



<div class="col-lg-2">
    <h6>Modul</h6>
    <div class="accordion mb-3" id="accordionExample" >
        @foreach(collect(get_module())->where('public',true)->where('web.detail',true) as $row)
        <div class="card">
          <div class="card-header" id="heading{{ $row->name }}" style="padding:0">
              <span class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#{{ $row->name }}" aria-expanded="true" aria-controls="{{ $row->name }}">
               <i class="fa {{ $row->icon }}"></i> {{ $row->title }}
              </span>
          </div>

          <div id="{{ $row->name }}" class="collapse {{ $loop->first ? 'show':'' }}" aria-labelledby="heading{{ $row->name }}" data-parent="#accordionExample">
            <div class="card-body py-2" >
                <ul style="margin:0;padding:0;">
              @if($row->web->index)
              <li>
                <a href="javascript::void(0)" onclick="$('.preview').attr('src','{{ url($row->name) }}')">View INDEX</a>
              </li>
              @endif
              @if($row->web->detail)
              <li>
                @php $detail = query()->detail($row->name) @endphp
                <a href="javascript::void(0)" onclick="$('.preview').attr('src','{{ url($detail->url ?? '/') }}')">View DETAIL</a>
            </li>
              @endif
              @if($row->form->category)
              @php $category = query()->index_category($row->name)->first() @endphp
              <li>
                <a href="javascript::void(0)"  onclick="$('.preview').attr('src','{{ url($category->url ?? '/') }}')">View CATEGORY</a>

              </li>
              @endif
            </ul>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <h6>Info Template</h6>

      <ul class="list-group mb-3">
    @foreach(config('modules.config.template_info')??[] as $row)
    <li class="list-group-item" style="padding:4px 10px">
      <small>{{ str($row[0])->headline() }}</small><br>
      <h6>{{ $row[1] }}</h6>
    </li>

    @endforeach
    <li class="list-group-item" style="padding:0;">
        <a target="_blank" href="{{ route('appearance.editor') }}" class="btn btn-outline-warning btn-sm btn-md w-100"> <i class="fa fa-code"></i> Edit Template</a>

            </li>
  </ul>

</div>
<div class="col-lg-10">

<iframe  src="{{ url('/') }}" frameborder="0" class="w-100 preview" style="height: 80vh;border-radius:5px;border:4px solid rgb(48, 48, 48)"></iframe>

</div>
</div>

</div>

@endsection
