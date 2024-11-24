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


<div class="row mb-3">
    <div class="col-12">
        <small>Status Pos</small>
        <select id="status" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">Pilih</option>
            @foreach(['publish','disematkan','draft','sampah'] as $row)
            <option value="{{ $row }}">{{ str($row)->headline() }}</option>
            @endforeach
        </select>
    </div>
    @if(current_module()->form->category)
    <div class="col-12">
        <small>Category</small>
        <select id="category_id" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">Pilih</option>
            @foreach(query()->index_category(get_post_type()) as $row)
            <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
            @endforeach
        </select>
    </div>
    @endif
    @if($parent = current_module()->form->post_parent)
    <div class="col-12">
        <small>{{ $parent[0] }}</small>
        <select id="parent_id"  data-live-search="true"  class="selectpicker form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">Pilih</option>
            @foreach(query()->with('parent.parent.parent')->onType($parent[1])->published()->select('title','id','parent_id')->get() as $row)
            <option value="{{ $row->id }}">{{ $row->title }} {{ $row->parent? ' - '.$row->parent->title.($row->parent->parent? ' - '.$row->parent->parent->title:'') : ''}}</option>
            @endforeach
        </select>
    </div>
@endif

<div class="col-12">
    <small>Penerbit</small>
    <select id="user_id" class="form-control form-control-sm" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
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
    <input type="date" id="from_date" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()" class="form-control form-control-sm">
</div>
<div class="col-6">
    <small>Sampai</small>
    <input type="date" id="to_date" onchange="if(this.value) $('.datatable').DataTable().ajax.reload()" class="form-control form-control-sm">
</div>
</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-warning" onclick="window.location.href='{{ url()->current() }}'">Reset</button>
        </div>
      </div>
    </div>
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
