@extends('admin.layout.app',['title'=>'Backup & Restore'])
@section('content')
<div class="row">
<div class="col-lg-12"><h3 style="font-weight:normal"> <i class="fa fa-database" aria-hidden="true"></i> Backup & Restore</h3>
  <div class="alert alert-info">
    Menu ini digunakan untuk Backup dan Restore data Website dalam bentuk  file <b>.zip</b>
  </div>
</div>
<div class="col-lg-12">
  <form class="formres" action="{{URL::full()}}" method="post" >
    @csrf
  <table class="table table-bordered table-hover" id="sampleTable">
    <thead>

      <tr>
        <th style="width:10px">No</th>
        <th>Module</th>
        <th>Data</th>
        <th>Asset</th>
        <th>Last Backup</th>
        <th>Last Restore</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody style="background:white">
      @foreach(json_decode(json_encode(collect(get_module(true))->where('data_title','!=',false)->sortBy('position'))) as $n=>$r)
      @php $co = DB::table('posts')->where('post_type',$r->name)->count() @endphp
      <tr>
        <td align="center">{{$n+1}}</td>
        <td>{{$r->title}}</td>
        <td>{{$co}}</td>
        <td>{{disk_used($r->name)}}</td>
        <td>{{last_backup($r->name)}}</td>
        <td>{{last_restore($r->name)}}</td>
        <td style="width:190px"> <button href="#" @if($co == 0) type="button" disabled @endif name="type" value="{{$r->name}}" class="btn btn-primary btn-sm"> <i class="fa fa-database" aria-hidden></i> Backup</button> <a onclick="$('.typeres').val('{{enc64($r->name)}}');$('.modal').modal('show')" href="javascript:void(0)"  class="btn btn-danger btn-sm"> <i class="fa fa-history" aria-hidden></i> Restore</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</form>
</div>
</div>
<div class="modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Restore Data</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      </div>
      <form id="fileUploadForm" action="{{URL::full()}}" method="post" onsubmit="javascript:void(0)" enctype="multipart/form-data">
        @csrf
      <div class="modal-body">
        <div class="progress" style="display:none">
<div id='percent' class="progress-bar progress-bar-striped progress-bar-animated progress-striped" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0</div>
</div>
<br>
          <div class="form-group file-form">

            <label for="">Pilih Berkas format .zip</label>
            <input required accept="application/x-zip-compressed" type="file" class="form-control form-control-file" name="resfile">
            <input type="hidden" name="typeres" class="typeres" value="">
          </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Submit</button>
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Tutup</button>
      </div>
    </form>

    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
<script type="text/javascript">
$(function () {
          $(document).ready(function () {
              $('#fileUploadForm').ajaxForm({
                  beforeSend: function () {
                      var percentage = '0';
                      $('.file-form').hide();
                      $('.progress').show();
                  },
                  uploadProgress: function (event, position, total, percentComplete) {
                      var percentage = percentComplete;
                      $('.progress .progress-bar').css("width", percentage+'%', function() {
                        return $(this).attr("aria-valuenow", percentage) + "%";
                      })
                        $('.progress .progress-bar').html('Restore '+percentage+'%');
                  },
                  complete: function (xhr) {
                      location.reload();
                  }
              });
          });
      });
</script>
@endsection
