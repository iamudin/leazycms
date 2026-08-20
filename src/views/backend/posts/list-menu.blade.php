<style>
  .dd-item,
  .dd-empty,
  .dd-placeholder {
    margin: 6px 0 !important;
  }
  .dd3-item > .dd-handle,
  .dd3-handle {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 36px !important;
    height: 40px !important;
    box-sizing: border-box !important;
    border: 1px solid #aaa !important;
    border-top-left-radius: 3px !important;
    border-bottom-left-radius: 3px !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    z-index: 3 !important;
  }
  .dd3-handle:before {
    content: '≡' !important;
    display: block !important;
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 38px !important;
    line-height: 36px !important;
    text-align: center !important;
    text-indent: 0 !important;
    color: #fff !important;
    font-size: 20px !important;
    font-weight: normal !important;
  }
  .dd3-content {
    display: block !important;
    height: 40px !important;
    line-height: 28px !important;
    margin: 0 !important;
    padding: 5px 115px 5px 46px !important;
    box-sizing: border-box !important;
    border: 1px solid #ccc !important;
    border-radius: 3px !important;
    background: #fafafa !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    position: relative !important;
  }
  .menu-label-title {
    font-weight: 600;
    color: #333;
    display: inline-block;
    max-width: 50%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
  }
  .menu-sep-icon {
    margin: 0 4px;
    color: #888;
    vertical-align: middle;
  }
  .menu-link-url {
    display: inline-block;
    max-width: 45%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
  }
  .dd-item > button {
    display: block;
    position: absolute !important;
    left: 42px !important;
    top: 10px !important;
    width: 20px !important;
    height: 20px !important;
    line-height: 18px !important;
    margin: 0 !important;
    padding: 0 !important;
    text-indent: 100% !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    border: 1px solid #cbd5e1 !important;
    background: #f8fafc !important;
    border-radius: 4px !important;
    color: #475569 !important;
    font-size: 0 !important;
    text-align: center !important;
    cursor: pointer !important;
    z-index: 6 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    float: none !important;
    box-sizing: border-box !important;
  }
  .dd-item > button[style*="display: none"],
  .dd-item > button[style*="display:none"] {
    display: none !important;
  }
  .dd-item > button:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
  }
  .dd-item > button:before {
    content: '+' !important;
    display: block !important;
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 100% !important;
    line-height: 18px !important;
    text-align: center !important;
    text-indent: 0 !important;
    font-size: 13px !important;
    font-weight: bold !important;
    color: #475569 !important;
    font-family: inherit !important;
  }
  .dd-item > button[data-action="collapse"]:before {
    content: '−' !important;
    font-size: 15px !important;
    line-height: 16px !important;
  }
  .dd3-item > button ~ .dd3-content {
    padding-left: 70px !important;
  }
  .menu-action-buttons {
    position: absolute !important;
    right: 8px !important;
    top: 5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    height: 28px !important;
    line-height: 28px !important;
    z-index: 5 !important;
  }
  .menu-action-buttons a {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 22px !important;
    height: 22px !important;
    text-decoration: none !important;
    font-size: 14px !important;
    line-height: 1 !important;
  }
  #nestable3 {
    max-height: 74vh;
    overflow-y: auto;
    overflow-x: auto;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
    padding-right: 4px;
  }
  #nestable3::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  #nestable3::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 6px;
  }
  #nestable3::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 6px;
    transition: background-color 0.2s ease;
  }
  #nestable3::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  /* Responsive Mobile Breakpoints */
  @media (max-width: 768px) {
    .dd-list .dd-list {
      padding-left: 18px !important;
    }
    .dd3-item > .dd-handle,
    .dd3-handle {
      width: 30px !important;
    }
    .dd-item > button {
      left: 35px !important;
      top: 11px !important;
      width: 18px !important;
      height: 18px !important;
    }
    .dd-item > button:before {
      line-height: 16px !important;
      font-size: 11px !important;
    }
    .dd3-content {
      padding-left: 36px !important;
      padding-right: 98px !important;
      font-size: 12.5px !important;
    }
    .dd3-item > button ~ .dd3-content {
      padding-left: 58px !important;
    }
    .menu-action-buttons {
      gap: 4px !important;
      right: 5px !important;
    }
    .menu-action-buttons a {
      width: 20px !important;
      height: 20px !important;
      font-size: 12.5px !important;
    }
    .menu-label-title {
      max-width: 90% !important;
    }
    .menu-sep-icon,
    .menu-link-url {
      display: none !important;
    }
  }

  @media (max-width: 480px) {
    .dd-list .dd-list {
      padding-left: 14px !important;
    }
    .dd3-content {
      padding-left: 34px !important;
      padding-right: 90px !important;
    }
    .dd3-item > button ~ .dd3-content {
      padding-left: 54px !important;
    }
    .menu-action-buttons {
      gap: 3px !important;
      right: 4px !important;
    }
  }
