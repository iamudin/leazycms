<div class="tile">
    <h4>
        @if(!$option)
        <i class="fa fa-plus"></i> Tambah
        @else
        <i class="fa fa-edit"></i> Edit
        @endif
    </h4>
    @if ($option)

@endif

        <form autocomplete="off" action="{{ $option ?  route('polling.option.update',[$polling->id,$option->id]): route('polling.option.store',$polling->id)}}" method="post" enctype="multipart/form-data">
            @csrf
            @if($option)
            @method('PUT')
            @endif
            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Nama</label>
                  <input class="form-control form-control-sm " required name="name" type="text" placeholder="Masukkan Nama Opsi" value="{{$option ? $option->name : old('name')}}">
            </div>

            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Gambar </label>
                 <input type="file" class="form-control-file" name="image" accept="image/png,image/jpeg">
            </div>

            <div class="form-group mt-2  mb-2 text-right">
                <button type="submit" class="btn btn-primary btn-sm">
                    @if(!$option)
                    <i class="fa fa-plus"></i> Tambah
                    @else
                    <i class="fa fa-save"></i> Simpan
                    @endif
                   </button>
                   @if($option)
                    <a href="{{ route('polling.option.index',$polling->id) }}" class="btn btn-danger btn-sm">
                       <i class="fa fa-undo"></i> Batal</a>
                    @endif
            </div>
</form>
</div>

