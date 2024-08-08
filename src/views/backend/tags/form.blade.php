<div class="tile">
    <h4>
        @if(!$tag)
        <i class="fa fa-plus"></i> Tambah
        @else
        <i class="fa fa-edit"></i> Edit
        @endif
    </h4>
    @if ($tag)
    <div style="border-left:3px solid green" class="alert alert-success"><b>URL : </b><a
            title="Kunjungi URL" data-toggle="tooltip" href="{{ url($tag->url) }}"
            target="_blank"><i><u>{{ url($tag->url) }}</u></i></a> <span
            title="Klik Untuk Menyalin alamat URL Kategori" data-toggle="tooltip"
            class="pointer copy pull-right badge badge-primary" data-copy="{{ url($tag->url) }}"><i
                class="fa fa-copy" aria-hidden></i> <b>Salin</b></span></div>
@endif

        <form autocomplete="off" action="{{ $tag ?  route('tag.update',$tag->id): route('tag.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            @if($tag)
            @method('PUT')
            @endif
            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Nama</label>
                  <input class="form-control form-control-sm " name="name" type="text" placeholder="Masukkan Nama tag" value="{{$tag ? $tag->name : old('name')}}">
            </div>

            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Deskripsi [ <i class="text-danger">Keterangan Singkat tentang Tag ini</i> ]</label>
                  <textarea class="form-control form-control-sm " name="description"  placeholder="Masukkan Keterangan">{{$tag ? $tag->description : old('description')}}</textarea>
            </div>

            <div class="form-group mt-2  mb-2 text-right">
                <button type="submit" class="btn btn-primary btn-sm">
                    @if(!$tag)
                    <i class="fa fa-plus"></i> Tambah
                    @else
                    <i class="fa fa-save"></i> Simpan
                    @endif
                   </button>
                   @if($tag)
                   <a href="{{ route('tag') }}" class="btn btn-danger btn-sm">
                       <i class="fa fa-undo"></i> Batal

                      </a>
                    @endif
            </div>
</form>
</div>
@push('scripts')
@include('cms::backend.layout.js')
@endpush
