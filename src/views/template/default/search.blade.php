<div class="container">
   <div class="row">
	   <div class="col-lg-8">
		<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="/">Beranda</a></li>
		   <li class="breadcrumb-item active">Hasil Pencarian "{{$keyword}}</li>
  </ol>
</nav>
		   <div class="list-content">
			         <div class="row">

					@foreach($index as $row)

                        <div class="col-lg-6">
                            <!-- Blog post-->
                        
                            <!-- Blog post-->
                            <div class="card mb-4">
                                <a href="#!"><img class="card-img-top" src="{{$row->thumbnail}}" alt="..." /></a>
                                <div class="card-body">
                                    <div class="small text-muted">{{$row->created}}</div>
                                    <h2 class="card-title h4">{{$row->title}}</h2>
                                    <p class="card-text">{{$row->short_content}}</p>
                                    <a class="btn btn-primary" href="{{url($row->url)}}">Read more →</a>
                                </div>
                            </div>
                        </div>
				@endforeach

                  
                    </div>
		   </div>
	   </div>
	   <div class="col-lg-4">
	   {{get_element('sidebar')}}
	   </div>
	</div>
</div>