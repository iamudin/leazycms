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

    @php 
    $multisiteEnabled = config('modules.multisite_enabled'); 
    @endphp

    <form class="row mb-3" action="{{ route('print.posts') }}" method="post">
        @csrf

    @if($multisiteEnabled)
    <div class="col-12 mb-2">
        <label class="font-weight-bold text-dark mb-1">
            <i class="fa fa-globe text-primary"></i> Domain Tenant <span class="badge badge-primary ml-1">Pilihan Utama</span>
        </label>
        <select id="tenant_id" name="tenant_id" class="form-control form-control-sm border-primary" onchange="onTenantFilterChange(this.value)">
            <option value="">-- Pilih Domain Tenant Terlebih Dahulu --</option>
            @php 
            $tenants = \Leazycms\Web\Models\Tenant::whereIn('status', ['active', 'maintenance', 'suspended'])->orderBy('domain')->get();
            $tenantCounts = query()->onType(get_post_type())
                ->selectRaw('tenant_id, count(*) as total')
                ->groupBy('tenant_id')
                ->pluck('total', 'tenant_id');
            $mainCount = ($tenantCounts[null] ?? 0) + ($tenantCounts[''] ?? 0);
            @endphp
            <option value="main">Main Domain ({{ $mainCount }})</option>
            @foreach($tenants as $tenant)
            <option value="{{ $tenant->id }}">{{ $tenant->domain }} ({{ $tenantCounts[$tenant->id] ?? 0 }})</option>
            @endforeach
        </select>
        <small id="tenant-filter-alert" class="text-muted d-block mt-1 font-italic">
            <i class="fa fa-info-circle"></i> Pilih domain tenant terlebih dahulu untuk membuka opsi filter lainnya.
        </small>
        <hr class="mt-3 mb-2" style="border-top: 1px dashed #ccc;">
    </div>
    @endif

    <div class="col-12">
        <small>Status Pos</small>
        <select id="status" name="status" class="form-control form-control-sm tenant-dependent-filter" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">--pilih status--</option>
            @if(!$multisiteEnabled)
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
            @endif
        </select>
    </div>

    @if(current_module()->form->category)
    <div class="col-12">
        <small>Category</small>
        <select id="category_id" name="category_id" class="form-control form-control-sm tenant-dependent-filter" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">--pilih kategori--</option>
            @if(!$multisiteEnabled)
            @foreach(query()->index_category(get_post_type()) as $row)
            <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
            @endforeach
            @endif
        </select>
    </div>
    @endif

    @if(current_module()->form->tag)
    <div class="col-12">
        <small>Tags</small>
        <select id="tag_id" name="tag_id" class="form-control form-control-sm tenant-dependent-filter" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">Pilih</option>
            @if(!$multisiteEnabled)
            @foreach(query()->index_tags(get_post_type()) as $row)
            <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
            @endforeach
            @endif
        </select>
    </div>
    @endif

    @if($parent = current_module()->form->post_parent)
    <div class="col-12">
        <small>{{ $parent[0] }}</small>
        <select id="parent_id" data-live-search="true" class="selectpicker form-control form-control-sm tenant-dependent-filter" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
            <option value="">Pilih</option>
            @if(!$multisiteEnabled)
            @php 
            $parentdata = query()->with('parent.parent.parent')->onType($parent[1])->published()->select('title', 'id', 'parent_id', 'category_id');
            if(isset($parent[2])) {
                $parentdata = $parentdata->whereHas('category', function ($q) use ($parent) {
                    $q->whereSlug($parent[2]);
                })->get();
            } else {
                $parentdata = $parentdata->get();
            }
            @endphp
            @foreach($parentdata as $row)
            <option value="{{ $row->id }}">{{ $row->title }} {{ $row->parent ? ' - ' . $row->parent->title . ($row->parent->parent ? ' - ' . $row->parent->parent->title : '') : ''}}</option>
            @endforeach
            @endif
        </select>
    </div>
    @push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
@endpush
    @endif

<div class="col-12">
    <small>Penerbit</small>
    <select id="user_id" name="user_id" class="form-control form-control-sm tenant-dependent-filter" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()">
        <option value="">Pilih</option>
        @if(!$multisiteEnabled)
        @foreach(query()->index_author(current_module()->name) as $row)
        <option value="{{ $row->id }}">{{ $row->name }} ({{ $row->posts_count }})</option>
        @endforeach
        @endif
    </select>
</div>
<div class="col-12 mt-2">
   <i class="fa fa-clock"></i> Waktu Penerbitan
</div>
<div class="col-6">
    <small>Mulai</small>
    <input type="date" name="from_date" id="from_date" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()" class="form-control form-control-sm tenant-dependent-filter">
</div>
<div class="col-6">
    <small>Sampai</small>
    <input type="date" name="to_date" id="to_date" {{ $multisiteEnabled ? 'disabled' : '' }} onchange="if(this.value) $('.datatable').DataTable().ajax.reload()" class="form-control form-control-sm tenant-dependent-filter">
