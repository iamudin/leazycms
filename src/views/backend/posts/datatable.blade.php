<script>
window.addEventListener('DOMContentLoaded', function () {
@if((isset(current_module()->datatable?->timestamps) && current_module()->datatable?->timestamps) || !isset(current_module()->datatable?->timestamps))
    @php
    $childColumns = collect(current_module()->datatable?->child_count ?? [])
        ->map(fn($type) => \Illuminate\Support\Str::snake($type) . '_count');
    @endphp
    var sort_col = $('.datatable')
        .find("th:contains('Dibuat')")[0]
        ?.cellIndex ?? 0;
@endif
    var table = $('.datatable').DataTable({

        responsive: {
            details: {
                type: 'inline'
            }
        },
   language: {
            search: "",
            searchPlaceholder: "Cari Data..."
    },
        processing: true,
        serverSide: true,
        deferRender: true,
        aaSorting: [],

        ajax: {
            method: "POST",
            url: "{{ route(get_post_type() . '.datatable') }}",
            data: function (d) {
                d._token = "{{ csrf_token() }}";

                @if(current_module()->form->post_parent)
                d.parent_id = $("#parent_id").val();
                @endif

                @if(current_module()->form->category)
                d.category_id = $("#category_id").val();
                @endif

                @if(current_module()->form->tag)
                d.tag_id = $("#tag_id").val();
                @endif

                d.status = $("#status").val();
                d.user_id = $("#user_id").val();
                d.from_date = $("#from_date").val();
                d.to_date = $("#to_date").val();
                d.search = $("input[type=search]").val();
            }
        },

        lengthMenu: [10, 20, 50, 100, 200, 500],

        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            @if(current_module()->web->sortable ?? false)
            { data: 'drag_handle', orderable: false, searchable: false, className: 'text-center drag-handle-cell' },
            @endif
            @if(isset(current_module()->datatable?->index_column) && current_module()->datatable?->index_column == true)
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            @endif

            @if (current_module()->form->thumbnail)
            { data: 'thumbnail', orderable: false, searchable: false },
            @endif

            { data: 'title', orderable: false, searchable: true },

            @if (current_module()->form->post_parent)
            { data: 'parents', orderable: false, searchable: true },
            @endif
            @if($child_count = current_module()->datatable?->child_count ?? null)
                  @foreach($childColumns as $col)
                    { data: '{{ $col }}', name: '{{ $col }}', orderable: false, searchable: false },
                @endforeach
            @endif
            @if ($custom = current_module()->datatable->custom_column)
                @if(is_array($custom))
                    @foreach($custom as $row)
                        { data: '{{ _us($row) }}', orderable: false, searchable: false },
                    @endforeach
                @else
                    { data: '{{ _us($custom) }}', orderable: false, searchable: false },
                @endif
            @endif
@if((isset(current_module()->datatable?->timestamps) && current_module()->datatable?->timestamps) || !isset(current_module()->datatable?->timestamps))

            { data: 'created_at', orderable: true, searchable: false },
            { data: 'updated_at', orderable: true, searchable: false },
@endif
            @if (current_module()->web->detail)
            { data: 'visited', orderable: true, searchable: false },
            @endif

            { data: 'status', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
        ],
@if(current_module()->web->sortable ?? false)
        aaSorting: [],
        order: [],
@elseif((isset(current_module()->datatable?->timestamps) && current_module()->datatable?->timestamps) || !isset(current_module()->datatable?->timestamps))
        order: [[ sort_col, 'desc' ]],
@endif
        createdRow: function (row, data, dataIndex) {
            let id = data.id || $(row).find('.dt-checkbox').val();
            if (id) {
                $(row).attr('data-id', id);
                @if(current_module()->web->sortable ?? false)
                $(row).attr('draggable', 'true');
                $(row).css('cursor', 'move');
                @endif
            }
        },
    });

    function initToggle() {
        $('input[data-toggle="toggle"]').each(function () {
            if (!$(this).parent().hasClass('toggle')) {
                $(this).bootstrapToggle();
            }
        });
    }

    table.on('draw.dt', function () {
        initToggle();
    });

    table.on('responsive-display', function (e, datatable, row, showHide) {
        if (showHide) {
            setTimeout(() => {
                initToggle();
            }, 100);
        }
    });

    $(document).on('change', '.toggle-status', function () {

        let $el = $(this);
        let id = $el.data('id');
        let status = $el.prop('checked') ? 'publish' : 'draft';

        $.ajax({
            url: "{{ route('post.status') }}",
            type: "POST",
            data: {
                id: id,
                status: status,
                _token: "{{ csrf_token() }}"
            },
            success: function () {
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                }
            },
            error: function () {
                alert("Gagal update status!");
                $el.bootstrapToggle('toggle');
            }
        });

    });

@if(current_module()->web->sortable ?? false)
    const postReorderRoute = '{{ route(get_post_type() . ".reorder") }}';

    function initPostDragAndDrop() {
        const tbody = document.querySelector('.datatable tbody');
        if (!tbody) return;

        let draggedRow = null;

        tbody.querySelectorAll('tr[draggable="true"]').forEach(row => {
            row.addEventListener('dragstart', function (e) {
                draggedRow = this;
                this.style.opacity = '0.4';
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            row.addEventListener('dragend', function () {
                this.style.opacity = '1';
                this.classList.remove('dragging');
                draggedRow = null;
                savePostOrder();
            });

            row.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const afterElement = getRowDragAfterElement(tbody, e.clientY);
                if (afterElement == null) {
                    tbody.appendChild(draggedRow);
                } else {
                    tbody.insertBefore(draggedRow, afterElement);
                }
            });
        });
    }

    function getRowDragAfterElement(tbody, y) {
        const draggableElements = [...tbody.querySelectorAll('tr[draggable="true"]:not(.dragging)')];
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

    function savePostOrder() {
        const rows = document.querySelectorAll('.datatable tbody tr');
        const order = [];

        rows.forEach(row => {
            let id = row.getAttribute('data-id');
            if (!id) {
                const cb = row.querySelector('.dt-checkbox');
                if (cb) id = cb.value;
            }
            if (id) {
                row.setAttribute('data-id', id);
                order.push(id);
            }
        });

        let startOffset = 0;
        if (typeof table !== 'undefined' && table.page) {
            startOffset = table.page.info().start;
        }

        // Instantly update visual row numbers in DOM
        rows.forEach((row, idx) => {
            const cells = row.querySelectorAll('td');
            cells.forEach(td => {
                if (!td.querySelector('input') && !td.classList.contains('drag-handle-cell') && td.innerText && /^\d+$/.test(td.innerText.trim())) {
                    td.innerText = (startOffset + idx + 1);
                }
            });
        });

        if (order.length > 0) {
            $.ajax({
                url: postReorderRoute,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order: order,
                    start_offset: startOffset
                },
                success: function (res) {
                    if (typeof notif === 'function') {
                        notif('Urutan data berhasil diperbarui!', 'success');
                    }
                    if (typeof table !== 'undefined') {
                        table.ajax.reload(null, false);
                    }
                },
                error: function () {
                    if (typeof notif === 'function') {
                        notif('Gagal memperbarui urutan data.', 'danger');
                    }
                }
            });
        }
    }

    table.on('draw.dt', function () {
        initPostDragAndDrop();
    });
@endif

});
</script>
