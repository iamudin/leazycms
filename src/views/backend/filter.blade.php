<div class="modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span class="modtitle"></span>Periode {{get_module_info('title_crud')}}</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      </div>
      <form class="" action="{{url(admin_path().'/permohonan')}}" method="get">
      <div class="modal-body">
          <div class="form-group">
            <label for="">Tahun</label>
            <select required onchange="$('.bln').show()" name="tahun" class="form-control form-control-sm">
  <option value="">--pilih tahun--</option>
  <option value="2023">2023</option>
  </select>
  <div class="bln mt-2" style="display:none">
            <label for="">Bulan</label>
            <select onchange="$('.tgl').show()" name="bulan" class="form-control form-control-sm" display>
  <option value="">--pilih tahun--</option>
  @for($i=1;$i<=12;$i++)
  <option value="{{$i}}">{{blnindo($i)}}</option>
  @endfor
  </select>
          </div>
          <div class="tgl mt-2"  style="display:none">
            <label for="">Tanggal</label>
            <select name="tanggal" class="form-control form-control-sm" display>
  <option value="">--pilih tanggal--</option>
  @for($i=1;$i<=31;$i++)
  <option value="{{$i}}">{{$i}}</option>
  @endfor
  </select>
          </div>
          </div>
     
         
         
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary save" type="submit">Tampilkan</button>
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Tutup</button>
      </div>
    </form>

    </div>
  </div>
</div>