</style>
<input type="hidden" id="nestable3-output" class="form-control" name="menu_json">
<div class="row">
  <div class="col-lg-12">

    <div class="dd" id="nestable3">
      <div style="margin-bottom: 10px;" class="d-flex justify-content-between align-items-center">
        <div>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('.dd').nestable('collapseAll')"><i
              class="fa fa-compress"></i> Tutup Semua</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('.dd').nestable('expandAll')"><i
              class="fa fa-expand"></i> Buka Semua</button>
        </div>
        <div id="menu-save-indicator" style="display:none;">
          <span class="badge badge-warning py-1 px-2"><i class="fa fa-spinner fa-spin"></i> Menyimpan urutan...</span>
        </div>
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
            <div class="dd3-content"><span class="menu-label-title">{{$l->menu_name}}</span> <i class="fa fa-angle-right menu-sep-icon" aria-hidden></i>
              <code class="menu-link-url"><a href="{{link_menu($l->menu_link)}}" title="Klik untuk mengunjungi"><i>{{Str::limit(link_menu($l->menu_link), '60', '...')}}</i></a></code>
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
                                $edit_post_btn = '<a href="' . $edit_url . '" target="_blank" class="text-primary btn-edit-post" title="Edit Konten Target"> <i class="fa fa-external-link" aria-hidden></i> </a>';
                            }
                        }
                    }
                }
              @endphp
              <span class="menu-action-buttons">
                {!! $edit_post_btn !!}
                <a href="javascript:void(0)" onclick="open_add_menu('{{$l->menu_id}}', '{{addslashes($l->menu_name)}}')" class="text-success btn-add-sub" title="Tambah Sub Menu"> <i class="fa fa-plus-circle" aria-hidden></i> </a>
                <a href="javascript:void(0)" onclick="open_edit_menu('{{$l->menu_id}}')" class="text-warning btn-edit-menu" title="Edit Menu"> <i class="fa fa-edit" aria-hidden></i> </a>
                <a href="javascript:void(0)" onclick="del_menu('{{$l->menu_id}}')" class="text-danger btn-delete-menu" title="Hapus Menu"> <i class="fa fa-trash-alt" aria-hidden></i> </a>
              </span>
            </div>
            {!!ceksubmenu($menu, $l->menu_id, 2)!!}

          </li>
        @endforeach
      </ol>
    </div>
    <button type="button" class="btn btn-sm btn-info pull-right btnadd mt-3" onclick="open_add_menu()" name="button">
      <i class="fa fa-plus" aria-hidden></i> Tambah Menu Utama
    </button>
  </div>

</div>

