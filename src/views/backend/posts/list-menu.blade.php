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
            <div class="autocomplete-box">
              <input type="text" class="menu form-control link" name="links" id="link_target"
                placeholder="Masukkan Url Tujuan">
              <div id="autocomplete-list" class="autocomplete-results"></div>
            </div>
          </div>
          <div class="form-group">
            <label for="">Icon</label>
            <div class="input-group">
              <input type="text" class="menu form-control iconx" id="menu-icon-input"
                accept="image/png,image/webp,image/gif,image/jpeg" name="icons" placeholder="fa fa-info atau Url Media"
                value="-">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false" title="Pilih Icon"><i class="fa fa-flag"></i></button>
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
          <button class="btn btn-primary save" onclick="setmenu();$('#menuFormModal').modal('hide')" type="button"
            name="save" value="">Simpan</button>

        </div>
      </form>

    </div>
  </div>
</div>

<script>
  $(function () {
    if ($('#fa-icon-picker').length) {
      // Increase width to fit more icons nicely
      $('#fa-icon-picker').css({ 'width': '400px', 'max-height': '300px' });

      var icons = [
        'fa-info', 'fa-info-circle', 'fa-question', 'fa-question-circle', 'fa-exclamation', 'fa-exclamation-circle', 'fa-exclamation-triangle',
        'fa-home', 'fa-user', 'fa-users', 'fa-user-circle', 'fa-user-plus', 'fa-id-badge', 'fa-id-card', 'fa-address-book', 'fa-address-card',
        'fa-cog', 'fa-cogs', 'fa-wrench', 'fa-envelope', 'fa-envelope-open', 'fa-phone', 'fa-phone-square', 'fa-mobile', 'fa-tablet', 'fa-desktop', 'fa-laptop',
        'fa-building', 'fa-industry', 'fa-hospital-o', 'fa-university', 'fa-briefcase', 'fa-suitcase', 'fa-plane', 'fa-car', 'fa-bus', 'fa-truck',
        'fa-file', 'fa-file-text', 'fa-file-pdf-o', 'fa-file-word-o', 'fa-file-excel-o', 'fa-file-powerpoint-o', 'fa-file-archive-o', 'fa-file-image-o', 'fa-file-video-o', 'fa-file-audio-o',
        'fa-download', 'fa-upload', 'fa-cloud-download', 'fa-cloud-upload', 'fa-save', 'fa-folder', 'fa-folder-open',
        'fa-camera', 'fa-image', 'fa-picture-o', 'fa-video-camera', 'fa-music', 'fa-headphones', 'fa-microphone',
        'fa-bell', 'fa-bell-o', 'fa-calendar', 'fa-calendar-check-o', 'fa-clock-o', 'fa-check', 'fa-check-circle', 'fa-check-square', 'fa-times', 'fa-times-circle', 'fa-plus', 'fa-plus-circle', 'fa-minus', 'fa-minus-circle',
        'fa-search', 'fa-search-plus', 'fa-search-minus', 'fa-globe', 'fa-link', 'fa-external-link', 'fa-paper-plane', 'fa-send',
        'fa-list', 'fa-list-ul', 'fa-list-ol', 'fa-th', 'fa-th-large', 'fa-th-list', 'fa-table',
        'fa-chart-bar', 'fa-bar-chart', 'fa-pie-chart', 'fa-line-chart', 'fa-area-chart',
        'fa-map-marker', 'fa-map', 'fa-location-arrow', 'fa-compass',
        'fa-star', 'fa-star-o', 'fa-star-half-o', 'fa-heart', 'fa-heart-o', 'fa-thumbs-up', 'fa-thumbs-down',
        'fa-shopping-cart', 'fa-shopping-bag', 'fa-shopping-basket', 'fa-credit-card', 'fa-money', 'fa-tags', 'fa-ticket',
        'fa-bullhorn', 'fa-gavel', 'fa-balance-scale', 'fa-graduation-cap', 'fa-book', 'fa-newspaper-o',
        'fa-angle-right', 'fa-angle-left', 'fa-angle-up', 'fa-angle-down', 'fa-arrow-right', 'fa-arrow-left', 'fa-arrow-up', 'fa-arrow-down',
        'fa-chevron-right', 'fa-chevron-left', 'fa-chevron-up', 'fa-chevron-down', 'fa-caret-right', 'fa-caret-down',
        'fa-share', 'fa-share-alt', 'fa-reply', 'fa-refresh', 'fa-sync', 'fa-spinner', 'fa-circle-o-notch',
        'fa-lock', 'fa-unlock', 'fa-key', 'fa-shield', 'fa-eye', 'fa-eye-slash',
        'fa-facebook', 'fa-twitter', 'fa-instagram', 'fa-youtube', 'fa-whatsapp', 'fa-telegram', 'fa-github', 'fa-linkedin'
      ];
      var html = '<div class="d-flex flex-wrap justify-content-center">';
      icons.forEach(function (icon) {
        html += '<button type="button" class="btn btn-light btn-sm m-1 icon-pick-btn" data-icon="fa ' + icon + '" style="width:35px;height:35px;" title="' + icon + '"><i class="fa ' + icon + '"></i></button>';
      });
      html += '</div>';
      $('#fa-icon-picker').html(html);

      $(document).off('click', '.icon-pick-btn').on('click', '.icon-pick-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#menu-icon-input').val($(this).data('icon'));
        $(this).closest('.dropdown-menu').removeClass('show');
      });
    }
  });
</script>