<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="badge badge-primary px-3 py-1.5 font-weight-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.5px; border-radius: 6px; background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe;">
        <i class="fa fa-list-ul mr-1.5"></i> Data {{ $module->form->looping_name }}
    </div>
    <div class="flex-grow-1 ml-3" style="height: 1px; background: #bbbbbbff;"></div>
</div>

<div class="table-responsive looping-table-responsive" style="border: 1.5px solid #e2e8f0; border-radius: 10px; overflow-x: auto; -webkit-overflow-scrolling: touch; background: #ffffff;">
    <table class="table table-hover mb-0" style="font-size: 13px; border-collapse: separate; border-spacing: 0; min-width: 100%;">
        <thead style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
            <tr>
                @foreach($module->form->looping_data as $r)
                    <th class="text-left font-weight-bold text-dark py-2 px-2.5 border-0" style="font-size: 12px; letter-spacing: 0.3px; white-space: nowrap; @if($r[0] == 'Sort') width: 30px; @endif">
                        {{ $r[0] }}
                    </th>
                @endforeach
                <th class="text-center font-weight-bold text-dark py-2 px-2.5 border-0" style="width: 60px; font-size: 12px; white-space: nowrap;">#</th>
            </tr>
        </thead>
        <tbody class="coldata">
            @if($looping_data)
                @foreach(json_decode(json_encode($looping_data)) as $y => $l)
                    <tr id="data-{{ $y }}" style="border-top: 1px solid #f1f5f9;">
                        @foreach($module->form->looping_data as $ky => $r)
                            @php $k = _us($r[0]); @endphp
                            <td class="py-1.5 px-2 align-middle border-0" @if('file' == $r[1]) onmouseover="$('.edit-{{_us($r[0])}}-{{$y}}').show()" onmouseout="$('.edit-{{_us($r[0])}}-{{$y}}').hide()" @endif>
                                @if('file' == $r[1])
                                    @php
                                        if (!empty($l->$k) && media_exists($l->$k)) {
                                            $f[$y] = true;
                                        }
                                    @endphp
                                    @include('cms::backend.posts.looping_data.file')
                                @elseif(is_array($r[1]))
                                    @include('cms::backend.posts.looping_data.option')
                                @elseif('text' == $r[1])
                                    @include('cms::backend.posts.looping_data.text')
                                @elseif('email' == $r[1])
                                    @include('cms::backend.posts.looping_data.email')
                                @elseif('number' == $r[1])
                                    @include('cms::backend.posts.looping_data.number')
                                @elseif('textarea' == $r[1])
                                    @include('cms::backend.posts.looping_data.textarea')
                                @else
                                    <input placeholder="{{ ucwords(mb_strtolower($r[0])) }}..." type="{{ $r[1] }}" class="form-control form-control-sm soft-control" style="min-width: 120px;" name="{{ _us($r[0]) }}[]" value="{{ $l?->$k ?? null }}">
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center py-1.5 px-2 align-middle border-0" style="min-width: 60px; white-space: nowrap;">
                            @if(!Route::is($post->type.'.show'))
                                <i class="fa fa-bars text-muted sort-handle mr-2 pointer" style="cursor: grab; font-size: 13px;" title="Tarik untuk mengurutkan"></i>
                                <i onclick="if(confirm('Hapus baris data ini?')){$('#data-{{$y}}').remove()}" class="fa fa-trash text-danger pointer" style="cursor: pointer; font-size: 13px;" title="Hapus baris"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
        @if(!Route::is($post->type.'.show'))
            <tfoot style="background: #f8fafc; border-top: 2px dashed #cbd5e1;">
                <tr class="addcol">
                    @foreach($module->form->looping_data as $r)
                        <td class="py-2 px-2 align-middle border-0">
                            @if($r[1] == 'file')
                                <input onchange="this.removeAttribute('disabled');" onmouseover="this.removeAttribute('disabled');" onmouseleave="if(this.value.trim() === '') this.setAttribute('disabled','disabled');" disabled accept="{{ allow_mime() }}" title="Format: {{ allowed_ext() }}" type="file" class="form-control-sm compress-image" style="width: 100%; min-width: 120px;" name="{{ _us($r[0]) }}[]">
                            @elseif(is_array($r[1]))
                                <select onmouseover="this.removeAttribute('disabled'); this.focus();" onmouseleave="if(this.value.trim() === '') this.setAttribute('disabled','disabled');" disabled class="form-control form-control-sm soft-control" style="min-width: 130px;" name="{{ _us($r[0]) }}[]">
                                    <option value="">-- Tambah {{ ucwords(mb_strtolower($r[0])) }} --</option>
                                    @foreach($r[1] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($r[1] == 'textarea')
                                <textarea onmouseover="this.removeAttribute('disabled'); this.focus();" onmouseleave="if(this.value.trim() === '') this.setAttribute('disabled','disabled');" disabled placeholder="+ Tambah {{ ucwords(mb_strtolower($r[0])) }}..." rows="1" class="form-control form-control-sm soft-control" style="min-width: 140px;" name="{{ _us($r[0]) }}[]"></textarea>
                            @else
                                <input onmouseover="this.removeAttribute('disabled'); this.focus();" onmouseleave="if(this.value.trim() === '') this.setAttribute('disabled','disabled');" disabled placeholder="+ Tambah {{ ucwords(mb_strtolower($r[0])) }}..." type="{{ $r[1] }}" class="form-control form-control-sm soft-control" style="min-width: 120px;" name="{{ _us($r[0]) }}[]">
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center py-2 px-2 align-middle border-0"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

@if(!Route::is($post->type.'.show'))
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    window.initLoopingDataSortable = function() {
        var tbody = document.querySelector('.coldata');
        if (tbody && typeof Sortable !== 'undefined') {
            new Sortable(tbody, {
                animation: 150,
                handle: '.sort-handle',
                ghostClass: 'bg-light'
            });
        }
    };
    window.initLoopingDataSortable();
</script>
@endif
