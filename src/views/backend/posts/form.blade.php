@if(get_post_type() == 'menu')
@include('cms::backend.posts.form-menu')
@else
    @if(Route::is(get_post_type() . '.show'))
        @include('cms::backend.posts.show')
    @else
    @include('cms::backend.posts.form-default')
    @endif
@endif
