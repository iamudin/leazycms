@if(get_post_type()=='menu')
@include('cms::backend.posts.form-menu')
@else
@include('cms::backend.posts.form-default')
@endif
