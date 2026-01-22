<script>
window.addEventListener('DOMContentLoaded', function () {

    // ambil index kolom "Dibuat"
    var sort_col = $('.datatable')
        .find("th:contains('Dibuat')")[0]
        ?.cellIndex ?? 0;

    var table = $('.datatable').DataTable({

        responsive: {
            details: {
                type: 'inline'
            }
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
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },

            @if (current_module()->form->thumbnail)
            { data: 'thumbnail', orderable: false, searchable: false },
            @endif

            { data: 'title', orderable: false, searchable: true },

            @if (current_module()->form->post_parent)
            { data: 'parents', orderable: false, searchable: true },
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

            { data: 'created_at', orderable: true, searchable: false },
            { data: 'updated_at', orderable: true, searchable: false },

            @if (current_module()->web->detail)
            { data: 'visited', orderable: true, searchable: false },
            @endif

            { data: 'status', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
        ],

        order: [[ sort_col, 'desc' ]],
    });

    // ================================
    // RE-INIT TOGGLE SETIAP DRAW
    // ================================
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

    // ================================
    // FIX RESPONSIVE CHILD ROW
    // ================================
    table.on('responsive-display', function (e, datatable, row, showHide) {
        if (showHide) {
            setTimeout(() => {
                initToggle();
            }, 100);
        }
    });

    // ================================
    // EVENT DELEGATION TOGGLE STATUS
    // ================================
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
            error: function () {
                alert("Gagal update status!");
                $el.bootstrapToggle('toggle'); // rollback
            }
        });

    });

});
</script>
