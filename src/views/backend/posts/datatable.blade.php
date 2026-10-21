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

    initToggle();

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

    let activeDateSelectorPostId = null;
    let activeDateSelectorField = null;
    let modalFlatpickrInstance = null;
    let activeSelectedDates = [];

    const indoDaysMap = {
        0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu'
    };
    const indoMonthsMap = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    function formatIndoDateJs(dateStr) {
        if (!dateStr) return '';
        try {
            var parts = dateStr.split('-');
            if (parts.length === 3) {
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10) - 1;
                var day = parseInt(parts[2], 10);
                var dt = new Date(year, month, day);
                var dayName = indoDaysMap[dt.getDay()] || '';
                var monthName = indoMonthsMap[month] || parts[1];
                return dayName + ', ' + day + ' ' + monthName + ' ' + year;
            }
        } catch (e) {}
        return dateStr;
    }

    function renderModalSelectedBadges() {
        var $container = $('#modalSelectedDatesContainer');
        $container.empty();
        activeSelectedDates.sort();

        $('#modalSelectedCount').text(activeSelectedDates.length);

        if (activeSelectedDates.length === 0) {
            $container.html('<div class="text-center text-muted py-4 small font-italic"><i class="fa fa-calendar-o d-block fa-2x mb-2 text-secondary opacity-50"></i>Belum ada tanggal dipilih.<br>Silakan klik tanggal pada kalender di sebelah kiri.</div>');
            return;
        }

        var html = '<div class="d-flex flex-column" style="gap: 6px;">';
        activeSelectedDates.forEach(function (dStr) {
            var label = formatIndoDateJs(dStr);
            html += '<div class="d-flex justify-content-between align-items-center bg-light border rounded px-2 py-1.5" style="font-size: 12px;">'
                + '<span class="font-weight-500 text-dark"><i class="fa fa-calendar-check-o text-success mr-1"></i>' + label + '</span>'
                + '<button type="button" class="btn btn-xs text-danger btn-remove-modal-date p-0 ml-2" data-date="' + dStr + '" title="Hapus tanggal ini" style="background: none; border: none; font-size: 16px; line-height: 1; cursor: pointer;">&times;</button>'
                + '</div>';
        });
        html += '</div>';
        $container.html(html);
    }

    $(document).on('click', '.btn-open-date-picker-modal, .dt-date-cell .dt-date-badge, .dt-date-cell .dt-date-more-badge', function (e) {
        e.preventDefault();
        var $btn = $(this).closest('.dt-date-cell').find('.btn-open-date-picker-modal');
        if (!$btn.length) {
            $btn = $(this);
        }

        activeDateSelectorPostId = $btn.data('id');
        activeDateSelectorField = $btn.data('field');
        var fieldLabel = $btn.data('field-label') || 'Jadwal';
        var postTitle = $btn.data('title') || '';
        var dates = $btn.data('dates') || [];
        var meta = $btn.data('meta') || {};

        if (typeof dates === 'string') {
            try { dates = JSON.parse(dates); } catch (err) { dates = dates ? [dates] : []; }
        }
        if (!Array.isArray(dates)) {
            dates = dates ? [dates] : [];
        }
        activeSelectedDates = dates.slice();

        $('#modalDateSelectorFieldLabel').text(fieldLabel);
        $('#modalDateSelectorPostTitle').text(postTitle ? ('(' + postTitle + ')') : '');

        var allowedDayNumbers = [];
        var allowedDaysRaw = meta.days || meta.days_of_week || null;
        if (typeof allowedDaysRaw === 'string') {
            allowedDaysRaw = allowedDaysRaw.split(',').map(function (s) { return s.trim(); });
        }
        if (Array.isArray(allowedDaysRaw) && allowedDaysRaw.length > 0) {
            var dayNameToNum = {
                'minggu': 0, 'ahad': 0, 'sunday': 0, '0': 0,
                'senin': 1, 'monday': 1, '1': 1,
                'selasa': 2, 'tuesday': 2, '2': 2,
                'rabu': 3, 'wednesday': 3, '3': 3,
                'kamis': 4, 'thursday': 4, '4': 4,
                'jumat': 5, "jum'at": 5, 'friday': 5, '5': 5,
                'sabtu': 6, 'saturday': 6, '6': 6
            };
            allowedDaysRaw.forEach(function (d) {
                var key = String(d).toLowerCase().trim();
                if (dayNameToNum[key] !== undefined) {
                    allowedDayNumbers.push(dayNameToNum[key]);
                }
            });
            $('#modalCalendarHelperText').text('Hari: ' + allowedDaysRaw.join(', ')).show();
        } else {
            $('#modalCalendarHelperText').text('').hide();
        }

        var fpConfig = {
            inline: true,
            mode: 'multiple',
            dateFormat: 'Y-m-d',
            defaultDate: activeSelectedDates,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                },
                months: {
                    shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                }
            },
            onChange: function (selectedDates, dateStr, instance) {
                activeSelectedDates = selectedDates.map(function (d) {
                    return instance.formatDate(d, 'Y-m-d');
                });
                renderModalSelectedBadges();
            }
        };

        if (allowedDayNumbers.length > 0) {
            fpConfig.disable = [
                function (date) {
                    return allowedDayNumbers.indexOf(date.getDay()) === -1;
                }
            ];
        }

        if (meta.dates && Array.isArray(meta.dates) && meta.dates.length > 0) {
            fpConfig.enable = meta.dates;
        } else {
            if (meta.start_date) {
                fpConfig.minDate = meta.start_date;
            } else {
                var daysBehind = parseInt(meta.days_behind || 0, 10);
                if (daysBehind > 0) {
                    fpConfig.minDate = new Date(Date.now() - (daysBehind * 86400000));
                }
            }

            if (meta.end_date) {
                fpConfig.maxDate = meta.end_date;
            } else {
                var daysAhead = parseInt(meta.days_ahead || 30, 10);
                if (daysAhead > 0) {
                    fpConfig.maxDate = new Date(Date.now() + (daysAhead * 86400000));
                }
            }
        }

        if (modalFlatpickrInstance) {
            modalFlatpickrInstance.destroy();
        }

        $('#inlineFlatpickrCalendar').empty();
        modalFlatpickrInstance = flatpickr('#inlineFlatpickrCalendar', fpConfig);

        renderModalSelectedBadges();
        $('#modalDateSelectorPicker').modal('show');
    });

    $(document).on('click', '.btn-remove-modal-date', function (e) {
        e.preventDefault();
        var dToRemove = $(this).data('date');
        var idx = activeSelectedDates.indexOf(dToRemove);
        if (idx !== -1) {
            activeSelectedDates.splice(idx, 1);
            if (modalFlatpickrInstance) {
                modalFlatpickrInstance.setDate(activeSelectedDates, true);
            }
            renderModalSelectedBadges();
        }
    });

    $(document).on('click', '#btnModalClearAllDates', function (e) {
        e.preventDefault();
        activeSelectedDates = [];
        if (modalFlatpickrInstance) {
            modalFlatpickrInstance.clear();
        }
        renderModalSelectedBadges();
    });

    $(document).on('click', '#btnSubmitDateSelectorModal', function (e) {
        e.preventDefault();
        if (!activeDateSelectorPostId || !activeDateSelectorField) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: "{{ route('post.date_selector_field') }}",
            type: "POST",
            data: {
                id: activeDateSelectorPostId,
                field: activeDateSelectorField,
                value: activeSelectedDates,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Update Tanggal');
                $('#modalDateSelectorPicker').modal('hide');

                if (typeof notif === 'function') {
                    notif(res.message || 'Tanggal berhasil diperbarui!', 'success');
                } else if (typeof $.notify === 'function') {
                    $.notify({
                        icon: 'fa fa-check',
                        title: '<b>Sukses:</b> ',
                        message: res.message || 'Tanggal berhasil diperbarui!'
                    }, {
                        type: 'success',
                        placement: { from: 'top', align: 'right' },
                        delay: 2000,
                        timer: 500
                    });
                }

                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Update Tanggal');
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Gagal menyimpan tanggal!";
                if (typeof notif === 'function') {
                    notif(msg, 'danger');
                } else {
                    alert(msg);
                }
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

<!-- Flatpickr Assets -->
<link rel="stylesheet" href="{{ url('backend/css/flatpickr.min.css') }}">
<script src="{{ url('backend/js/plugins/flatpickr.min.js') }}"></script>

<!-- Modal Date Selector Picker -->
<div class="modal fade" id="modalDateSelectorPicker" tabindex="-1" role="dialog" aria-labelledby="modalDateSelectorPickerLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light py-3 border-bottom d-flex align-items-center">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalDateSelectorPickerLabel">
                        <i class="fa fa-calendar text-primary mr-2"></i><span id="modalDateSelectorFieldLabel">Kelola Tanggal</span>
                    </h5>
                    <small class="text-muted" id="modalDateSelectorPostTitle">-</small>
                </div>
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row">
                    <div class="col-md-7 border-right mb-3 mb-md-0">
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <label class="font-weight-bold text-secondary mb-0 small">
                                <i class="fa fa-hand-pointer-o mr-1 text-primary"></i> Pilih tanggal pada kalender:
                            </label>
                            <span class="badge badge-light border text-muted small" id="modalCalendarHelperText"></span>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div id="inlineFlatpickrCalendar"></div>
                        </div>
                    </div>
                    <div class="col-md-5 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold text-secondary mb-0 small">
                                <i class="fa fa-list-ul mr-1 text-primary"></i> Tanggal Terpilih (<span id="modalSelectedCount" class="text-primary font-weight-bold">0</span>)
                            </label>
                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2" id="btnModalClearAllDates" title="Hapus Semua">
                                <i class="fa fa-trash-o mr-1"></i> Kosongkan
                            </button>
                        </div>
                        <div id="modalSelectedDatesContainer" class="p-2 border rounded bg-white flex-grow-1" style="min-height: 220px; max-height: 290px; overflow-y: auto;">
                            <!-- Selected date badges -->
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fa fa-info-circle text-info mr-1"></i> Klik tanggal pada kalender untuk menambah/menghapus, atau klik tombol silang (&times;) pada daftar.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 border-top">
                <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary px-4 font-weight-bold" id="btnSubmitDateSelectorModal">
                    <i class="fa fa-check mr-1"></i> Update Tanggal
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .dt-date-cell .dt-date-badge {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
        border: 1px solid #7dd3fc !important;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }
    .dt-date-cell .dt-date-badge:hover {
        background-color: #bae6fd !important;
    }
    .dt-date-cell .dt-date-more-badge {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        transition: all 0.15s ease-in-out;
    }
    .dt-date-cell .dt-date-more-badge:hover {
        background-color: #e2e8f0 !important;
    }
    #inlineFlatpickrCalendar .flatpickr-calendar {
        box-shadow: none !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        margin: 0 auto;
    }
    #inlineFlatpickrCalendar .flatpickr-day.selected,
    #inlineFlatpickrCalendar .flatpickr-day.startRange,
    #inlineFlatpickrCalendar .flatpickr-day.endRange {
        background: #009688 !important;
        border-color: #009688 !important;
        color: #fff !important;
    }
    #inlineFlatpickrCalendar .flatpickr-day.today {
        border-color: #009688 !important;
    }
</style>

