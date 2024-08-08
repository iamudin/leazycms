<div class="container">
   <div class="row">
	   <div class="col-lg-8">
		   	<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="/">Beranda</a></li>
    <li class="breadcrumb-item active"  aria-current="page" >{{$detail->title}}</li>
  </ol>
</nav>
		    <h1>{{$detail->title}}</h1>
		   <span class="">{{$detail->created}}</span>
		  @if($detail->media) <img src="{{$detail->thumbnail}}" class="img-thumbnail w-100 my-2">@endif
		   <div class="content">
		   {!!$detail->content!!}
		   </div>
	   </div>
	   <div class="col-lg-4">
	   {{get_element('sidebar')}}
	   </div>
	</div>
</div>