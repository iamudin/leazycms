@extends('cms::layouts.layout')
@section('content')
@include(View::exists(get_view(get_view())) ? blade_path(get_view()) : blade_path(get_post_type('view_type')))
@include('cms::layouts.feed_ad')
@endsection


