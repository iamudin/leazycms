<!-- Page content-->
        <div class="container">
            <div class="row">
                <!-- Blog entries-->
                <div class="col-lg-8">
                    <!-- Featured blog post-->
					@foreach(query()->index_limit('berita',1) as $row)
					
                    <div class="card mb-4">
                        <a href="#!"><img class="card-img-top" src="{{$row->thumbnail}}" alt="..." /></a>
                        <div class="card-body">
                            <div class="small text-muted">{{$row->created}}</div>
                            <h2 class="card-title">{{$row->title}}</h2>
                            <p class="card-text">{{$row->short_content}}</p>
                            <a class="btn btn-primary" href="{{$row->url}}">Read more →</a>
                        </div>
                    </div>
					@endforeach
                    <!-- Nested row for non-featured blog posts-->
                    <div class="row">

					@foreach(query()->index_skip('berita',1,4) as $row)

                        <div class="col-lg-6">
                            <!-- Blog post-->
                        
                            <!-- Blog post-->
                            <div class="card mb-4">
                                <a href="#!"><img class="card-img-top" src="{{$row->thumbnail}}" alt="..." /></a>
                                <div class="card-body">
                                    <div class="small text-muted">{{$row->created}}</div>
                                    <h2 class="card-title h4">{{$row->title}}</h2>
                                    <p class="card-text">{{$row->short_content}}</p>
                                    <a class="btn btn-primary" href="{{$row->url}}">Read more →</a>
                                </div>
                            </div>
                        </div>
				@endforeach

                  
                    </div>
                    <!-- Pagination-->
                    <nav aria-label="Pagination">
                        <hr class="my-0" />
                        <ul class="pagination justify-content-center my-4">
                            <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1" aria-disabled="true">Newer</a></li>
                            <li class="page-item active" aria-current="page"><a class="page-link" href="#!">1</a></li>
                            <li class="page-item"><a class="page-link" href="#!">2</a></li>
                            <li class="page-item"><a class="page-link" href="#!">3</a></li>
                            <li class="page-item disabled"><a class="page-link" href="#!">...</a></li>
                            <li class="page-item"><a class="page-link" href="#!">15</a></li>
                            <li class="page-item"><a class="page-link" href="#!">Older</a></li>
                        </ul>
                    </nav>
                </div>
                <!-- Side widgets-->
                <div class="col-lg-4">
                    <!-- Search widget-->
             	{{get_element('sidebar')}}
                </div>
            </div>
        </div>