<!DOCTYPE html>
<html lang="en">
    <head>
     	{{init_meta_header()}}
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{template_asset('css/styles.css')}}" rel="stylesheet" />
    </head>
    <body>
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="{{url('/')}}"><img src="/{{get_option('logo')}}" height="40"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
						@foreach(get_menu('header') as $row)
                        <li class="nav-item"><a class="nav-link" href="{{$row->url}}">{{$row->name}}</a></li>
						@endforeach
                       
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Page header with logo and tagline-->
		@if(isHomePage())
        <header class="mb-4">
			<h1>{{config('modules.version')}}</h1>
           @if($banner=get_banner('slider'))
			<img src="/{{$banner->image}}" class="w-100">
			@endif
        </header>
		@else 
		<br>
		@endif