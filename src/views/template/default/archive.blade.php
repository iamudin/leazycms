<section id="hero">
    <div class="hero-container" data-aos="fade-up">
      <h1>Welcome to Serenity</h1>
      <h2>We are team of talented designers making websites with Bootstrap</h2>
      <a href="#about" class="btn-get-started scrollto">Get Started</a>
    </div>
  </section><!-- End Hero -->

  <main id="main">

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container">

        @foreach($index as $row)
        <h1>{{$row->title}}</h1>
        @endforeach
    </div>
    </section>
  </main>
