<div class="tile">
    <h4>
        @if(!$category)
        <i class="fa fa-plus"></i> Tambah
        @else
        <i class="fa fa-edit"></i> Edit
        @endif
    </h4>
    @if ($category && current_module()->public)
    <div style="border-left:3px solid green" class="alert alert-success"><b>URL : </b><a
            title="Kunjungi URL" data-toggle="tooltip" href="{{ url($category->url) }}"
            target="_blank"><i><u>{{ url($category->url) }}</u></i></a><br> <span
            title="Klik Untuk Menyalin alamat URL Kategori" data-toggle="tooltip"
            class="pointer copy badge badge-primary" data-copy="{{ url($category->url) }}"><i
                class="fa fa-copy" aria-hidden></i> <b>Salin</b></span></div>
@endif
        <form action="{{ $category ?  route(get_post_type().'.category.update',$category->id): route(get_post_type().'.category.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            @if($category)
            @method('PUT')
            @endif
            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Nama</label>
                  <input class="form-control form-control-sm " name="name" type="text" placeholder="Masukkan Nama Kategori" value="{{$category ? $category->name : old('name')}}">
            </div>

            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Keterangan</label>
                  <textarea class="form-control form-control-sm" name="description" placeholder="Masukkan Keterangan">{{$category ? $category->description : old('description')}}</textarea>
            </div>
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Urutan</label>
                @php
                $count = \Leazycms\Web\Models\Category::whereType(current_module()->name)->whereStatus('publish')->count();
                @endphp
                <select name="sort" id="" class="form-control form-control-select form-control-sm">
                    <option value="0" >Pilih</option>
                @for($i=1; $i <= ( $count > 0 && !$category ? $count+1 : $count); $i++)
                <option value="{{ $i }}"{{ $category && $category->sort==$i ? 'selected':'' }}>{{$i}}</option>

                  @endfor
                </select>
            </div>
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Icon</label>
                @if($category && $category->icon && media_exists($category->icon))
                <br><img src="{{ url($category->icon) }}" style="height: 70px" class="img-thumbnail"> <a href="javascript:void(0)" onclick="media_destroy('{{ $category->icon }}')" class="btn-danger btn-sm"> <i class="fa fa-trash text-white"></i> </a>
                @else
                  <input accept="image/png,image/jpeg"   class="compress-image form-control-sm form-control-file " name="icon"  type="file" value="{{$category?->icon}}">
                @endif
            </div>
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Status</label><br>
                @foreach(['publish','draft'] as $row)
                  <input name="status"  type="radio" value="{{$row}}" {{ $category && $category->status==$row ? 'checked':'' }}> {{ str($row)->headline() }} &nbsp; &nbsp;
                  @endforeach
            </div>
            <div class="form-group mt-2  mb-2 text-right">
                <button type="submit" class="btn btn-primary btn-sm">
                     @if(!$category)
                     <i class="fa fa-plus"></i> Tambah
                     @else
                     <i class="fa fa-save"></i> Simpan
                     @endif
                    </button>
                    @if($category)
                    <a href="{{ route(get_post_type().'.category') }}" class="btn btn-danger btn-sm">
                        <i class="fa fa-undo"></i> Batal

                       </a>
                     @endif

            </div>
</form>
</div>
@push('scripts')
@include('cms::backend.layout.js')
@endpush
