<div class="container">
   <div class="row">
	   <div class="col-lg-8">
		   	<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="/">Beranda</a></li>
		   <li class="breadcrumb-item "><a href="{{url($module->name)}}">{{$module->title}}</a></li>
    <li class="breadcrumb-item active"  aria-current="page" >{{$category->name ??'Uncategorized'}}</li>
  </ol>
</nav>
<h1>{{$detail->title}}</h1>
<span class="">{{$detail->created}}</span>
		   <img src="{{$detail->thumbnail}}" class="img-thumbnail w-100 my-2">
		   <div class="content">
<pre><code class="theme-androidstudio language-php hljs">
{!! $detail->content !!}
        </code></pre>
		   </div>
		   <div class="pb-5">
		   {{share_button()}}
	   </div></div>
	   <div class="col-lg-4">
	   {{get_element('sidebar')}}
	   </div>
	</div>
</div>
<script>
    window.onload = function() {
        const height = document.body.scrollHeight;
        window.parent.postMessage({ type: 'iframeHeight', height: height }, '*');
    };
</script>