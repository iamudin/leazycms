@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])
@section('content')

  <div class="row">
    <!-- Modern Background Watermark -->
    <i class="fa {{get_module_info('icon')}}" style="position: fixed; right: -5%; top: 20%; font-size: 80vh; color: rgba(0, 0, 0, 0.1); z-index: 0; pointer-events: none; transform: rotate(-15deg);"></i>

    <div class="col-lg-12 mb-3" style="position: relative; z-index: 1;">
      <h3 style="font-weight:normal;float:left;"> <i class="fa {{get_module_info('icon')}}" aria-hidden="true"></i>
        {{get_post_type('title_crud')}}
      </h3>

      <div class="pull-right">
        <div class="btn-group">



          @if($module->web->index)
            <a href="/{{ $module->name }}" target="_blank" class="btn btn-info btn-sm"> <i class="fa fa-globe" aria-hidden
                title="Lihat di Halaman Web Utama"></i> Lihat di Web</a>
          @endif
          @if(!empty($module->guide))
            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#guideModal"> <i
                class="fa fa-question" aria-hidden title="Lihat Panduan"></i> Panduan Modul</button>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-12">

      <div class="row">
        @php
          $user = request()->user();
          $postType = get_post_type();
          $canSeeAll = $user->isAdmin() || !$user->hasRole($postType, 'admin', true);

          $counts = query()->onType($postType)->withTenant()
            ->when(!$canSeeAll, fn($q) => $q->whereBelongsTo($user))
            ->withTrashed()
            ->selectRaw("
                            SUM(deleted_at IS NULL AND status = 'publish') as publish,
                            SUM(deleted_at IS NULL AND status = 'draft') as draft,
                            SUM(deleted_at IS NOT NULL) as trash
                        ")->first();

          $publish = $counts->publish ?? 0;
          $draft = $counts->draft ?? 0;
          $trash = $counts->trash ?? 0;
        @endphp
        <div onclick="$('#status').val('publish').trigger('change');" title="Klik untuk selengkapnya"
          class="pointer col-6 col-md-4 col-lg-3 mb-3">
          <div
            style="position: relative; overflow: hidden; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; transition: all 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
            <h4 id="count-publish" style="margin: 0; font-weight: bold; font-size: 26px; color: #ffffff; position: relative; z-index: 2;">
              {{ $publish }}</h4>
            <p style="margin: 5px 0 0; font-size: 14px; color: rgba(255,255,255,0.85); position: relative; z-index: 2;">
              Dipublikasi</p>
            <i class="fa fa-globe"
              style="position: absolute; right: -5px; bottom: -10px; font-size: 70px; color: rgba(255,255,255,0.2); z-index: 0; transform: rotate(-15deg);"></i>
          </div>
        </div>

        <div onclick="$('#status').val('draft').trigger('change');" title="Klik untuk selengkapnya"
          class="pointer col-6 col-md-4 col-lg-3 mb-3">
          <div
            style="position: relative; overflow: hidden; background: linear-gradient(135deg, #fd7e14 0%, #e85d04 100%); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; transition: all 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
            <h4 id="count-draft" style="margin: 0; font-weight: bold; font-size: 26px; color: #ffffff; position: relative; z-index: 2;">
              {{ $draft }}</h4>
            <p style="margin: 5px 0 0; font-size: 14px; color: rgba(255,255,255,0.85); position: relative; z-index: 2;">
              Draft</p>
            <i class="fa fa-save"
              style="position: absolute; right: -5px; bottom: -10px; font-size: 70px; color: rgba(255,255,255,0.2); z-index: 0; transform: rotate(-15deg);"></i>
          </div>
        </div>
        @if(current_module()->form->category)
          @if(auth()->user()->isAdmin() || !auth()->user()->hasRole('category' . current_module()->name, 'index', true))
            <a href="{{ route(get_post_type() . '.category') }}" class="col-6 col-md-4 col-lg-3 mb-3"
              style="text-decoration: none;">
              <div
                style="position: relative; overflow: hidden; background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; transition: all 0.2s;"
                onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';"
                onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
                <h4 id="count-category" style="margin: 0; font-weight: bold; font-size: 26px; color: #ffffff; position: relative; z-index: 2;">
                  {{ \Leazycms\Web\Models\Category::onType(get_post_type())->count() }}</h4>
                <p style="margin: 5px 0 0; font-size: 14px; color: rgba(255,255,255,0.85); position: relative; z-index: 2;">
                  Kategori</p>
                <i class="fa fa-tags"
                  style="position: absolute; right: -5px; bottom: -10px; font-size: 70px; color: rgba(255,255,255,0.2); z-index: 0; transform: rotate(-15deg);"></i>
              </div>
            </a>
          @endif
        @endif
        <div onclick="$('#status').val('sampah').trigger('change');" title="Klik untuk selengkapnya"
          class="pointer col-6 col-md-4 col-lg-3 mb-3">
          <div
            style="position: relative; overflow: hidden; background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; transition: all 0.2s;"
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';"
            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
            <h4 id="count-trash" style="margin: 0; font-weight: bold; font-size: 26px; color: #ffffff; position: relative; z-index: 2;">
              {{ $trash }}</h4>
            <p style="margin: 5px 0 0; font-size: 14px; color: rgba(255,255,255,0.85); position: relative; z-index: 2;">
              Sampah</p>
            <i class="fa fa-trash-alt"
              style="position: absolute; right: -5px; bottom: -10px; font-size: 70px; color: rgba(255,255,255,0.2); z-index: 0; transform: rotate(-15deg);"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-12">
      @include('cms::backend.layout.error')
      <div>
        <div class="mb-2 bulkaction float-left" style="display: none">
          <select id="bulkAction" class="form-control-sm form-control-select">
            <option value="">Pilih tindakan</option>
            <option value="delete">Hapus</option>
            <option value="draft">Draft</option>
            <option value="publish">Publikasikan</option>
          </select>
          <button type="button" id="applyAction" class="btn btn-primary btn-sm">Submit</button>
        </div>
        <div class="mb-4 text-right ">
          <div class="btn-group">
            @if(need_sync_dummy())
              <button type="button" class="btn btn-success btn-sm btn-sync-dummy">
                <i class="fas fa-sync" aria-hidden="true"></i> Sinkron Data Dummy

              </button>
            @endif
            @if(Route::has(get_post_type() . '.create'))
              @if(auth()->user()->isAdmin() || !auth()->user()->hasRole(get_post_type(), 'create', true))
                <a href="{{route(get_post_type() . '.create')}}" class="btn btn-primary btn-sm"> <i class=" fa fa-plus"
                    aria-hidden></i> Tambah {{ $module->title }} Baru</a>
              @endif
            @endif
            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#filter-modal"> <i
                class="fas fa-filter"></i> Filter</button>
          </div>
        </div>
      </div>
      <style>
        .dataTables_wrapper {
            background: rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.05);
            position: relative;
            z-index: 1;
        }
        table.dataTable {
            background: transparent !important;
        }
        table.dataTable tbody tr {
            background-color: rgba(255, 255, 255, 0.2) !important;
        }
        table.dataTable tbody tr.odd {
            background-color: rgba(255, 255, 255, 0.4) !important;
        }
        table.dataTable tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.6) !important;
        }
        table.dataTable tbody td, table.dataTable thead th {
            background-color: transparent !important;
            border-color: rgba(0, 0, 0, 0.05) !important;
        }
        table.dataTable thead {
            background: rgba(255, 255, 255, 0.5) !important;
        }
      </style>
      <table class="display table table-hover table-bordered datatable table-striped"
        style="width:100%;">
        <thead style="text-transform:uppercase;color:#444;font-size:small; background: rgba(255, 255, 255, 0.4);">
          <tr>
            <th style="width:10px;vertical-align: middle">
              <div class="animated-checkbox">
                <label>
                  <input type="checkbox" id="select-all">
                  <span class="label-text"></span>
                </label>
              </div>
            </th>
            @if(isset(current_module()->datatable?->index_column) && current_module()->datatable?->index_column == true)
              <th style="width:10px;vertical-align: middle">#</th>
            @endif
            @if(current_module()->form->thumbnail)
              <th style="width:55px;vertical-align: middle">Gambar</th>
            @endif
            <th style="vertical-align: middle">{{current_module()->datatable->data_title}}</th>
            @if($parent = current_module()->form->post_parent)
              <th style="vertical-align: middle">{{$parent[0] }}</th>
            @endif
            @if($child_count = collect(current_module()->datatable->child_count ?? []))
              @foreach($child_count as $type)
                <th style="vertical-align: middle;width:80px">
                  {{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $type)) }}
                </th>
              @endforeach

            @endif
            @if($custom = current_module()->datatable->custom_column)
              @if(is_array($custom))
                @foreach ($custom as $row)
                  <th style="vertical-align: middle">{{$row}}</th>
                @endforeach
              @else
                <th style="vertical-align: middle">{{$custom}}</th>
              @endif
            @endif
            @if((isset(current_module()->datatable?->timestamps) && current_module()->datatable?->timestamps) || !isset(current_module()->datatable?->timestamps))

              <th style="width:60px;vertical-align: middle">Dibuat</th>

              <th style="width:60px;vertical-align: middle">Diubah</th>
            @endif
            @if(current_module()->web->detail)
              <th style="width:30px;vertical-align: middle">Hits</th>
            @endif
            <th style="width:30px;vertical-align: middle">Status</th>
            <th style="width:40px;vertical-align: middle">Aksi</th>
          </tr>
        </thead>

        <tbody style="background: transparent;">

        </tbody>


      </table>

    </div>
    <br>
    <br>

  </div>
  @include('cms::backend.posts.filter')
  @include('cms::backend.posts.datatable')

  <!-- Modal Komentar -->
  <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Komentar <span id="modalPostId"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Loader -->
          <div id="commentLoader" class="text-center my-3" style="display:none;">
            <div class="spinner-border text-info" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>

          <!-- Daftar komentar -->
          <ul id="commentList" class="list-group"></ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>


  @if(!empty($module->guide))
    <!-- Modal Panduan -->
    <div class="modal fade" id="guideModal" tabindex="-1" role="dialog" aria-labelledby="guideModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          @php
            $videoUrl = $module->guide;
            $videoId = '';
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $videoUrl, $matches)) {
              $videoId = $matches[1];
            } else {
              $videoId = $videoUrl; // In case it's just the ID
            }
          @endphp
          <div class="modal-header align-items-center">
            <h5 class="modal-title" id="guideModalLabel">Panduan Modul {{ $module->title }}</h5>
            <div class="ml-auto d-flex align-items-center">
              @if($videoId)
                <a href="https://www.youtube.com/watch?v={{ $videoId }}" target="_blank" class="btn btn-danger btn-sm mr-3">
                  <i class="fa fa-video"></i> Buka di YouTube
                </a>
              @endif
              <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Tutup">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </div>
          <div class="modal-body p-0">
            @if($videoId)
              <div class="embed-responsive embed-responsive-16by9"
                style="position: relative; display: block; width: 100%; padding: 0; overflow: hidden; ">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ $videoId }}" allowfullscreen
                  style="position: absolute; top: 0; bottom: 0; left: 0; width: 100%; height: 100%;border: 0;"></iframe>
              </div>
            @else
              <div class="p-4 text-center">Video panduan tidak valid.</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif

  @push('styles')
    {{datatable_asset('style')}}
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
      rel="stylesheet">
  @endpush
  @push('scripts')
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script>
      $(document).on('click', '.btn-view-media', function () {

        let file = $(this).data('media');
        let ext = $(this).data('ext');

        let url = file;

        let content = '';

        let imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        let pdfExt = ['pdf'];

        if (imageExt.includes(ext)) {

          content = `<img src="${url}" class="img-fluid">`;

        } else if (pdfExt.includes(ext)) {

          content = `<iframe src="${url}" width="100%" height="600px"></iframe>`;

        } else {

          content = `
                                                                                                                                                                                                                                                                                                                          <div class="text-center">
                                                                                                                                                                                                                                                                                                                              <p>File tidak dapat dilihat.</p>
                                                                                                                                                                                                                                                                                                                              <a href="${url}" class="btn btn-primary" download>
                                                                                                                                                                                                                                                                                                                                  Download File
                                                                                                                                                                                                                                                                                                                              </a>
                                                                                                                                                                                                                                                                                                                          </div>
                                                                                                                                                                                                                                                                                                                      `;
        }

        $('#mediaContent').html(content);
        $('#mediaModal').modal('show');
      });
    </script>
    <script>
      function show_comment(post_id) {

        $("#commentList").empty();
        $("#commentLoader").show();

        $("#commentModal").modal("show");

        $.ajax({
          url: "{{ route('comments.get', null) }}/" + post_id,
          type: "GET",
          success: function (res) {
            $("#commentLoader").hide();
            $("#modalPostId").text(res.title);
            let comments = res.comments || [];

            if (comments.length === 0) {
              $("#commentList").append(`<li class="list-group-item text-muted">Belum ada komentar</li>`);
            } else {
              comments.forEach(c => {
                $("#commentList").append(`
                                                                                                                                                                                                                                                                                                                                    <li class="list-group-item">
                                                                                                                                                                                                                                                                                                                                      <strong>
                                                                                                                                                                                                                                                                                                                                        <i class="fa fa-user"></i> ${c.name}
                                                                                                                                                                                                                                                                                                                                        <code>${new Date(c.created_at).toLocaleDateString("id-ID", {
                  day: "numeric",
                  month: "long",
                  year: "numeric"
                }) + " " +
                  new Date(c.created_at).toLocaleTimeString("id-ID", {
                    hour: "2-digit",
                    minute: "2-digit"
                  })
                  }</code>
                                                                                                                                                                                                                                                                                                                                      </strong>
                                                                                                                                                                                                                                                                                                                                      <br>
                                                                                                                                                                                                                                                                                                                                      <small>${c.content}</small>
                                                                                                                                                                                                                                                                                                                                    </li>
                                                                                                                                                                                                                                                                                                                                  `);
              });

            }
          },
          error: function (xhr) {
            $("#commentLoader").hide();
            $("#commentList").append(`<li class="list-group-item text-danger">Gagal memuat komentar</li>`);
          }
        });
      }
    </script>
    <script>
      $(function () {
        $('#select-all').prop('checked', false);
        $('#bulkAction').val('');
        $('#select-all').click(function () {
          var isChecked = this.checked;
          $('.dt-checkbox').prop('checked', isChecked);
          toggleActionButton();
        });

        $(document).on('change', '.dt-checkbox', function () {
          toggleActionButton();
        });

        function toggleActionButton() {
          var selectedCount = $('.dt-checkbox:checked').length;

          if (selectedCount > 0) {
            $('.bulkaction').prop('disabled', false).fadeIn();
          } else {
            $('.bulkaction').prop('disabled', true).fadeOut();
          }
        }

        $('#applyAction').click(function () {
          var selectedIds = [];
          $('.dt-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
          });

          var action = $('#bulkAction').val();
          if (selectedIds.length > 0 && action) {
            swal(
              {
                title: "Anda yakin dengan tindakan ini ?",
                text: "Semua data terkait akan berimbas atas tindakan ini",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Iya, Lakukan!",
                cancelButtonText: "Tidak, Batalkan!",
                closeOnConfirm: false,
                closeOnCancel: false,
              },
              function (isConfirm) {
                if (isConfirm) {
                  $.ajax({
                    url: "{{ route(get_post_type() . '.bulkaction') }}",
                    type: 'POST',
                    data: {
                      _token: $('meta[name="csrf-token"]').attr('content'),
                      id: selectedIds,
                      action: action,
                    },
                    success: function (response) {
                      swal("Berhasil", "Tindakan Berhasil dilakukan", "success");

                      $('#select-all').prop('checked', false);
                      $('.bulkaction').prop('disabled', true).fadeOut();
                      $(".datatable").DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                      $('#select-all').prop('checked', false);
                      swal("Gagal", "Tindakan gagal dilakukan", "error");

                    }
                  });

                  swal.close();
                } else {
                  swal("Dibatalkan", "Tindakan dibatalkan", "error");
                }
              }
            );


          }
        });
      });


      $(document).ready(function () {
        $('.datatable').on('xhr.dt', function (e, settings, json, xhr) {
          if (json && json.counts) {
            $('#count-publish').text(json.counts.publish);
            $('#count-draft').text(json.counts.draft);
            $('#count-trash').text(json.counts.trash);
            if ($('#count-category').length) {
              $('#count-category').text(json.counts.category);
            }
          }
        });

        $('#guideModal').on('hidden.bs.modal', function () {
          var $iframe = $(this).find('iframe');
          if ($iframe.length) {
            var src = $iframe.attr('src');
            $iframe.attr('src', '');
            $iframe.attr('src', src);
          }
        });
      });
    </script>
    <script>
      $(document).on('click', '.btn-sync-dummy', function () {
        swal({
          title: "Sinkronisasi Data Dummy?",
          text: "Data dummy untuk modul ini akan ditambahkan.",
          type: "info",
          showCancelButton: true,
          confirmButtonText: "Ya, Sinkronkan!",
          cancelButtonText: "Batal",
          closeOnConfirm: false,
          showLoaderOnConfirm: true
        }, function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              url: "{{ route(get_post_type() . '.sync_dummy') }}",
              type: 'POST',
              data: {
                _token: $('meta[name="csrf-token"]').attr('content')
              },
              success: function (response) {
                swal("Berhasil!", response.message, "success");
                setTimeout(() => {
                  location.reload();
                }, 1500);
              },
              error: function (xhr) {
                swal("Gagal!", "Gagal melakukan sinkronisasi data dummy.", "error");
              }
            });
          }
        });
      });
    </script>
    {{datatable_asset('js')}}
    @include('cms::backend.layout.js')
  @endpush

@endsection