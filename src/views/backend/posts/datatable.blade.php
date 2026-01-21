<script>
    window.addEventListener('DOMContentLoaded', function() {
     var sort_col = $('.datatable').find("th:contains('Dibuat')")[0].cellIndex;
        var table = $('.datatable').DataTable({

            responsive: true,

            processing: true,
            serverSide: true,
            aaSorting: [],

            ajax: {
                method: "POST",
                url: "{{ route(get_post_type() . '.datatable')}}",
                data: function (d){
                 d._token = "{{csrf_token()}}";
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
            deferRender: true,
            columns: [
                {
                    className: 'text-center',
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    className: 'text-center',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },

                @if (current_module()->form->thumbnail)
                    {
                        data: 'thumbnail',
                        searchable: false,
                        name: 'thumbnail',
                        orderable: false
                    },
                @endif
                {
                    data: 'title',
                    searchable: true,
                    name: 'title',
                    orderable: false
                },
                @if (current_module()->form->post_parent)
                    {
                        data: 'parents',
                        name: 'parents',
                        orderable: false,
                        searchable: true
                    },
                @endif
                
                @if ($custom = current_module()->datatable->custom_column)
                @if(is_array($custom))
                @foreach($custom as $row)
                    {
                        data: '{{ _us($row) }}',
                        name: '{{ _us($row) }}',
                        orderable: false,
                        searchable: false
                    },
                @endforeach
                @else
                   {
                        data: '{{ $custom }}',
                        name: '{{ $custom }}'',
                        orderable: false,
                        searchable: false
                    },
                @endif
                @endif {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true,
                    searchable: false
                },

                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        orderable: true,
                        searchable: false
                    },
                @if (current_module()->web->detail)
                    {
                        data: 'visited',
                        name: 'visited',
                        orderable: true,
                        searchable: false
                    },
                @endif
                 {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            responsive: true,
            order: [
                [sort_col, 'desc']
            ],
        });
        table.on('draw', function () {

            // re-init toggle
            $('input[data-toggle="toggle"]').bootstrapToggle();

            // event ketika toggle di-klik
            $('.toggle-status').change(function () {

                let id = $(this).data('id');
                let status = $(this).prop('checked') ? 'publish' : 'draft';

                $.ajax({
                    url: "{{ route('post.status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status: status,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        console.log("Status updated");
                    },
                    error: function (err) {
                        console.log(err);
                        alert("Gagal update status!");
                    }
                });

            });
        });


    });
</script>
