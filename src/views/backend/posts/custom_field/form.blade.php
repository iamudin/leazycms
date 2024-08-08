@foreach ($module->form->custom_field as $r )
@if($r[1] == 'text')
@include('cms::backend.posts.custom_field.text')
@elseif ($r[1] == 'textarea')
@include('cms::backend.posts.custom_field.textarea')
@elseif ($r[1] == 'file')
@include('cms::backend.posts.custom_field.file')
@elseif ($r[1] == 'image')
@include('cms::backend.posts.custom_field.image')
@elseif ($r[1] == 'number')
@include('cms::backend.posts.custom_field.number')
@elseif ($r[1] == 'phone')
@include('cms::backend.posts.custom_field.phone')
@elseif ($r[1] == 'email')
@include('cms::backend.posts.custom_field.email')
@elseif ($r[1] == 'date')
@include('cms::backend.posts.custom_field.date')
@elseif ($r[1] == 'datetime')
@include('cms::backend.posts.custom_field.datetime')
@elseif (is_array($r[1]))
@include('cms::backend.posts.custom_field.option')
@elseif($r[1]=='break')
<br>
<h6 for="" style="border-bottom:1px dashed #000">{{$r[0]}}</h6>
@else
@endif
@endforeach
