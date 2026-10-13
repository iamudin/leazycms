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
@php
    $moduleSummaryCols = get_module_summary_columns(current_module());
@endphp
@if(!empty($moduleSummaryCols))
    var summaryConfig = {!! json_encode($moduleSummaryCols) !!};

    function parseSummaryVal(val) {
        if (typeof val === 'number') return val;
        if (!val) return 0;
        if (typeof val === 'string') {
            var rawMatch = val.match(/data-raw=["']?(-?\d+(?:\.\d+)?)["']?/);
            if (rawMatch && rawMatch[1]) {
                return parseFloat(rawMatch[1]) || 0;
            }
            var clean = val.replace(/<[^>]*>/g, '').trim();
            var isNeg = clean.indexOf('-') !== -1;
            clean = clean.replace(/[^\d.,]/g, '');
            if (!clean) return 0;
            if (clean.indexOf('.') !== -1 && clean.indexOf(',') !== -1) {
                if (clean.lastIndexOf(',') > clean.lastIndexOf('.')) {
                    clean = clean.replace(/\./g, '').replace(',', '.');
                } else {
                    clean = clean.replace(/,/g, '');
                }
            } else if (clean.indexOf('.') !== -1) {
                clean = clean.replace(/\./g, '');
            } else if (clean.indexOf(',') !== -1) {
                clean = clean.replace(',', '.');
            }
            var num = parseFloat(clean);
            return isNaN(num) ? 0 : (isNeg ? -num : num);
        }
        return 0;
    }

    function formatSummaryNumber(num, prefix) {
        prefix = prefix || '';
        if (typeof num !== 'number') {
            num = parseFloat(num) || 0;
        }
        var isNeg = num < 0;
        var absVal = Math.abs(num);
        var parts = absVal.toFixed(2).split('.');
        var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        var formatted = (isNeg ? '-' : '') + prefix + intPart;
        if (parts[1] && parts[1] !== '00') {
            formatted += ',' + parts[1];
        }
        return formatted;
    }
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

                @if(config('modules.multisite_enabled') && is_main_domain())
                d.tenant_id = $("#tenant_id").val();
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
@if(!empty($moduleSummaryCols))
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            var ajaxJson = api.ajax.json();
            var allTotals = (ajaxJson && ajaxJson.summary) ? ajaxJson.summary : {};

            var pageTotals = {};
            Object.keys(summaryConfig).forEach(function (field) {
                pageTotals[field] = 0;
            });

            api.rows({ page: 'current' }).data().each(function (rowData) {
                Object.keys(summaryConfig).forEach(function (field) {
                    if (rowData && typeof rowData[field] !== 'undefined') {
                        pageTotals[field] += parseSummaryVal(rowData[field]);
                    }
                });
            });

            Object.keys(summaryConfig).forEach(function (field) {
                var prefix = summaryConfig[field].prefix || '';
                var pageSumFormatted = formatSummaryNumber(pageTotals[field], prefix);
                var allSumFormatted = formatSummaryNumber(allTotals[field] || 0, prefix);

                $('.sum-page-' + field).text(pageSumFormatted);
                $('.sum-all-' + field).text(allSumFormatted);
            });
        },
@endif
    });

@if(!empty($moduleSummaryCols))
    table.on('xhr.dt', function (e, settings, json, xhr) {
        if (json && json.summary) {
            Object.keys(summaryConfig).forEach(function (field) {
                var prefix = summaryConfig[field].prefix || '';
                var allSumFormatted = formatSummaryNumber(json.summary[field] || 0, prefix);
                $('.sum-all-' + field).text(allSumFormatted);
            });
        }
    });
@endif

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

    let updatingCustomToggle = false;
    $(document).on('change', '.toggle-custom-field', function () {
        if (updatingCustomToggle) return;

        let $el = $(this);
        let id = $el.data('id');
        let field = $el.data('field');
        let value = $el.prop('checked') ? 'true' : 'false';

        $.ajax({
            url: "{{ route('post.toggle_field') }}",
            type: "POST",
            data: {
                id: id,
                field: field,
                value: value,
                _token: "{{ csrf_token() }}"
            },
            success: function () {
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Gagal update data!";
                alert(msg);
                updatingCustomToggle = true;
                $el.bootstrapToggle('toggle');
                updatingCustomToggle = false;
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
