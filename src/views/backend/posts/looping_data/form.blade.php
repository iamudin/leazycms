<div class="table-responsive" style="height:75vh;">
    <br>
    <h6 style="border-bottom:1px dashed #000;font-weight:normal"><b>Data {{ $module->form->looping_name }}</b></h6>
    <table class="table table-bordered table-hover table-striped" style="background:#fff;font-size:small;">
       <thead style="background:#f7f7f7">
          <tr>
             @foreach($module->form->looping_data as $r)
             <th class="text-center" @if($r[0] == 'Sort') style="width:10px"@endif>{{$r[0]}}</th>
             @endforeach
             <th class="text-center">#</th>
          </tr>
       </thead>
       <tbody class="coldata">
         <!--  -->
          @if($looping_data)

          <!--  -->
          @foreach(json_decode(json_encode($looping_data)) as $y=> $l)
          <tr id='data-{{$y}}'>
          @foreach($module->form->looping_data as $ky=>$r)
          <?php $k = _us($r[0]);?>
          <td align="center" @if('file'==$r[1])  onmouseover="$('.edit-{{_us($r[0])}}-{{$y}}').show()" onmouseout="$('.edit-{{_us($r[0])}}-{{$y}}').hide()" @endif>
             @if('file'==$r[1])
             <?php
if(!empty($l->$k) && media_exists($post,$l->$k)){
  $f[$y] = true;
}
?>
            @include('cms::backend.posts.looping_data.file')
             @elseif(is_array($r[1]))
             @include('cms::backend.posts.looping_data.option')
             @elseif('text' == $r[1])
             @include('cms::backend.posts.looping_data.text')
             @elseif('date' == $r[1])
             @include('cms::backend.posts.looping_data.date')
             @elseif('datetime' == $r[1])
             @include('cms::backend.posts.looping_data.datetime')
             @elseif('email' == $r[1])
             @include('cms::backend.posts.looping_data.email')
             @elseif('number' == $r[1])
             @include('cms::backend.posts.looping_data.number')
             @else
             @endif

          </td>

          @endforeach
          <td class="text-center" >  <i @if(isset($f[$y])) onclick="alert('Hapus file terlebih dahulu')" @else onclick="if(confirm('Hapus Data Baris?')){$('#data-{{$y}}').remove()}" @endif class="fa fa-trash pointer text-danger" style="display:inline" aria-hidden></i>  </td>
          </tr>
          @endforeach

          @endif
       </tbody>
       <tfoot style="background:#f7f7f7">
          <tr>
             <td colspan="{{count($module->form->looping_data)+1}}" align="right">
            <button style="display:none" type="button" class="btn btn-sm btn-outline-danger delbut" onclick="$('.coldata tr.nw:last').remove();$('.btnadd').show();$('.delbut').hide();">&nbsp;&nbsp;<i class="fa fa-times" aria-hidden></i></button> <button onclick="$('.coldata').append('<tr class=\'nw\'>'+ $('.addcol').html()+'</tr>');$('.coldata tr.nw select').removeAttr('disabled');$('.coldata tr.nw input').removeAttr('disabled');$('.delbut').show();$('.btnadd').hide()" type="button" class="btn btn-sm btn-outline-info btnadd" name="button"> &nbsp; <i class="fa fa-plus"></i></button></td>
          </tr>
          <tr style="display:none" class="addcol">
             @foreach($module->form->looping_data as $r)
             <td class="text-center">
                @if($r[1]=='file')
                <input  disabled onchange="readFile(this)" type="{{$r[1]}}"  class="form-control-sm" style="width:74px;"   name="{{_us($r[0])}}[]" >
                @elseif(is_array($r[1]))
             <select disabled  class="form-control form-control-sm" name="{{_us($r[0])}}[]">
                <option value="">-pilih {{ucwords(mb_strtolower($r[0]))}}-</option>
                @foreach($r[1] as $r)
                <option value="{{$r}}">{{$r}}</option>
                @endforeach
             </select>
             @else

                <input style="min-width:80px" disabled placeholder="Entri Data {{ucwords(mb_strtolower($r[0]))}}" type="{{$r[1]}}"  class="form-control form-control-sm"  name="{{_us($r[0])}}[]" >
                @endif
             </td>
             @endforeach
             <td></td>
          </tr>
       </tfoot>
    </table>
 </div>
