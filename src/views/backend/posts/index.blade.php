@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])
@section('content')


              <div class="row">
              <div class="col-lg-12 mb-3">
                <h3 style="font-weight:normal;float:left;" ><i class="fa {{get_module_info('icon')}}" aria-hidden="true"></i> {{get_post_type('title_crud')}} @if(request('trash')) <sup class="badge badge-warning" style="font-size:7px"> <i class="fa fa-trash-restore"></i> </sup> @endif
              </h3>

              <div class="pull-right">
                <div class="btn-group">
                @if(Route::has(get_post_type() . '.create'))
                <a href="{{route(get_post_type() . '.create')}}" class="btn btn-primary btn-sm"> <i class="fa fa-plus" aria-hidden></i> Tambah</a>
                @endif
                
                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#filter-modal"> <i class="fas fa-filter"></i> Filter</button>
              </div>
              </div>
              </div>

              <div class="col-lg-12">
             
                <div class="row"> 
                  @php
  $publish = request()->user()->isAdmin() ? query()->onType(get_post_type())->published()->count() : query()->whereBelongsTo(request()->user())->onType(get_post_type())->published()->count();
  $draft = request()->user()->isAdmin() ? query()->onType(get_post_type())->whereStatus('draft')->count() : query()->whereBelongsTo(request()->user())->onType(get_post_type())->whereStatus('draft')->count();
  $trash = request()->user()->isAdmin() ? query()->onType(get_post_type())->onlyTrashed()->count() : query()->whereBelongsTo(request()->user())->onType(get_post_type())->onlyTrashed()->count();


                @endphp
                      <div onclick="$('#status').val('publish').trigger('change');" title="Klik untuk selengkapnya" class="pointer col-6 col-md-6 col-lg-3">
                      <div class="widget-small primary coloured-icon"><i class="icon fa fa-globe fa-3x"></i>
                        <div class="info pl-3">
                        <p class="mt-2 text-muted">Dipublikasi</p>
                        <h2><b>{{ $publish }}</b></h2>
                        </div>
                      </div>
                      </div>

                      <div onclick="$('#status').val('draft').trigger('change');" title="Klik untuk selengkapnya" class="pointer col-6 col-md-6 col-lg-3">
                      <div class="widget-small warning coloured-icon"><i class="icon fa fa-save fa-3x"></i>
                        <div class="info pl-3">
                        <p class="mt-2 text-muted">Draft</p>
                        <h2><b>{{ $draft }}</b></h2>
                        </div>
                      </div>
                      </div>
                      @if(current_module()->form->category)
                      <a href="{{ route(get_post_type() . '.category') }}" class="col-6 col-md-6 col-lg-3">
                      <div class="widget-small info coloured-icon"><i class="icon fa fa-tags fa-3x"></i>
                        <div class="info pl-3">
                        <p class="mt-2 text-muted">Kategori</p>
                        <h2><b>{{ \Leazycms\Web\Models\Category::onType(get_post_type())->count() }}</b></h2>
                        </div>
                      </div>
                    </a>
                      @endif
                      <div onclick="$('#status').val('sampah').trigger('change');" title="Klik untuk selengkapnya" class="pointer col-6 col-md-6 col-lg-3">
                      <div class="widget-small danger coloured-icon"><i class="icon fa fa-trash-alt fa-3x"></i>
                        <div class="info pl-3">
                        <p class="mt-2 text-muted">Sampah</p>
                        <h2><b>{{ $trash }}</b></h2>
                        </div>
                      </div>
                      </div>
                    </div>
              </div>
              <div class="col-lg-12">
                @include('cms::backend.layout.error')

                <div class="mb-2 bulkaction" style="display: none">
                  <select id="bulkAction" class="form-control-sm form-control-select" >
                    <option value="">Pilih tindakan</option>
                    <option value="delete">Hapus</option>
                    <option value="draft">Draft</option>
                    <option value="publish">Publikasikan</option>
                  </select>
                  <button type="button" id="applyAction" class="btn btn-primary btn-sm" >Submit</button>
                </div>
              <table class="display table table-hover table-bordered datatable" style="background:#f7f7f7;width:100%;">
              <thead style="text-transform:uppercase;color:#444;font-size:small">
                <tr>
                <th style="width:10px;vertical-align: middle">
                  <div class="animated-checkbox">
                    <label>
                      <input type="checkbox" id="select-all">
                      <span class="label-text"></span>
                    </label>
                </div>
                </th>
                <th style="width:10px;vertical-align: middle">#</th>
                @if(current_module()->form->thumbnail)
                <th style="width:55px;vertical-align: middle" >Gambar</th>
                @endif
                <th style="vertical-align: middle">{{current_module()->datatable->data_title}}</th>

                @if($parent = current_module()->form->post_parent)
                <th style="vertical-align: middle" >{{$parent[0]}}</th>
                @endif
                @if($child_count=current_module()->datatable?->child_count??null)
                <th style="vertical-align: middle;width:30px" > {{ $child_count }}</th>
                @endif
                @if($custom = current_module()->datatable->custom_column)
                @if(is_array($custom))
                @foreach ($custom as $row)
                <th style="vertical-align: middle" >{{$row}}</th>
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
                <th  style="width:30px;vertical-align: middle">Hits</th>
                @endif
                <th style="width:30px;vertical-align: middle">Status</th>
                <th style="width:40px;vertical-align: middle">Aksi</th>
                </tr>
              </thead>

              <tbody style="background:#fff">

              </tbody>


              </table>

              </div>
              <br>
              <br>

              </div>
              @include('cms::backend.posts.filter')
              @include('cms::backend.posts.datatable')

              <!-- Modal Komentar -->
            <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Komentar  <span id="modalPostId"></span></h5>
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



              @push('styles')
                            {{datatable_asset('style')}}
                <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
                  rel="stylesheet">
              @endpush
              @push('scripts')
                <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

                  <script>
                function show_comment(post_id) {
                  // Reset tampilan

                  $("#commentList").empty();
                  $("#commentLoader").show();

                  // Tampilkan modal
                  $("#commentModal").modal("show");

                  // Panggil AJAX POST
                  $.ajax({
                    url: "{{ route('comments.get', null) }}/"+post_id,          // ganti dengan route/URL backend kamu
                    type: "GET",
                    success: function(res) {
                      $("#commentLoader").hide();
                        $("#modalPostId").text(res.title);
                      let comments = res.comments || []; // sesuaikan dengan struktur JSON

                      if (comments.length === 0) {
                        $("#commentList").append(`<li class="list-group-item text-muted">Belum ada komentar</li>`);
                      } else {
                     comments.forEach(c => {
                  $("#commentList").append(`
                    <li class="list-group-item">
                      <strong>
                        <i class="fa fa-user"></i> ${c.name}
                        <code>${
                          new Date(c.created_at).toLocaleDateString("id-ID", {
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
                    error: function(xhr) {
                      $("#commentLoader").hide();
                      $("#commentList").append(`<li class="list-group-item text-danger">Gagal memuat komentar</li>`);
                    }
                  });
                }
                </script>
                  <script>
                    $(function () {
                    $('#select-all').prop('checked',false);
                    $('#bulkAction').val('');
                    $('#select-all').click(function() {
                      var isChecked = this.checked;
                      $('.dt-checkbox').prop('checked', isChecked);
                      toggleActionButton();
                    });

                    $(document).on('change', '.dt-checkbox', function() {
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

                    $('#applyAction').click(function() {
                      var selectedIds = [];
                      $('.dt-checkbox:checked').each(function() {
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
                                action:action,
                              },
                              success: function(response) {
                          swal("Berhasil", "Tindakan Berhasil dilakukan", "success");

                               $('#select-all').prop('checked',false);
                               $('.bulkaction').prop('disabled', true).fadeOut();
                              $(".datatable").DataTable().ajax.reload();
                              },
                              error: function(xhr) {
                              $('#select-all').prop('checked',false);
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


                  </script>
                  {{datatable_asset('js')}}
                     @include('cms::backend.layout.js')
              @endpush

@endsection
