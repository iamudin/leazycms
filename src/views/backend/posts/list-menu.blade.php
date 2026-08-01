<input type="hidden" id="nestable3-output" class="form-control" name="menu_json">
<div class="row">
  <div class="col-lg-12">

    <div class="dd" id="nestable3" style="max-height:73vh;overflow:auto">
      <div style="margin-bottom: 10px;">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('.dd').nestable('collapseAll')"><i
            class="fa fa-compress"></i> Tutup Semua</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('.dd').nestable('expandAll')"><i
            class="fa fa-expand"></i> Buka Semua</button>
      </div>
      <ol class="dd-list main-list">
        @php $menu = collect(json_decode(json_encode($looping_data))) @endphp

        @foreach(json_decode(json_encode($menu->where('menu_parent', 0))) as $y => $l)


          <li class="dd-item dd3-item menu-id-{{$l->menu_id}}" data-id="{{$l->menu_id}}">
            <input type="hidden" name="menu_id[]" value="{{$l->menu_id ?? null}}">
            <input type="hidden" name="menu_parent[]" value="{{$l->menu_parent ?? null}}">
            <input type="hidden" class="name-{{$l->menu_id}}" name="menu_name[]" value="{{$l->menu_name ?? null}}">
            <input type="hidden" class="desc-{{$l->menu_id}}" name="menu_description[]"
              value="{{$l->menu_description ?? null}}">
            <input type="hidden" class="link-{{$l->menu_id}}" name="menu_link[]" value="{{$l->menu_link ?? null}}">
            <input type="hidden" class="icon-{{$l->menu_id}}" name="menu_icon[]" value="{{$l->menu_icon ?? null}}">
            <div style="cursor:move" class="dd-handle dd3-handle"></div>
            <div class="dd3-content">{{$l->menu_name}} <i class="fa fa-angle-right" aria-hidden></i>
              <code><a href="{{link_menu($l->menu_link)}}" title="Klik untuk mengunjungi"><i>{{Str::limit(link_menu($l->menu_link), '60', '...')}}</i></a></code>
              @php
                $edit_post_btn = '';
                $raw_link = $l->menu_link ?? '';
                if ($raw_link && !str_starts_with($raw_link, 'http') && $raw_link !== '#' && $raw_link !== '/') {
                    $link_clean = ltrim($raw_link, '/');
                    if (!empty($link_clean)) {
                        $parts = explode('/', $link_clean);
                        $is_module_index = false;
                        $is_category = false;
                        
                        $modules = collect(get_module())->pluck('name')->toArray();
                        if (count($parts) == 1 && in_array($parts[0], $modules)) {
                            $is_module_index = true;
                        }
                        if (count($parts) >= 2 && $parts[1] == 'category') {
                            $is_category = true;
                        }
                        
                        if (!$is_module_index && !$is_category) {
                            if (count($parts) == 1) {
                                $typepost = 'page';
                                $slug = $parts[0];
                            } else {
                                $typepost = $parts[0];
                                $slug = end($parts);
                            }
                            if (strlen($slug) >= 5 && in_array($typepost, $modules)) {
                                $edit_url = url(admin_path() . '/' . $typepost . '/create?slug=' . $slug);
                                $edit_post_btn = '<a href="' . $edit_url . '" target="_blank" class="text-primary" title="Edit Konten Target"> <i class="fa fa-external-link" aria-hidden></i> </a> &nbsp; ';
                            }
                        }
                    }
                }
              @endphp
              <span
                style="float:right">{!! $edit_post_btn !!}<a href="javascript:void(0)"
                  onclick="$('.description').val('{{$l->menu_description}}');$('.link').val('{{$l->menu_link}}');$('.name').val('{{$l->menu_name}}');$('.iconx').val('{{$l->menu_icon}}');$('#type').val('{{$l->menu_id}}');$('#menuFormModal').modal('show')"
                  class="text-warning"> <i class="fa fa-edit" aria-hidden></i> </a> &nbsp; <a href="javascript:void(0)"
                  onclick="del_menu('{{$l->menu_id}}')" class="text-danger"> <i class="fa fa-trash" aria-hidden></i>
                </a></span>
            </div>
            {!!ceksubmenu($menu, $l->menu_id)!!}


          </li>
        @endforeach
      </ol>
    </div>
    <button type="button" class="btn btn-sm btn-info pull-right btnadd"
      onclick="$('#type').val('add');$('.menu').val('');$('textarea').val('');$('#menuFormModal').modal('show');"
      name="button">
      <i class="fa fa-plus" aria-hidden></i> Baru</button>
  </div>