</div>
<input type="hidden" name="type" value="{{ get_post_type() }}">
<button type="submit" class="submit-filter d-none"></button>
</form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-sm btn-primary" onclick="submitPrintFilter()"> <i class="fa fa-print"></i> Cetak</button>
          <button type="button" class="btn btn-sm btn-warning" onclick="window.location.href='{{ url()->current() }}'">Reset</button>
        </div>
      </div>
    </div>
  </div>

@push('scripts')
<script>
function submitPrintFilter() {
    @if(config('modules.multisite_enabled'))
    if ($('#tenant_id').length && !$('#tenant_id').val()) {
        if (typeof swal === 'function') {
            swal("Perhatian", "Silakan pilih domain tenant terlebih dahulu sebelum mencetak data!", "warning");
        } else {
            alert("Silakan pilih domain tenant terlebih dahulu sebelum mencetak data!");
        }
        return false;
    }
    @endif
    $('.submit-filter').click();
}

@if(config('modules.multisite_enabled'))
function onTenantFilterChange(tenantId) {
    if (!tenantId) {
        $('.tenant-dependent-filter').val('').prop('disabled', true);
        if (typeof $.fn.selectpicker !== 'undefined' && $('#parent_id').hasClass('selectpicker')) {
            $('#parent_id').selectpicker('refresh');
        }
        $('#tenant-filter-alert').html('<i class="fa fa-info-circle"></i> Pilih domain tenant terlebih dahulu untuk membuka opsi filter lainnya.').removeClass('text-success text-danger text-info').addClass('text-muted');
        $('.datatable').DataTable().ajax.reload();
        return;
    }

    $('#tenant-filter-alert').html('<i class="fa fa-spinner fa-spin"></i> Menyesuaikan opsi filter dengan domain tenant terpilih...').removeClass('text-muted text-danger text-success').addClass('text-info');
    $('.tenant-dependent-filter').prop('disabled', true);

    $.ajax({
        url: "{{ route('posts.filter_options') }}",
        type: "GET",
        data: {
            tenant_id: tenantId,
            type: "{{ get_post_type() }}"
        },
        success: function(res) {
            if (res.status) {
                // Status Pos
                let $status = $('#status');
                $status.empty().append('<option value="">--pilih status--</option>');
                $status.append(`<option value="publish">Publish (${res.statuses.publish || 0})</option>`);
                $status.append(`<option value="disematkan">Disematkan (${res.statuses.disematkan || 0})</option>`);
                $status.append(`<option value="draft">Draft (${res.statuses.draft || 0})</option>`);
                $status.append(`<option value="sampah">Sampah (${res.statuses.sampah || 0})</option>`);

                // Category
                let $cat = $('#category_id');
                if ($cat.length) {
                    $cat.empty().append('<option value="">--pilih kategori--</option>');
                    if (res.categories && res.categories.length) {
                        res.categories.forEach(function(c) {
                            $cat.append(`<option value="${c.id}">${c.name} (${c.posts_count || 0})</option>`);
                        });
                    }
                }

                // Tags
                let $tag = $('#tag_id');
                if ($tag.length) {
                    $tag.empty().append('<option value="">Pilih</option>');
                    if (res.tags && res.tags.length) {
                        res.tags.forEach(function(t) {
                            $tag.append(`<option value="${t.id}">${t.name} (${t.posts_count || 0})</option>`);
                        });
                    }
                }

                // Parent
                let $parent = $('#parent_id');
                if ($parent.length) {
                    $parent.empty().append('<option value="">Pilih</option>');
                    if (res.parents && res.parents.length) {
                        res.parents.forEach(function(p) {
                            $parent.append(`<option value="${p.id}">${p.title}</option>`);
                        });
                    }
                    if (typeof $.fn.selectpicker !== 'undefined' && $parent.hasClass('selectpicker')) {
                        $parent.selectpicker('refresh');
                    }
                }

                // Authors / Users
                let $user = $('#user_id');
                if ($user.length) {
                    $user.empty().append('<option value="">Pilih</option>');
                    if (res.authors && res.authors.length) {
                        res.authors.forEach(function(u) {
                            $user.append(`<option value="${u.id}">${u.name} (${u.posts_count || 0})</option>`);
                        });
                    }
                }

                $('.tenant-dependent-filter').prop('disabled', false);
                if (typeof $.fn.selectpicker !== 'undefined' && $('#parent_id').hasClass('selectpicker')) {
                    $('#parent_id').selectpicker('refresh');
                }

                $('#tenant-filter-alert').html('<i class="fa fa-check-circle"></i> Opsi filter telah disesuaikan untuk domain tenant ini.').removeClass('text-info text-danger text-muted').addClass('text-success');

                $('.datatable').DataTable().ajax.reload();
            }
        },
        error: function() {
            $('#tenant-filter-alert').html('<i class="fa fa-exclamation-triangle"></i> Gagal memuat opsi filter tenant.').removeClass('text-info text-muted text-success').addClass('text-danger');
            $('.tenant-dependent-filter').prop('disabled', false);
            $('.datatable').DataTable().ajax.reload();
        }
    });
}
@endif
</script>
@endpush