<div class="modal" id="menuFormModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span class="modtitle">Tambah </span>Menu</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
      </div>
      <form class="" action="javascript:void(0)" method="post">
        <div class="modal-body">
          <div class="alert alert-info py-2" style="font-size: 13px;">
            Pastikan Klik Tombol Simpan untuk melakukan perubahan!
          </div>
          <div id="parent_info_badge" class="alert alert-success py-2 px-3 mb-3" style="display:none; font-size: 13px;">
            <i class="fa fa-level-down"></i> Menambahkan Sub Menu di dalam: <strong id="parent_info_name"></strong>
          </div>
          <div class="form-group">
            <input type="hidden" id="type" value="add">
            <input type="hidden" id="menu_parent_target" value="">
            <label for="">Nama Menu</label>
            <input type="text" class="menu form-control name" name="names" placeholder="Masukkan Nama Menu" value="" required>
          </div>
          <div class="form-group">
            <label for="">Keterangan</label>
            <textarea type="text" class="menu form-control description" name="descriptions"
              placeholder="Masukkan Keterangan Menu"></textarea>
          </div>
          <div class="form-group">
            <label for="">Url Tujuan</label>
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
                            $disallowedModules = app()->bound('tenant') ? app('tenant')->modules ?? [] : [];
                            if (is_string($disallowedModules)) {
                                $disallowedModules = json_decode($disallowedModules, true) ?? [];
                            }
                            if (is_array($disallowedModules) && count($disallowedModules) > 0) {
                                $activeModules = $activeModules->whereNotIn('name', $disallowedModules);
                            }
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
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button class="btn btn-primary save" onclick="saveMenuModal(this)" type="button"
            name="save" value="">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  var autoSaveTimer = null;
  var isNestableInitialized = false;
  var isSavingModal = false;
  var currentAjaxRequest = null;
  var lastSerializedMenu = '';

  function refreshAddSubButtons() {
    $('#nestable3 li.dd-item').each(function () {
      var $li = $(this);
      var level = $li.parents('ol.dd-list').length;
      var menuId = $li.attr('data-id');
      var menuName = $li.find('> input[name="menu_name[]"]').val() || '';
      var escapedName = menuName.replace(/'/g, "\\'");

      var $actions = $li.children('.dd3-content').find('.menu-action-buttons');
      if ($actions.length === 0) {
        $actions = $li.children('.dd3-content').find('span[style*="float:right"], span[style*="float: right"]');
      }

      // Remove existing add-sub button completely without leaving text nodes
      $actions.find('a.btn-add-sub').remove();

      if (level <= 4) {
        var addBtnHtml = '<a href="javascript:void(0)" onclick="open_add_menu(\'' + menuId + '\', \'' + escapedName + '\')" class="text-success btn-add-sub" title="Tambah Sub Menu"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>';
        var $editBtn = $actions.find('a.btn-edit-menu, a.text-warning');
        if ($editBtn.length) {
          $editBtn.first().before(addBtnHtml);
        } else {
          $actions.append(addBtnHtml);
        }
      }
    });
  }

  function autoSaveMenuOrder() {
    if (!isNestableInitialized || isSavingModal) return;

    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function () {
      if (isSavingModal) return;

      var $indicator = $('#menu-save-indicator');
      $indicator.html('<span class="badge badge-warning py-1 px-2"><i class="fa fa-spinner fa-spin"></i> Menyimpan urutan...</span>').fadeIn(150);

      var form = $('.editorForm')[0];
      if (!form) return;
      var formData = new FormData(form);

      if (currentAjaxRequest) {
        currentAjaxRequest.abort();
        currentAjaxRequest = null;
      }

      currentAjaxRequest = $.ajax({
        url: $(form).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          'Accept': 'application/json'
        },
        success: function (response) {
          currentAjaxRequest = null;
          $indicator.html('<span class="badge badge-success py-1 px-2"><i class="fa fa-check"></i> Urutan tersimpan</span>');
          setTimeout(function () {
            $indicator.fadeOut(400);
          }, 2000);
      
        },
        error: function (xhr, status) {
          if (status === 'abort') return;
          currentAjaxRequest = null;
          $indicator.html('<span class="badge badge-danger py-1 px-2"><i class="fa fa-exclamation-triangle"></i> Gagal menyimpan</span>');
          setTimeout(function () {
            $indicator.fadeOut(400);
          }, 3000);
          if (typeof notif === 'function') {
            notif('Gagal menyimpan urutan menu', 'danger');
          }
        }
      });
    }, 400);
  }

  $(document).ready(function () {
    var updateOutput = function (e) {
      var list = e.length ? e : $(e.target),
        output = list.data('output');
      var currentJson = '';
      if (window.JSON) {
        currentJson = window.JSON.stringify(list.nestable('serialize'));
        output.val(currentJson);
      } else {
        output.val('JSON browser support required for this demo.');
      }

      if (!isNestableInitialized) {
        lastSerializedMenu = currentJson;
        refreshAddSubButtons();
        return;
      }

      // Only proceed if hierarchy / position has actually CHANGED
      if (currentJson && currentJson !== lastSerializedMenu) {
        lastSerializedMenu = currentJson;
        refreshAddSubButtons();
        autoSaveMenuOrder();
      }
    };

    $('#nestable3').nestable({
      group: 1
    }).on('change', updateOutput);

    updateOutput($('#nestable3').data('output', $('#nestable3-output')));

    setTimeout(function () {
      isNestableInitialized = true;
      if (window.JSON) {
        lastSerializedMenu = window.JSON.stringify($('#nestable3').nestable('serialize'));
      }
    }, 600);
  });

  function open_add_menu(parentId, parentName) {
    if (parentId) {
      var $parentLi = $('.menu-id-' + parentId);
      var currentLevel = $parentLi.parents('ol.dd-list').length;
      if (currentLevel >= 5) {
        alert('Maksimal sub menu hanya dapat ditambahkan sampai level 4!');
        return;
      }
    }

    $('#type').val('add');
    $('#menu_parent_target').val(parentId || '');
    
    $('.name').val('');
    $('.description').val('');
    $('.link').val('');
    $('.iconx').val('-');
    $('#menu-icon-preview').attr('class', 'fa fa-flag');
    
    if (parentId) {
      $('.modtitle').text('Tambah Sub ');
      $('#parent_info_name').text(parentName || ('ID #' + parentId));
      $('#parent_info_badge').show();
    } else {
      $('.modtitle').text('Tambah ');
      $('#parent_info_badge').hide();
    }
    
    $('#menuFormModal').modal('show');
  }

  function open_edit_menu(id) {
    $('#type').val(id);
    $('#menu_parent_target').val('');
    $('.modtitle').text('Edit ');
    $('#parent_info_badge').hide();
    
    var name = $('.name-' + id).val() || '';
    var desc = $('.desc-' + id).val() || '';
    var link = $('.link-' + id).val() || '';
    var icon = $('.icon-' + id).val() || '-';
    
    $('.name').val(name);
    $('.description').val(desc);
    $('.link').val(link);
    $('.iconx').val(icon);
    
    var previewClass = (icon && icon !== '-') ? icon : 'fa fa-flag';
    $('#menu-icon-preview').attr('class', previewClass);
    
    $('#menuFormModal').modal('show');
  }

  function del_menu(id) {
    if (confirm('Yakin ingin menghapus menu ini beserta sub menunya jika ada?')) {
      var $li = $('.menu-id-' + id);
      var $parentLi = $li.closest('ol.dd-list').closest('li.dd-item');

      $li.remove();

      if ($parentLi.length > 0) {
        var $subList = $parentLi.children('ol.dd-list');
        if ($subList.children('li.dd-item').length === 0) {
          $subList.remove();
          $parentLi.children('[data-action="collapse"], [data-action="expand"]').remove();
          $parentLi.removeClass('dd-collapsed');
        }
      }

      $('#nestable3').nestable({
        group: 1
      }).change();
    }
  }

  function setmenu() {
    var name = $('.name').val().trim();
    var desc = $('.description').val() || '';
    var link = $('.link').val() || '';
    var icon = $('.iconx').val() || '-';

    var type = $('#type').val();
    var parentId = $('#menu_parent_target').val();

    function formatLinkMenu(menuUrl) {
      if (!menuUrl) return '';
      if (menuUrl.indexOf('http') !== -1 || menuUrl.indexOf('javascript:') === 0 || menuUrl === '#') {
        return menuUrl;
      }
      var cleanLink = menuUrl.charAt(0) === '/' ? menuUrl.substring(1) : menuUrl;
      return "{{ url('') }}/" + cleanLink;
    }
    var formattedLink = formatLinkMenu(link);
    var displayLink = formattedLink.length > 60 ? formattedLink.substring(0, 60) + '...' : formattedLink;

    if (type !== 'add') {
      // Edit existing
      $('.name-' + type).val(name);
      $('.desc-' + type).val(desc);
      $('.link-' + type).val(link);
      $('.icon-' + type).val(icon);

      var $item = $('.menu-id-' + type);
      var $content = $item.children('.dd3-content');
      if ($content.length) {
        var $title = $content.find('.menu-label-title');
        if ($title.length) {
          $title.text(name);
        } else if ($content[0].childNodes[0].nodeType === 3) {
          $content[0].childNodes[0].nodeValue = name + " ";
        }

        var $linkCode = $content.find('code a');
        if ($linkCode.length) {
          $linkCode.attr('href', formattedLink).find('i').text(displayLink);
        }
      }
      refreshAddSubButtons();
    } else {
      // Add new menu or sub menu with guaranteed unique ID
      var newId = Date.now().toString().slice(-6) + Math.floor(Math.random() * 900 + 100);
      var newParentVal = parentId ? parentId : '0';

      var buttonsHtml = '<span class="menu-action-buttons">' +
        '<a href="javascript:void(0)" onclick="open_edit_menu(\'' + newId + '\')" class="text-warning btn-edit-menu" title="Edit Menu"> <i class="fa fa-edit" aria-hidden="true"></i> </a>' +
        '<a href="javascript:void(0)" onclick="del_menu(\'' + newId + '\')" class="text-danger btn-delete-menu" title="Hapus Menu"> <i class="fa fa-trash-alt" aria-hidden="true"></i> </a>' +
        '</span>';

      var contentHtml = '<span class="menu-label-title">' + name + '</span> <i class="fa fa-angle-right menu-sep-icon" aria-hidden="true"></i> <code class="menu-link-url"><a href="' + formattedLink + '" title="Klik untuk mengunjungi"><i>' + displayLink + '</i></a></code>' + buttonsHtml;

      var newItemHtml = '<li class="dd-item dd3-item menu-id-' + newId + '" data-id="' + newId + '">' +
        '<input type="hidden" name="menu_id[]" value="' + newId + '">' +
        '<input type="hidden" name="menu_parent[]" value="' + newParentVal + '">' +
        '<input type="hidden" class="name-' + newId + '" name="menu_name[]" value="' + $('<div/>').text(name).html() + '">' +
        '<input type="hidden" class="desc-' + newId + '" name="menu_description[]" value="' + $('<div/>').text(desc).html() + '">' +
        '<input type="hidden" class="link-' + newId + '" name="menu_link[]" value="' + $('<div/>').text(link).html() + '">' +
        '<input type="hidden" class="icon-' + newId + '" name="menu_icon[]" value="' + $('<div/>').text(icon).html() + '">' +
        '<div style="cursor:move" class="dd-handle dd3-handle"></div>' +
        '<div class="dd3-content">' + contentHtml + '</div>' +
        '</li>';

      var $newItem = $(newItemHtml);

      if (parentId && $('.menu-id-' + parentId).length > 0) {
        var $parentLi = $('.menu-id-' + parentId);
        var $subList = $parentLi.children('ol.dd-list');
        if ($subList.length === 0) {
          $subList = $('<ol class="dd-list"></ol>');
          $parentLi.append($subList);
        }
        $subList.append($newItem);

        // Ensure parent has collapse/expand button
        if ($parentLi.children('[data-action="collapse"]').length === 0) {
          $parentLi.prepend('<button data-action="collapse" type="button">Collapse</button><button data-action="expand" type="button" style="display: none;">Expand</button>');
        }

        if ($parentLi.hasClass('dd-collapsed')) {
          $parentLi.removeClass('dd-collapsed');
        }
        $parentLi.children('[data-action="expand"]').hide();
        $parentLi.children('[data-action="collapse"]').show();
      } else {
        $('.main-list').append($newItem);
      }

      $('#nestable3').nestable({
        group: 1
      }).change();

      refreshAddSubButtons();
    }
  }

  function saveMenuModal(btn) {
    var name = $('.name').val().trim();
    if (!name) {
      alert('Nama menu tidak boleh kosong!');
      $('.name').focus();
      return;
    }

    isSavingModal = true;
    clearTimeout(autoSaveTimer);

    setmenu();
    submitMenuAjax(btn);
  }

  function submitMenuAjax(btn) {
    let $btn = $(btn);
    let originalText = $btn.text();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
    $btn.prop('disabled', true);
    
    var form = $('.editorForm')[0];
    var formData = new FormData(form);

    if (currentAjaxRequest) {
      currentAjaxRequest.abort();
      currentAjaxRequest = null;
    }

    currentAjaxRequest = $.ajax({
      url: $(form).attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
      },
      success: function(response) {
        $btn.html('Simpan');
        $btn.prop('disabled', false);
        $('#menuFormModal').modal('hide');
        isSavingModal = false;
        currentAjaxRequest = null;
        if (window.JSON) {
          lastSerializedMenu = window.JSON.stringify($('#nestable3').nestable('serialize'));
        }
        if (typeof notif === 'function') {
          notif('Berhasil menyimpan menu!', 'success');
        }
      },
      error: function(xhr, status) {
        if (status === 'abort') return;
        $btn.html('Simpan');
        $btn.prop('disabled', false);
        isSavingModal = false;
        currentAjaxRequest = null;
        let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
        if (errors) {
          let firstError = Object.values(errors)[0][0];
          if (typeof notif === 'function') notif(firstError, 'error');
          else alert(firstError);
        } else {
          if (typeof notif === 'function') notif('Terjadi kesalahan saat menyimpan menu', 'error');
          else alert('Terjadi kesalahan saat menyimpan menu');
        }
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