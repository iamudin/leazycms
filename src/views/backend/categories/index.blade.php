@extends('cms::backend.layout.app',['title'=>get_post_type('title_crud')])
@section('content')
<div class="row">
<div class="col-lg-12 mb-3">
  <h3 style="font-weight:normal;float:left"><i class="fa {{get_module_info('icon')}}" aria-hidden="true"></i> {{get_post_type('title_crud')}}
</h3>
<div class="pull-right">
    <a href="{{route(get_post_type())}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
</div>
@include('cms::backend.layout.error')
</div>

@if(auth()->user()->isAdmin() || (!auth()->user()->isAdmin() && $dothing))
<div class="col-lg-12 mb-3 d-flex justify-content-between align-items-center">
    <div id="bulkActions" style="display:none;">
        <div class="d-flex align-items-center" style="gap:8px;">
            <label class="mb-0 d-flex align-items-center" style="cursor:pointer; gap:6px; font-size:0.85rem;">
                <input type="checkbox" id="selectAllEmpty" style="width:16px;height:16px;cursor:pointer;">
                <span class="text-muted">Pilih Semua (0 Data)</span>
            </label>
            <button type="button" id="btnBulkDelete" class="btn btn-danger btn-sm" disabled>
                <i class="fa fa-trash"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>
    <div class="ml-auto">
        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
            <i class="fa fa-plus"></i> Tambah Kategori
        </button>
    </div>
</div>

