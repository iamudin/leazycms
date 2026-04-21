<div class="modal" id="filter-modal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"> <i class="fa fa-filter"></i> Filter</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">


    <form  class="row mb-3" action="{{ route('print.posts') }}" method="post">
        @csrf
    <div class="col-12">
        <small>Status Pos</small>
        <select id="status" name="status" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">--pilih status--</option>
            @foreach(['publish', 'disematkan', 'draft', 'sampah'] as $row)
            @php 
            $query = query()->onType(get_post_type());
    $count = match ($row) {
        'publish' => $publish,
        'disematkan' => $query->wherePinned(1)->count(),
        'draft' => $draft,
        'sampah' => $trash
    };
            @endphp
            <option value="{{ $row }}">{{ str($row)->headline() }} ({{ $count }})</option>
            @endforeach
        </select>
    </div>
    @if(current_module()->form->category)
    <div class="col-12">
        <small>Category</small>
        <select id="category_id" name="category_id" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">--pilih kategori--</option>
            @foreach(query()->index_category(get_post_type()) as $row)
            <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
            @endforeach
        </select>
    </div>
    @endif
    @if(current_module()->form->tag)
    <div class="col-12">
        <small>Tags</small>
        <select id="tag_id" name="tag_id" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">Pilih</option>
            @foreach(query()->index_tags(get_post_type()) as $row)
            <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
            @endforeach
        </select>
    </div>
    @endif
    @if($parent = current_module()->form->post_parent)
    <div class="col-12">
        <small>{{ $parent[0] }}</small>
        <select id="parent_id"  data-live-search="true"  class="selectpicker form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            @php 
            $parentdata = query()->with('parent.parent.parent')->onType($parent[1])->published()->select('title', 'id', 'parent_id', 'category_id');
            @endphp
            @if(isset($parent[2]))
            @php 
            $parentdata = $parentdata->whereHas('category', function ($q) use ($parent) {
            $q->whereSlug($parent[2]);
        })->get()
            @endphp
            @else 
            @php 
            $parentdata = $parentdata->get();
            @endphp
            @endif
            <option value="">Pilih</option>
            @foreach($parentdata as $row)
            <option value="{{ $row->id }}">{{ $row->title }} {{ $row->parent ? ' - ' . $row->parent->title . ($row->parent->parent ? ' - ' . $row->parent->parent->title : '') : ''}}</option>
            @endforeach
        </select>
    </div>
    @push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endpush
@push('scripts')
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
@endpush
@endif

<div class="col-12">
    <small>Penerbit</small>
    <select id="user_id" name="user_id" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
        <option value="">Pilih</option>
        @foreach(query()->index_author(current_module()->name) as $row)
        <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
        @endforeach
    </select>
</div>
<div class="col-12 mt-2">
   <i class="fa fa-clock"></i> Waktu Penerbitan
</div>
<div class="col-6">

    <small>Mulai</small>
    <input type="date" name="from_date" id="from_date" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()" class="form-control form-control-sm">
</div>
<div class="col-6">
    <small>Sampai</small>
    <input type="date" name="to_date" id="to_date" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()" class="form-control form-control-sm">
</div>
<input type="hidden" name="type" value="{{ get_post_type() }}">
<button type="submit" class="submit-filter d-none"></button>
</form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn  btn-sm btn-primary" onclick="$('.submit-filter').click()" > <i class="fa fa-print"></i> Cetak</button>
          <button type="button" class="btn  btn-sm btn-warning" onclick="window.location.href='{{ url()->current() }}'">Reset</button>
        </div>
      </div>
    </div>
  </div>

