
    <input type="hidden" id="nestable3-output" class="form-control" name="menu_json">
    <div class="row">
      <div class="col-lg-12">

    <div class="dd" id="nestable3" style="max-height:55vh;overflow:auto">
          <ol class="dd-list main-list">
            @php $menu = collect(json_decode(json_encode($looping_data))) @endphp

               @foreach(json_decode(json_encode($menu->where('menu_parent', 0))) as $y => $l)


              <li class="dd-item dd3-item menu-id-{{$l->menu_id}}" data-id="{{$l->menu_id}}">
                <input  type="hidden" name="menu_id[]" value="{{$l->menu_id ?? null}}">
                <input  type="hidden" name="menu_parent[]" value="{{$l->menu_parent ?? null}}">
                <input  type="hidden" class="name-{{$l->menu_id}}"  name="menu_name[]" value="{{$l->menu_name ?? null}}">
                <input  type="hidden" class="desc-{{$l->menu_id}}" name="menu_description[]" value="{{$l->menu_description ?? null}}">
                <input  type="hidden" class="link-{{$l->menu_id}}" name="menu_link[]" value="{{$l->menu_link ?? null}}">
                <input  type="hidden" class="icon-{{$l->menu_id}}" name="menu_icon[]" value="{{$l->menu_icon ?? null}}">
                  <div style="cursor:move" class="dd-handle dd3-handle"></div><div class="dd3-content">{{$l->menu_name}} <i class="fa fa-angle-right" aria-hidden></i> <code><a href="{{link_menu($l->menu_link)}}" title="Klik untuk mengunjungi"><i>{{Str::limit(link_menu($l->menu_link),'60','...')}}</i></a></code><span style="float:right"><a href="javascript:void(0)" onclick="$('.description').val('{{$l->menu_description}}');$('.link').val('{{$l->menu_link}}');$('.name').val('{{$l->menu_name}}');$('.iconx').val('{{$l->menu_icon}}');$('#type').val('{{$l->menu_id}}');$('.modal').modal('show')" class="text-warning" > <i class="fa fa-edit" aria-hidden></i> </a> &nbsp; <a href="javascript:void(0)" onclick="del_menu('{{$l->menu_id}}')" class="text-danger" > <i class="fa fa-trash" aria-hidden></i> </a></span></div>
              {!!ceksubmenu($menu, $l->menu_id)!!}


              </li>
              @endforeach
          </ol>
      </div>
      <button type="button" class="btn btn-sm btn-info pull-right btnadd" onclick="$('#type').val('add');$('.menu').val('');$('textarea').val('');$('.modal').modal('show');" name="button"> <i class="fa fa-plus" aria-hidden></i> Baru</button>
    </div>

    </div>

    <div class="newmenu" style="display:none">
      @php $id = rnd(4); @endphp
      <li class="dd-item dd3-item" id="dataid" data-id="{{$id}}">
        <input  type="hidden" id="id" name="menu_id[]" value="{{$id}}">
        <input  type="hidden" id="parent" name="menu_parent[]" value="0">
        <input  type="hidden" id="name" name="menu_name[]" value="New Menu">
        <input  type="hidden" id="description" name="menu_description[]" value="New Description">
        <input  type="hidden" id="link" name="menu_link[]" value="http://linkmenu.com">
        <input  type="hidden" id="iconx" name="menu_icon[]" value="Icon">

          <div style="cursor:move" class="dd-handle dd3-handle"></div><div class="dd3-content" id="labelname">New Menu</div>
      </li>
    </div>
    <div class="modal" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><span class="modtitle"></span>Form</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
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
                <textarea  type="text" class="menu form-control description" name="descriptions" placeholder="Masukkan Keterangan Menu" value=""></textarea>
              </div>
              <div class="form-group ">
                <label for="">Url Tujuan </label>
                  <div class="autocomplete-box">
                    <input type="text" class="menu form-control link" name="links"  id="link_target" placeholder="Masukkan Url Tujuan">
                    <div id="autocomplete-list" class="autocomplete-results"></div>
                  </div>
              </div>
              <div class="form-group">
                <label for="">Icon</label>
                <input type="text" class="menu form-control iconx" name="icons" placeholder="Masukkan Url Icon" value="-">
              </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary save" onclick="setmenu();$('.modal').modal('hide')" type="button" name="save" value="">Simpan</button>

          </div>
        </form>

        </div>
      </div>
    </div>
 