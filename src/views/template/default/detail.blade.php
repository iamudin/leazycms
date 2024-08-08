<main id="main">
    <section id="cta" class="cta">
      <div class="container" data-aos="fade-in">
        <div class="text-center">
          <h3>{{ $detail->title }}</h3>
          <img src="{{ $detail->thumbnail}}" style="width:100%" alt="">
         {{$detail->content}}
        </div>
      </div>
    </section>
</main>