</div>

<div class="newmenu" style="display:none">
  @php $id = rnd(4); @endphp
  <li class="dd-item dd3-item" id="dataid" data-id="{{$id}}">
    <input type="hidden" id="id" name="menu_id[]" value="{{$id}}">
    <input type="hidden" id="parent" name="menu_parent[]" value="0">
    <input type="hidden" id="name" name="menu_name[]" value="New Menu">
    <input type="hidden" id="description" name="menu_description[]" value="New Description">
    <input type="hidden" id="link" name="menu_link[]" value="http://linkmenu.com">
    <input type="hidden" id="iconx" name="menu_icon[]" value="Icon">

    <div style="cursor:move" class="dd-handle dd3-handle"></div>
    <div class="dd3-content" id="labelname">New Menu</div>
  </li>
</div>
<div class="modal" id="menuFormModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span class="modtitle"></span>Form</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
      </div>
      <form class="" action="javascript:void(0)" method="post">
        <div class="modal-body">
          <div class="alert alert-info">
            Pastikan Klik Tombol Simpan untuk melakukan perubahan!
          </div>
          <div class="form-group">
            <input type="hidden" id="type" value="add">
            <label for="">Nama Menu</label>
            <input type="text" class="menu form-control name" name="names" placeholder="Masukkan Nama Menu" value="">
          </div>
          <div class="form-group">
            <label for="">Keterangan</label>
            <textarea type="text" class="menu form-control description" name="descriptions"
              placeholder="Masukkan Keterangan Menu" value=""></textarea>
          </div>
          <div class="form-group ">
            <label for="">Url Tujuan </label>
            <div class="input-group autocomplete-box">
              <input type="text" class="menu form-control link" name="links" id="link_target"
                placeholder="Masukkan Url Tujuan">
              <div id="autocomplete-list" class="autocomplete-results" style="top: 100%; left: 0; right: 0; width: 100%;"></div>
              <div class="input-group-append">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Pilih Link Template">
                    <i class="fa fa-link"></i> Pilih
                </button>
                <div class="dropdown-menu dropdown-menu-right p-2 shadow" style="width: 300px; max-height: 250px; overflow-y: auto;">
                    <h6 class="dropdown-header font-weight-bold text-primary"><i class="fa fa-cube"></i> Module (Index)</h6>
                    @php
                        $activeModules = collect(get_module());
                        if (config('modules.multisite_enabled')) {
                            $tenantModules = app()->bound('tenant') ? app('tenant')->modules ?? [] : [];
                            $activeModules = $activeModules->whereIn('name', array_merge(is_array($tenantModules) ? $tenantModules : [], function_exists('default_menu') ? default_menu() : []));
                        }
                    @endphp
                    @foreach($activeModules->filter(function($m) { return isset($m->web->index) && $m->web->index; }) as $mod)
                        <button type="button" class="dropdown-item link-pick-btn" data-link="/{{ $mod->name }}">
                            <i class="fa {{ $mod->icon ?? 'fa-circle-o' }}"></i> {{ $mod->title }} <small class="text-muted">(/{{ $mod->name }})</small>
                        </button>
                    @endforeach
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header font-weight-bold text-primary"><i class="fa fa-file-text"></i> Halaman (Page)</h6>
                    @foreach(query()->where('type', 'page')->published()->get(['title', 'url']) as $page)
                        <button type="button" class="dropdown-item link-pick-btn" data-link="/{{ ltrim($page->url, '/') }}">
                            <i class="fa fa-file-o"></i> {{ \Str::limit($page->title ?? 'Untitled', 20) }} <small class="text-muted">(/{{ ltrim($page->url, '/') }})</small>
                        </button>
                    @endforeach
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="">Icon</label>
            <div class="input-group">
              <input type="text" class="menu form-control iconx" id="menu-icon-input"
                accept="image/png,image/webp,image/gif,image/jpeg" name="icons" placeholder="fa fa-info atau Url Media"
                value="-" onkeyup="document.getElementById('menu-icon-preview').className = this.value || 'fa fa-flag'">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false" title="Pilih Icon"><i class="fa fa-flag" id="menu-icon-preview"></i></button>
                <div class="dropdown-menu dropdown-menu-right p-2 shadow"
                  style="width: 250px; max-height: 200px; overflow-y: auto;" id="fa-icon-picker">
                  <!-- Populated by JS -->
                </div>
                <button type="button" class="btn btn-outline-secondary btn-text-gmedia" data-target="#menu-icon-input"
                  title="Pilih Media"><i class="fa fa-folder-open"></i> Media</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary save" onclick="setmenu();submitMenuAjax(this)" type="button"
            name="save" value="">Simpan</button>

        </div>
      </form>

    </div>
  </div>
