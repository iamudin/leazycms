
@foreach ($module->form->custom_field as $r )
@if ($r[1]->type == 'text')
@include('cms::backend.posts.custom_field.text')
@elseif ($r[1]->type == 'textarea')
@include('cms::backend.posts.custom_field.textarea')
@elseif ($r[1]->type == 'file')
@include('cms::backend.posts.custom_field.file')
@elseif ($r[1]->type == 'image')
@include('cms::backend.posts.custom_field.image')
@elseif ($r[1]->type == 'number')
@include('cms::backend.posts.custom_field.number')
@elseif ($r[1]->type == 'phone')
@include('cms::backend.posts.custom_field.phone')
@elseif ($r[1]->type == 'email')
@include('cms::backend.posts.custom_field.email')
@elseif ($r[1]->type == 'date')
@include('cms::backend.posts.custom_field.date')
@elseif ($r[1]->type == 'datetime')
@include('cms::backend.posts.custom_field.datetime')
@elseif (is_array($r[1]->type))
@include('cms::backend.posts.custom_field.option')
@elseif($r[1]->type =='break')
<h6 for="" style="border-bottom:1px dashed #000;margin-top:10px">{{$r[0]}}</h6>
@else
@endif
@endforeach
