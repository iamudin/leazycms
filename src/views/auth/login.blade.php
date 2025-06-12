<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="{{url('backend/css/main.css')}}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Masuk</title>
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}" />
    <meta property="og:title" content="Masuk - {{$data['title'] ?? 'Web Title'}}" />
<meta property="og:image" content="{{url($data['logo'] ?? noimage())}}" />
<meta property="og:site_name" content="{{$data['title'] ?? 'Web Title'}}" />
<meta property="og:description" content="Masuk Sebagai Admin / Operator" />
<style>
  .no-focus-border {
border: 1px solid #ccc; 
outline: none;
}

.no-focus-border:focus {
border: none;
outline: none;
}
@keyframes move {
  100% {
    transform: translate3d(0, 0, 1px) rotate(360deg);
  }
}

.background {
  position: fixed;
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
  background: #4CB8B6;
  overflow: hidden;
}

.ball {
  position: absolute;
  width: 20vmin;
  height: 20vmin;
  border-radius: 50%;
  backface-visibility: hidden;
  animation: move linear infinite;
}

.ball:nth-child(odd) {
    color: #006D5B;
}

.ball:nth-child(even) {
    color: #FF6F61;
}

/* Using a custom attribute for variability */
.ball:nth-child(1) {
  top: 77%;
  left: 88%;
  animation-duration: 40s;
  animation-delay: -3s;
  transform-origin: 16vw -2vh;
  box-shadow: 40vmin 0 5.703076368487546vmin currentColor;
}
.ball:nth-child(2) {
  top: 42%;
  left: 2%;
  animation-duration: 53s;
  animation-delay: -29s;
  transform-origin: -19vw 21vh;
  box-shadow: -40vmin 0 5.17594621519026vmin currentColor;
}
.ball:nth-child(3) {
  top: 28%;
  left: 18%;
  animation-duration: 49s;
  animation-delay: -8s;
  transform-origin: -22vw 3vh;
  box-shadow: 40vmin 0 5.248179047256236vmin currentColor;
}
.ball:nth-child(4) {
  top: 50%;
  left: 79%;
  animation-duration: 26s;
  animation-delay: -21s;
  transform-origin: -17vw -6vh;
  box-shadow: 40vmin 0 5.279749632220298vmin currentColor;
}
.ball:nth-child(5) {
  top: 46%;
  left: 15%;
  animation-duration: 36s;
  animation-delay: -40s;
  transform-origin: 4vw 0vh;
  box-shadow: -40vmin 0 5.964309466052033vmin currentColor;
}
.ball:nth-child(6) {
  top: 77%;
  left: 16%;
  animation-duration: 31s;
  animation-delay: -10s;
  transform-origin: 18vw 4vh;
  box-shadow: 40vmin 0 5.178483653434181vmin currentColor;
}
.ball:nth-child(7) {
  top: 22%;
  left: 17%;
  animation-duration: 55s;
  animation-delay: -6s;
  transform-origin: 1vw -23vh;
  box-shadow: -40vmin 0 5.703026794398318vmin currentColor;
}
.ball:nth-child(8) {
  top: 41%;
  left: 47%;
  animation-duration: 43s;
  animation-delay: -28s;
  transform-origin: 25vw -3vh;
  box-shadow: 40vmin 0 5.196265905749415vmin currentColor;
}

 </style>
  </head>
  <body class="background">
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <section class="login-content" style=" background-color:rgba(33, 11, 11, 0.4);">
      <div class="login-box" style=" background-color:transparent;box-shadow:none;width:100%">

        <form method="POST"  style="width:300px;margin-left:auto;margin-right:auto"  action="{{$data['loginsubmit'] }}">
          @csrf
          <center>
            <img height="40" src="{{$data['logo']}}" onerror="{{ noimage() }}">
            <br>
            <br>
            <h4 class="text-warning">{{$data['title'] ?? 'Web Title'}}</h4>
           @if(get_option('site_description')) <h6 class="text-white"><i>{{ $data['description'] ?? 'Web description'  }}</i></h6>@endif
            <br>

            @if(get_option('site_maintenance')=='Y')
            <p class="badge badge-danger">Mode Perbaikan Aktif</p>
            @endif
          </center>
                @if (session()->has('error'))
                <div class="alert alert-dismissible alert-danger">
                  <button class="close" type="button" data-dismiss="alert">×</button>
                  {{session()->get('error')}}
                </div>
                @endif

          <div class="form-group  pb-0 mb-2">
            <label class="control-label " style="color:#f5f5f5"><i class="fa fa-at"></i> Nama Pengguna</label>
                <input id="username" onkeyup="this.value = this.value.replace(/\s+/g, '')" placeholder="Isi Nama Pengguna" type="text" class="form-control form-control-lg " name="username" required autocomplete="username" autofocus>
          </div>
          <div class="form-group">
            <label class="control-label" style="color:#f5f5f5"> <i class="fa fa-key"></i> Kata Sandi</label>
                <input id="password" onkeyup="this.value = this.value.replace(/\s+/g, '')" placeholder="Isi Kata Sandi" type="password" class="form-control form-control-lg " name="password" required autocomplete="current-password" autofocus>
          </div>

          <div class="form-group">
            <img src="{{ $captcha }}" alt="" style="border-radius: 5px 0 0 5px;height: 40px;width:45%;"> <input class="no-focus-border" type="text" name="captcha" placeholder="Enter Code" required  maxlength="6" style="font-weight: bold; border:none;float:right;height: 40px;border-radius:0 5px 5px 0;height: 40px;width:55%;">
          </div>
          <div class="form-group text-white">
            <input  type="checkbox" name="remember"> Tetap ingat akun ini <sup class="text-warning"> {!! help('Akun tetap login dibrowser ini selama tidak melakukan logout') !!}</sup>
         </div>
          <div class="form-group btn-container">
            <button class="btn btn-warning btn-block"><i class="fa fa-sign-in fa-lg fa-fw"></i>MASUK</button>
          </div>
          <div class="form-group text-white credit text-center mt-4">
            <small>Build with &#10084; by LeazyCMS v{{ get_leazycms_version() ?? '0.0' }}</small></div>
        </form>
      </div>
    </section>
    <!-- Essential javascripts for application to work-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{url('backend/js/popper.min.js')}}"></script>
    <script src="{{url('backend/js/bootstrap.min.js')}}"></script>
    <script src="{{url('backend/js/main.js')}}"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="{{url('backend/js/plugins/pace.min.js')}}"></script>
    <script type="text/javascript">
      // Login Page Flipbox control
      $('.login-content [data-toggle="flip"]').click(function() {
      	$('.login-box').toggleClass('flipped');
      	return false;
      });
    </script>
  </body>
</html>