</div>

<script>
  function submitMenuAjax(btn) {
    let $btn = $(btn);
    let originalText = $btn.text();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
    $btn.prop('disabled', true);
    
    var form = $('.editorForm')[0];
    var formData = new FormData(form);
    
    $.ajax({
      url: $(form).attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        $btn.html('Simpan');
        $btn.prop('disabled', false);
        $('#menuFormModal').modal('hide');
     
        
        // Refresh the page so the newly added menu gets proper edit/delete buttons and correct hierarchy
      
      },
      error: function(xhr) {
        $btn.html('Simpan');
        $btn.prop('disabled', false);
        alert('Terjadi kesalahan saat menyimpan menu');
      }
    });
  }
</script>

<script>
  $(function () {
    if ($('#fa-icon-picker').length) {
      $('#fa-icon-picker').css({ 'width': '350px', 'max-height': '350px' });

      var initialHtml = `
        <div class="px-2 pb-2">
            <input type="text" id="fa-search-input" class="form-control form-control-sm" placeholder="Ketik nama icon..." onclick="event.stopPropagation()">
        </div>
        <div id="fa-icon-container" style="max-height: 250px; overflow-y: auto;">
            <div class="text-center w-100 py-3 text-muted" id="fa-loading">
                <i class="fa fa-spinner fa-spin fa-2x"></i><br><small>Memuat Icon...</small>
            </div>
        </div>
      `;
      $('#fa-icon-picker').html(initialHtml);

      fetch('https://raw.githubusercontent.com/FortAwesome/Font-Awesome/5.15.4/metadata/icons.json')
        .then(response => response.json())
        .then(data => {
            let iconsList = Object.keys(data).map(key => {
                let style = 'fa';
                if (data[key].styles.includes('brands')) style = 'fab';
                else if (data[key].styles.includes('solid')) style = 'fas';
                else if (data[key].styles.includes('regular')) style = 'far';
                
                return {
                    class: style + ' fa-' + key,
                    name: key
                };
            });

            let html = '<div class="d-flex flex-wrap justify-content-center">';
            iconsList.forEach(icon => {
                html += `
                <button type="button" class="btn btn-light btn-sm m-1 icon-pick-btn" data-icon="${icon.class}" title="${icon.name}" style="width:35px;height:35px;">
                    <i class="${icon.class}"></i>
                </button>`;
            });
            html += '</div>';
            $('#fa-icon-container').html(html);
        })
        .catch(err => {
            console.error('Gagal memuat API icon', err);
            $('#fa-loading').html('<div class="text-center text-danger py-3"><p style="font-size:12px;">Gagal memuat icon.</p></div>');
        });

      $(document).on('keyup', '#fa-search-input', function() {
          var filter = $(this).val().toLowerCase();
          var nodes = document.querySelectorAll('#fa-icon-container .icon-pick-btn');
          for (var i = 0; i < nodes.length; i++) {
              if (nodes[i].getAttribute('title').toLowerCase().includes(filter) || nodes[i].getAttribute('data-icon').toLowerCase().includes(filter)) {
                  nodes[i].style.display = "inline-block";
              } else {
                  nodes[i].style.display = "none";
              }
          }
      });

      $(document).off('click', '.icon-pick-btn').on('click', '.icon-pick-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var selectedIcon = $(this).data('icon');
        $('#menu-icon-input').val(selectedIcon);
        $('#menu-icon-preview').attr('class', selectedIcon);
        $(this).closest('.dropdown-menu').removeClass('show');
      });

      $(document).off('click', '.link-pick-btn').on('click', '.link-pick-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#link_target').val($(this).data('link'));
        $(this).closest('.dropdown-menu').removeClass('show');
      });
    }
  });
</script>