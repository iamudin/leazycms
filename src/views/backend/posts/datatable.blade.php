<script type="text/javascript">
    window.addEventListener('DOMContentLoaded', function() {
     var sort_col = $('.datatable').find("th:contains('Dibuat')")[0].cellIndex;
        var table = $('.datatable').DataTable({
            responsive: true,

            processing: true,
            serverSide: true,
            aaSorting: [],

            ajax: {
                method: "POST",
                url: "{{ route(get_post_type() . '.datatable') }}",
                data: {_token:"{{csrf_token()}}"}
            },
            lengthMenu: [10, 20, 50, 100, 200, 500],
            deferRender: true,
            columns: [
 
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
                        name: 'post_thumbnail',
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
                @if (current_module()->datatable->custom_column)
                    {
                        data: 'data_field',
                        name: 'data_field',
                        orderable: false,
                        searchable: true
                    },
                @endif {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true,
                    searchable: false
                },

                @if (get_post_type() != 'media')
                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        orderable: true,
                        searchable: false
                    },
                @endif
                @if (current_module()->web->detail)
                    {
                        data: 'visitors_count',
                        name: 'visitors_count',
                        orderable: true,
                        searchable: false
                    },
                @endif 
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


    });
</script>
