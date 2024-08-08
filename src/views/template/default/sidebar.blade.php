<div class="card mb-4">
                        <div class="card-header">Search</div>
                        <div class="card-body">
								<form action="/search" method="post" style="display:inline">
								@csrf
                            <div class="input-group">
							
                                <input class="form-control" name="keyword" type="text" placeholder="Keyword pencairan..." />
                                <button class="btn btn-primary" id="button-search" >Cari</button>
								
                            </div>
									</form>
                        </div>
                    </div>
                    <!-- Categories widget-->
                    <div class="card mb-4">
                        <div class="card-header">Kategori Berita</div>
                        <div class="card-body p-0 ">
                            <div class="row">
                                <div class="col-sm-12">
                                    <ul class="list-unstyled mb-0">
										@foreach(query()->index_category('berita') as $row)
                                        <li><a href="{{url($row->url)}}" style="border-bottom:1px dotted #ccc;background:#fff;padding:7px;display:block;">{{$row->name}} <span class="float-end">( {{$row->posts_count}} )</span></a></li>
										@endforeach
                                      
                                    </ul>
                                </div>
                           
                            </div>
                        </div>
                    </div>
                    <!-- Side widget-->
                    <div class="card mb-4">
                        <div class="card-header">Side Widget</div>
                        <div class="card-body">
						@php $banner = get_banner('samping',5) @endphp
							{{banner_here('Samping',$banner)}}
							@foreach($banner as $row)
							<img src="/{{$row->image}}" class="w-100"><br>
							@endforeach
						</div>
                    </div>