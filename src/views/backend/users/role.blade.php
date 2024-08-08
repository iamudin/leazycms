@extends('cms::backend.layout.app',['title'=>'Hak Akses'])
@section('content')
<div class="row">
<div class="col-lg-12 mb-3">
  <h3 style="font-weight:normal;float:left"><i class="fa fa-key aria-hidden="true"></i> Hak Akses
</h3>
<div class="pull-right">
    @if(Route::has('user'))

    <a href="{{route('user')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
    @endif
</div>
</div>
<div class="col-lg-12">
<form action="{{ route('role.update') }}" method="post">
    @csrf
<div class="row">

    @foreach(collect(get_module())->whereNotIn('name',['media','menu']) as $row)
    <div class="col-lg-4 mb-4">
        <div class="card">
            <h4 class="card-header bg-dark text-white"> <i class="fa {{ $row->icon }} }}"></i> {{ $row->title }}</h4>
            <div class="card-body">
                <label for="">Content:</label>
              <table class="table table-bordered table-striped" style="font-size:small">
                <thead>
                    <tr>
                    <th>Level</th>
                    <th>Index</th>
                    <th>Create</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            @foreach(explode(',',get_option('roles')) as $r)
            <tr>
                <td>{{ str($r)->headline() }}</td>
                @foreach(['index','create','update','delete'] as $r2)
                <td align="center">
                <input @if($role && collect($role)->where('level',$r)->where('module',$row->name)->where('action',$r2)->count()) checked='checked' @endif value="true" type="checkbox" name="{{ $r.'_'.$row->name.'_'.$r2 }}">
            </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
              </table>
              @if($row->form->category)
              <label for="">Category :</label>
              <table class="table table-bordered table-striped" style="font-size:small">
                <thead>
                    <tr>
                    <th>Level</th>
                    <th>Index</th>
                    <th>Create</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            @foreach(explode(',',get_option('roles')) as $r)
            <tr>
                <td>{{ str($r)->headline() }}</td>
                @foreach(['index','create','update','delete'] as $r2)
                <td align="center">
                <input @if($role && collect($role)->where('level',$r)->where('module','category'.$row->name)->where('action',$r2)->count()) checked='checked' @endif value="true" type="checkbox" name="{{ $r.'_category'.$row->name.'_'.$r2 }}">
            </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
              </table>
              @endif
            </div>
          </div>
    </div>
    @endforeach


</div>
    <button class="btn btn-primary btn-md pull-right"><i class="fa fa-save"></i> Simpan</button>
</form>

</div>
</div>

@include('cms::backend.layout.js')
@endsection