<!-- Modal Kategori -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="categoryForm" action="" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="categoryMethod" value="POST">
            <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="categoryModalTitle"><i class="fa fa-plus-circle text-primary"></i> Tambah Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-1">Nama Kategori</label>
                        <input class="form-control" id="cat_name" name="name" type="text" placeholder="Masukkan Nama Kategori" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-1">Keterangan</label>
                        <textarea class="form-control" id="cat_description" name="description" rows="3" placeholder="Masukkan Keterangan"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold mb-1">Urutan</label>
                                @php
                                    $count = \Leazycms\Web\Models\Category::whereType(current_module()->name)->whereStatus('publish')->count();
                                @endphp
                                <select name="sort" id="cat_sort" class="form-control">
                                    <option value="0">Pilih</option>
                                    @for($i=1; $i <= $count + 1; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold mb-1">Status</label><br>
                                <div class="custom-control custom-radio custom-control-inline mt-1">
                                    <input type="radio" id="statusPublish" name="status" value="publish" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="statusPublish">Publish</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline mt-1">
                                    <input type="radio" id="statusDraft" name="status" value="draft" class="custom-control-input">
                                    <label class="custom-control-label" for="statusDraft">Draft</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-1">Ikon Kategori</label>
                        <div id="iconPreviewContainer" style="display: none;" class="mb-2">
                            <img id="cat_icon_preview" src="" style="height: 60px; object-fit: contain; background: #f8f9fa; border-radius: 8px; padding: 5px; border: 1px solid #dee2e6;">
                        </div>
                        <input accept="image/webp,image/gif,image/png,image/jpeg" class="compress-image form-control-file" name="icon" type="file">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah ikon.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="fa fa-save"></i> Simpan Kategori</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="col-lg-12">
    <div class="row" id="sortable-category-list">
        @php
            $gradients = [
                'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                'background: linear-gradient(135deg, #FF8008 0%, #FFC837 100%)',
                'background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%)',
                'background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%)',
                'background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)'
            ];
        @endphp

        @foreach($categories as $index => $cat)
            @php $bg = $gradients[$index % count($gradients)]; @endphp
            <div class="col-md-6 mb-3 px-2 category-sort-item" data-id="{{ $cat->id }}" draggable="true" style="cursor: move;">
                <div class="list-group h-100">
                    <div class="list-group-item d-flex align-items-center shadow-sm text-white border-0 h-100" style="border-radius: 10px; padding: 12px 15px; {{ $bg }};">
                        
                        {{-- Checkbox Bulk Delete (hanya untuk kategori 0 data) --}}
                        @if($cat->posts_count == 0)
                        <div class="mr-2" onclick="event.stopPropagation();">
                            <input type="checkbox" class="bulk-check" value="{{ $cat->id }}" style="width:18px;height:18px;cursor:pointer;accent-color:#dc3545;">
                        </div>
                        @endif

                        <!-- Drag Handle Handle Icon -->
                        <div class="mr-3 text-white-50 drag-handle" style="cursor: move;" title="Tarik & Lepas untuk mengubah urutan kategori">
                            <i class="fa fa-bars fa-lg"></i>
                        </div>

                        <!-- Icon Kategori -->
                        <div class="mr-3">
                            @if($cat->icon && media_exists($cat->icon))
                                <img src="{{ url($cat->icon) }}" style="height: 45px; width: 45px; object-fit: contain; background: rgba(255,255,255,0.25); border-radius: 8px; padding: 4px;" alt="icon">
                            @else
                                <div style="height: 45px; width: 45px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.25); border-radius: 8px;">
                                    <i class="fa fa-folder-open fa-lg"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Judul dan Info Kategori -->
                        <div class="flex-grow-1">
                            <h5 class="mb-0 font-weight-bold" style="letter-spacing: 0.5px;">{{ $cat->name }}</h5>
                            <small style="opacity: 0.9; font-size: 0.85rem;">
                                <i class="fa fa-file-text-o mr-1"></i> {{ $cat->posts_count }} Data Terkait
                            </small>
                        </div>

                        <!-- Badge Sort Order & Status -->
                        <div class="d-flex align-items-center">
                            <span class="badge bg-white text-dark px-3 py-2 shadow-sm mr-2 sort-number-badge" style="border-radius: 20px; font-size: 0.75rem; font-weight: bold;" title="Urutan Kolom Sort">
                                <i class="fa fa-sort text-primary mr-1"></i> #<span class="sort-val">{{ $cat->sort ?? ($index + 1) }}</span>
                            </span>

                            <span class="badge bg-white {{ $cat->status == 'publish' ? 'text-success' : 'text-secondary' }} px-3 py-2 shadow-sm mr-3" style="border-radius: 20px; font-size: 0.75rem;">
                                <i class="fa {{ $cat->status == 'publish' ? 'fa-check-circle' : 'fa-archive' }} mr-1"></i> {{ ucfirst($cat->status) }}
                            </span>
                            
                            <div class="btn-group shadow-sm" style="border-radius: 6px; overflow: hidden;">
                                @if($cat->status == 'publish' && $cat->posts_count > 0)
                                    <a target="_blank" href="{{ url($cat->url) }}" class="btn btn-light btn-sm" title="Preview"><i class="fa fa-globe text-info"></i></a>
                                @endif
                                <button type="button" onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}', {{ $cat->sort }}, '{{ $cat->status }}', '{{ $cat->icon && media_exists($cat->icon) ? url($cat->icon) : '' }}')" class="btn btn-light btn-sm" title="Edit"><i class="fa fa-edit text-warning"></i></button>
                                @if(!$cat->posts()->exists())
                                    <button onclick="deleteAlert('{{ route(get_post_type() . '.category.destroy', $cat->id) }}')" class="btn btn-light btn-sm" title="Hapus"><i class="fa fa-trash text-danger"></i></button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
        
        @if($categories->count() == 0)
            <div class="col-12 text-center py-5">
                <div class="text-muted">
                    <i class="fa fa-folder-open fa-4x mb-3 text-light"></i>
                    <h5>Belum ada kategori</h5>
                    <p>Silakan tambah kategori baru dengan menekan tombol Tambah Kategori.</p>
                </div>
            </div>
        @endif
    </div>
</div>
</div>

<script type="text/javascript">
    const storeRoute = '{{ route(get_post_type() . ".category.store") }}';
    const updateRouteTemplate = '{{ route(get_post_type() . ".category.update", ":id") }}';
    const reorderRoute = '{{ route(get_post_type() . ".category.reorder") }}';
    const bulkDeleteRoute = '{{ route(get_post_type() . ".category.bulk-delete") }}';

    // ========== Bulk Delete Logic ==========
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const bulkChecks = document.querySelectorAll('.bulk-check');
            const selectAll = document.getElementById('selectAllEmpty');
            const bulkActions = document.getElementById('bulkActions');
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const selectedCount = document.getElementById('selectedCount');

            // Tampilkan toolbar jika ada kategori yang bisa dihapus
            if (bulkChecks.length > 0 && bulkActions) {
                bulkActions.style.display = 'block';
            }

            function updateBulkState() {
                const checked = document.querySelectorAll('.bulk-check:checked');
                const total = checked.length;
                selectedCount.textContent = total;
                btnBulkDelete.disabled = total === 0;

                // Sync select-all state
                if (selectAll) {
                    selectAll.checked = bulkChecks.length > 0 && total === bulkChecks.length;
                    selectAll.indeterminate = total > 0 && total < bulkChecks.length;
                }
            }

            bulkChecks.forEach(function(cb) {
                cb.addEventListener('change', updateBulkState);
            });

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    bulkChecks.forEach(function(cb) {
                        cb.checked = selectAll.checked;
                    });
                    updateBulkState();
                });
            }

            if (btnBulkDelete) {
                btnBulkDelete.addEventListener('click', function() {
                    const checked = document.querySelectorAll('.bulk-check:checked');
                    const ids = Array.from(checked).map(function(cb) { return cb.value; });

                    if (ids.length === 0) return;

                    swal({
                        title: 'Hapus ' + ids.length + ' Kategori?',
                        text: 'Kategori yang dipilih (dengan 0 data terkait) akan dihapus permanen.',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Semua!',
                        cancelButtonText: 'Batalkan',
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            $.ajax({
                                url: bulkDeleteRoute,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: ids
                                },
                                success: function(res) {
                                    swal('Berhasil!', res.message, 'success');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 800);
                                },
                                error: function(xhr) {
                                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                                    swal('Gagal!', msg, 'error');
                                }
                            });
                        }
                    });
                });
            }
        });
    })();

    function openCreateModal() {
        $('#categoryModalTitle').html('<i class="fa fa-plus-circle text-primary"></i> Tambah Kategori');
        $('#categoryForm').attr('action', storeRoute);
        $('#categoryMethod').val('POST');
        
        $('#cat_name').val('');
        $('#cat_description').val('');
        $('#cat_sort').val('0');
        $('#statusPublish').prop('checked', true);
        
        $('#iconPreviewContainer').hide();
        $('#cat_icon_preview').attr('src', '');
        
        $('#categoryModal').modal('show');
    }

    function openEditModal(id, name, description, sort, status, iconUrl) {
        $('#categoryModalTitle').html('<i class="fa fa-edit text-warning"></i> Edit Kategori');
        $('#categoryForm').attr('action', updateRouteTemplate.replace(':id', id));
        $('#categoryMethod').val('PUT');
        
        $('#cat_name').val(name);
        $('#cat_description').val(description);
        $('#cat_sort').val(sort || '0');
        
        if(status === 'publish') {
            $('#statusPublish').prop('checked', true);
        } else {
            $('#statusDraft').prop('checked', true);
        }
        
        if (iconUrl) {
            $('#cat_icon_preview').attr('src', iconUrl);
            $('#iconPreviewContainer').show();
        } else {
            $('#iconPreviewContainer').hide();
            $('#cat_icon_preview').attr('src', '');
        }
        
        $('#categoryModal').modal('show');
    }

    // Drag & Drop Sorting Handler
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('sortable-category-list');
        if (!container) return;

        let draggedItem = null;

        container.querySelectorAll('.category-sort-item').forEach(attachDragEvents);

        function attachDragEvents(item) {
            item.addEventListener('dragstart', function (e) {
                draggedItem = this;
                this.style.opacity = '0.4';
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragend', function () {
                this.style.opacity = '1';
                this.classList.remove('dragging');
                draggedItem = null;
                updateCategoryOrder();
            });

            item.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const afterElement = getDragAfterElement(container, e.clientY);
                if (afterElement == null) {
                    container.appendChild(draggedItem);
                } else {
                    container.insertBefore(draggedItem, afterElement);
                }
            });
        }

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.category-sort-item:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function updateCategoryOrder() {
            const items = container.querySelectorAll('.category-sort-item');
            const order = [];

            items.forEach((item, idx) => {
                order.push(item.getAttribute('data-id'));
                const sortValSpan = item.querySelector('.sort-val');
                if (sortValSpan) sortValSpan.innerText = (idx + 1);
            });

            $.ajax({
                url: reorderRoute,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order: order
                },
                success: function (res) {
                    if (typeof notif === 'function') {
                        notif('Urutan kategori berhasil diperbarui!', 'success');
                    }
                },
                error: function () {
                    if (typeof notif === 'function') {
                        notif('Gagal memperbarui urutan kategori.', 'danger');
                    }
                }
            });
        }
    });
</script>
@include('cms::backend.layout.js')
@endsection
