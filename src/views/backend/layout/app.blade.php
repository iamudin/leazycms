<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="authord" content="">
    <!-- Twitter meta-->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:site" content="">
    <meta property="twitter:creator" content="">
    <!-- Open Graph Meta-->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="">
    <meta property="og:title" content="">
    <meta property="og:url" content="">
    <meta property="og:image" content="">
    <meta property="og:description" content="">
    <title>{{$title ? $title.' - Admin Panel '.get_option('site_title') : 'Admin Panel '.get_option('site_title')}}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="yes" name="apple-touch-fullscreen">
    <link rel="apple-touch-icon" sizes="180x180" href="/{{ get_option('icon') ?? noimage() }}">
    <meta name="theme-color" content="#009688"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('backend/css/main.css')}}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="/{{get_option('favicon') ?? noimage() }}" type="image/x-icon" /><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>
        <!-- Main Quill library -->

<style>
.pointer{
  cursor:pointer;
}
</style>
@if(\Session::has('success'))
<script>
window.onload = function() {
  notif("{{Session::get('success')}}","success");
};
</script>
@endif

@if(\Session::has('warning'))
<script>
window.onload = function() {
  notif("{{Session::get('warning')}}","warning");
};
</script>
@endif
@if(\Session::has('danger'))
<script>
window.onload = function() {
  notif("{{Session::get('danger')}}","danger");
};
</script>
@endif

@stack('styles')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  </head>
  <body id="body" class="app sidebar-mini" >
  @include('cms::backend.layout.header')
  @include('cms::backend.layout.sidebar')


  <main class="app-content" style="background: #F0F0F1">

    <style>
    body {
      font-family:sans-serif;
    }
    .text-stroke {
      text-decoration:line-through;
    }
a:hover {
  text-decoration: none;
}
.btop {
  margin-top:-80px;right:0;position:absolute;
}
      input[type=text] {
        background-color:rgb(255,255,255,.8);
      }
      #editor {
        background-color:rgb(255,255,255,.8);

      }

      label.myLabel input[type="file"] {
          position: absolute;
          top: -1000px;
      }


      /***** Example custom styling *****/

      .myLabel {
          border: 1px solid #000;
          padding: 2px 5px;
          margin: 2px;
          background: #fff;
          font-size: 9px;
          cursor: pointer;
          display: inline-block;
      }

      .myLabel:hover {
          background: red;
      }

      .myLabel:active {
          background: #CCF;
      }

      .myLabel:invalid + span {
          color: #fff;
      }

      .myLabel:valid + span {
          color: #fff;
      }
      .card-list{
position: relative;
background: #ffffff;
border-radius: 3px;
padding: 10px;
-webkit-box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
margin-bottom: 10px;
-webkit-transition: all 0.3s ease-in-out;
-o-transition: all 0.3s ease-in-out;
transition: all 0.3s ease-in-out;
      }
    </style>

  @yield('content')

  </main>

<script type="text/javascript">

$('.copy').click(function(){
var $temp = $("<input>");
$("body").append($temp);
$temp.val($(this).attr('data-copy')).select();
document.execCommand("copy");
notif('Copied','info');
$temp.remove();
});

function copy($val){
var $temp = $("<input>");
$("body").append($temp);
$temp.val($val).select();
document.execCommand("copy");
notif('Copied','info');
$temp.remove();
}

</script>
    <!-- Essential javascripts for application to work-->
     <script src="{{secure_asset('backend/js/popper.min.js')}}"></script>
     <script src="{{secure_asset('backend/js/bootstrap.min.js')}}"></script>
     <script src="{{secure_asset('backend/js/main.js')}}"></script>
     <!-- The javascript plugin to display page loading on top-->
     <script src="{{secure_asset('backend/js/plugins/pace.min.js')}}"></script>
     <!-- Page specific javascripts-->
     <script type="text/javascript" src="{{secure_asset('backend/js/plugins/chart.js')}}"></script>

     <script type="text/javascript" src="{{secure_asset('backend/js/plugins/bootstrap-notify.min.js')}}"></script>

     <script type="text/javascript" src="{{secure_asset('backend/js/plugins/sweetalert.min.js')}}"></script>
     @stack('scripts')

   </body>
 </html>
