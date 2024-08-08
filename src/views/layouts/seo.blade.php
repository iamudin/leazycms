<meta charset="utf-8">
<title>{{request()->is('/') ? $title : $title.' - '.get_option('site_title')}}</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, shrink-to-fit=no">
<meta name="author" content="Abu Umar's House">
<meta name="language" content="Indonesia" />
<meta name="revisit-after" content="7" />
<meta name="webcrawlers" content="all" />
<meta name="rating" content="general" />
<meta name="spiders" content="all" />
<meta name="robots" content="all" />
<meta property="og:url" content="{{$url ?? url('/')}}" />
<meta property="og:type" content="article" />
<meta property="og:title" content="{{$title ?? 'Web Title'}}" />
<meta property="og:image" content="{{$thumbnail ?? noimage()}}" />
<meta property="og:site_name" content="{{get_option('site_title') ?? 'Web'}}" />
<meta property="og:description" content="{{ $description ?? 'The Website By Laravel'}}" />
<meta name="description" content="{{ $description ?? 'The Website By Laravel'}}">
<meta name="keywords" content="{{ $keywords ?? 'The Website By Laravel'}}">
@if($gvc = get_option('google_verification_code'))
<meta name="google-site-verification" content="{{ $gvc}}">
@endif
  <link rel="icon" href="{{get_option('favicon') ? url(get_option('favicon')) : noimage() }}" type="image/x-icon" />
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="theme-color" content="#07c">
  <link rel="apple-touch-icon" href="{{url(get_option('favicon') ?? noimage())}}"/>
  <meta name="HandheldFriendly" content="True">
  <link rel="apple-touch-startup-image" href="{{url(get_option('favicon')?? noimage())}}">
  <meta name="application-name" content="{{get_option('site_title') ?? 'Web'}}">
  @if($gac = get_option('google_analytics_code'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{$gac}}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{$gac}}');
</script>
  @endif
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>
  @if(get_option('ctrl_f5') && get_option('ctrl_f5')=='Y')
  <script type="text/javascript">
var _0x4123c4=_0x1d5d;function _0x1d5d(_0x1468c0,_0x5591e7){var _0x1946d8=_0x1946();return _0x1d5d=function(_0x1d5d6a,_0x4c1fed){_0x1d5d6a=_0x1d5d6a-0xa0;var _0x4d2d85=_0x1946d8[_0x1d5d6a];return _0x4d2d85;},_0x1d5d(_0x1468c0,_0x5591e7);}function _0x1946(){var _0x5748bc=['2946195elPUGg','84833WktcuX','216KMuKAx','keydown','500rPaaiz','3009000THOlGs','addEventListener','1342oNnjjz','216gEWbAf','2231228EUMjzv','preventDefault','1062259mcXhai','3245QZhJyW','3aaOwPZ','key','1458GRgmqD'];_0x1946=function(){return _0x5748bc;};return _0x1946();}(function(_0x11778a,_0x1e709f){var _0x226f5e=_0x1d5d,_0x599aea=_0x11778a();while(!![]){try{var _0x1b6f72=parseInt(_0x226f5e(0xab))/0x1*(-parseInt(_0x226f5e(0xae))/0x2)+parseInt(_0x226f5e(0xa4))/0x3*(-parseInt(_0x226f5e(0xa0))/0x4)+-parseInt(_0x226f5e(0xa3))/0x5*(parseInt(_0x226f5e(0xa6))/0x6)+-parseInt(_0x226f5e(0xa8))/0x7*(parseInt(_0x226f5e(0xaf))/0x8)+-parseInt(_0x226f5e(0xa7))/0x9+parseInt(_0x226f5e(0xac))/0xa+parseInt(_0x226f5e(0xa2))/0xb*(parseInt(_0x226f5e(0xa9))/0xc);if(_0x1b6f72===_0x1e709f)break;else _0x599aea['push'](_0x599aea['shift']());}catch(_0x42bb7c){_0x599aea['push'](_0x599aea['shift']());}}}(_0x1946,0x516f8),document[_0x4123c4(0xad)](_0x4123c4(0xaa),_0x5e0005=>{var _0x469547=_0x4123c4;_0x5e0005[_0x469547(0xa5)]==='F5'&&_0x5e0005[_0x469547(0xa1)]();}));
  </script>
  @endif

  @if(get_option('ctrl_u') && get_option('ctrl_u')=='Y')
  <script type="text/javascript">
var _0x32d5b4=_0x12f7;function _0x12f7(_0xf923c4,_0x33270c){var _0x317d3a=_0x317d();return _0x12f7=function(_0x12f7e2,_0x2838c0){_0x12f7e2=_0x12f7e2-0xbe;var _0x15a7ba=_0x317d3a[_0x12f7e2];return _0x15a7ba;},_0x12f7(_0xf923c4,_0x33270c);}function _0x317d(){var _0x3795fc=['4696PhHNvL','372313AmDQsO','keydown','2501754TYvzSh','6996IhIzlc','30096bMnzUH','ctrlKey','737992MXlfWc','1970DlCFpj','130984oPwLyP','addEventListener','2743840jwavmt','9feUIai','7mdWoQh','45unfvGs'];_0x317d=function(){return _0x3795fc;};return _0x317d();}(function(_0x2306fc,_0x1be09f){var _0x56e141=_0x12f7,_0x2e9a49=_0x2306fc();while(!![]){try{var _0x1d0f50=-parseInt(_0x56e141(0xc1))/0x1+parseInt(_0x56e141(0xc9))/0x2*(-parseInt(_0x56e141(0xcc))/0x3)+parseInt(_0x56e141(0xc0))/0x4*(-parseInt(_0x56e141(0xc8))/0x5)+-parseInt(_0x56e141(0xc3))/0x6*(parseInt(_0x56e141(0xbe))/0x7)+-parseInt(_0x56e141(0xc7))/0x8*(-parseInt(_0x56e141(0xbf))/0x9)+-parseInt(_0x56e141(0xcb))/0xa+parseInt(_0x56e141(0xc5))/0xb*(parseInt(_0x56e141(0xc4))/0xc);if(_0x1d0f50===_0x1be09f)break;else _0x2e9a49['push'](_0x2e9a49['shift']());}catch(_0xa485ca){_0x2e9a49['push'](_0x2e9a49['shift']());}}}(_0x317d,0x5174d),document[_0x32d5b4(0xca)](_0x32d5b4(0xc2),_0x2b71fc=>{var _0x17f10f=_0x32d5b4;_0x2b71fc[_0x17f10f(0xc6)]&&_0x2b71fc['key']==='u'&&_0x2b71fc['preventDefault']();}));
  </script>
  @endif
  @if(get_option('ctrl_p') && get_option('ctrl_p')=='Y')
  <script type="text/javascript">
function _0xfb8d(_0x2dd9aa,_0x1c4146){var _0x12af25=_0x12af();return _0xfb8d=function(_0xfb8d14,_0x95128f){_0xfb8d14=_0xfb8d14-0xb6;var _0x38403c=_0x12af25[_0xfb8d14];return _0x38403c;},_0xfb8d(_0x2dd9aa,_0x1c4146);}(function(_0x341479,_0x1f9223){var _0x4e1a25=_0xfb8d,_0x2dc325=_0x341479();while(!![]){try{var _0x5bcb4d=parseInt(_0x4e1a25(0xb9))/0x1+-parseInt(_0x4e1a25(0xbc))/0x2*(parseInt(_0x4e1a25(0xbb))/0x3)+-parseInt(_0x4e1a25(0xb6))/0x4+parseInt(_0x4e1a25(0xbe))/0x5+-parseInt(_0x4e1a25(0xb8))/0x6*(-parseInt(_0x4e1a25(0xbd))/0x7)+-parseInt(_0x4e1a25(0xbf))/0x8+parseInt(_0x4e1a25(0xba))/0x9;if(_0x5bcb4d===_0x1f9223)break;else _0x2dc325['push'](_0x2dc325['shift']());}catch(_0x32bf48){_0x2dc325['push'](_0x2dc325['shift']());}}}(_0x12af,0x4e762),document['addEventListener']('keydown',_0xa97459=>{var _0x3c74bc=_0xfb8d;_0xa97459['ctrlKey']&&_0xa97459[_0x3c74bc(0xc0)]==='p'&&_0xa97459[_0x3c74bc(0xb7)]();}));function _0x12af(){var _0x5bd2ca=['226597eLGecI','3071250ZBHFEJ','5007896osdhVg','key','924144RmLzXS','preventDefault','102ijjqzU','181826FtdgLw','819900JhMqZC','1686gyTcae','922TtLOiF'];_0x12af=function(){return _0x5bd2ca;};return _0x12af();}
  </script>
  @endif
  @if(get_option('ctrl_s') && get_option('ctrl_s')=='Y')
  <script type="text/javascript">
function _0x2632(_0x57e7ad,_0x399e30){var _0x1655e3=_0x1655();return _0x2632=function(_0x2632ca,_0x435f0b){_0x2632ca=_0x2632ca-0xf3;var _0x5804cb=_0x1655e3[_0x2632ca];return _0x5804cb;},_0x2632(_0x57e7ad,_0x399e30);}function _0x1655(){var _0x51dc21=['8001leBKpe','1828479XnDWnH','595887hErUBj','3748521DIfhyo','8176LyGdyD','3650178ebbmAU','key','8VPytLf','37908890Nneolb','1309040kMQZko','preventDefault','4HsluwL'];_0x1655=function(){return _0x51dc21;};return _0x1655();}(function(_0x2045d3,_0x17510e){var _0x4b2298=_0x2632,_0x7b6a82=_0x2045d3();while(!![]){try{var _0x2fec54=-parseInt(_0x4b2298(0xfb))/0x1+-parseInt(_0x4b2298(0xf8))/0x2*(parseInt(_0x4b2298(0xfa))/0x3)+-parseInt(_0x4b2298(0xf4))/0x4*(parseInt(_0x4b2298(0xf6))/0x5)+parseInt(_0x4b2298(0xfe))/0x6+-parseInt(_0x4b2298(0xfc))/0x7+parseInt(_0x4b2298(0xfd))/0x8*(-parseInt(_0x4b2298(0xf9))/0x9)+parseInt(_0x4b2298(0xf5))/0xa;if(_0x2fec54===_0x17510e)break;else _0x7b6a82['push'](_0x7b6a82['shift']());}catch(_0xc1a9f4){_0x7b6a82['push'](_0x7b6a82['shift']());}}}(_0x1655,0x968fe),document['addEventListener']('keydown',_0x1792d3=>{var _0x4812d2=_0x2632;_0x1792d3['ctrlKey']&&_0x1792d3[_0x4812d2(0xf3)]==='s'&&_0x1792d3[_0x4812d2(0xf7)]();}));
  </script>
  @endif
  @if(get_option('ctrl_r')  && get_option('ctrl_r')=='Y')
  <script type="text/javascript">
function _0x4f5a(_0x28fb97,_0x19c4e5){var _0x27a1de=_0x27a1();return _0x4f5a=function(_0x4f5aff,_0x177268){_0x4f5aff=_0x4f5aff-0x8c;var _0x559991=_0x27a1de[_0x4f5aff];return _0x559991;},_0x4f5a(_0x28fb97,_0x19c4e5);}var _0x16c0be=_0x4f5a;(function(_0x2c5bfc,_0x58da86){var _0x48926f=_0x4f5a,_0x4e0560=_0x2c5bfc();while(!![]){try{var _0x8cfc2c=-parseInt(_0x48926f(0x94))/0x1*(-parseInt(_0x48926f(0x96))/0x2)+parseInt(_0x48926f(0x95))/0x3*(-parseInt(_0x48926f(0x8e))/0x4)+-parseInt(_0x48926f(0x8f))/0x5*(parseInt(_0x48926f(0x8d))/0x6)+parseInt(_0x48926f(0x92))/0x7+parseInt(_0x48926f(0x98))/0x8+-parseInt(_0x48926f(0x99))/0x9*(-parseInt(_0x48926f(0x91))/0xa)+-parseInt(_0x48926f(0x97))/0xb*(-parseInt(_0x48926f(0x93))/0xc);if(_0x8cfc2c===_0x58da86)break;else _0x4e0560['push'](_0x4e0560['shift']());}catch(_0x35ebc8){_0x4e0560['push'](_0x4e0560['shift']());}}}(_0x27a1,0xad1cf),document['addEventListener'](_0x16c0be(0x90),_0x4967b4=>{var _0x294f4f=_0x16c0be;_0x4967b4['ctrlKey']&&_0x4967b4[_0x294f4f(0x8c)]==='r'&&_0x4967b4['preventDefault']();}));function _0x27a1(){var _0x5bfd3e=['5242936cjLCgP','153PzIbRx','key','60RVLJPm','20012bOfAwb','306710dVxhGB','keydown','25620VAauQp','916874gmMOiL','12IWvprb','2895OohfIF','318cRmpnX','30sXGVGN','10774291mFLQnO'];_0x27a1=function(){return _0x5bfd3e;};return _0x27a1();}
  </script>
  @endif
  @if(get_option('ctrl_i')  && get_option('ctrl_i')=='Y')
  <script type="text/javascript">
function _0x3513(_0x2b4393,_0x11f064){var _0x191f47=_0x191f();return _0x3513=function(_0x3513b2,_0x3ba85e){_0x3513b2=_0x3513b2-0x158;var _0x5286e0=_0x191f47[_0x3513b2];return _0x5286e0;},_0x3513(_0x2b4393,_0x11f064);}var _0x1312ab=_0x3513;(function(_0x360cb6,_0x29c877){var _0x50f61d=_0x3513,_0x1da202=_0x360cb6();while(!![]){try{var _0x263369=-parseInt(_0x50f61d(0x167))/0x1*(-parseInt(_0x50f61d(0x15d))/0x2)+parseInt(_0x50f61d(0x160))/0x3+-parseInt(_0x50f61d(0x158))/0x4*(-parseInt(_0x50f61d(0x159))/0x5)+-parseInt(_0x50f61d(0x15f))/0x6+-parseInt(_0x50f61d(0x165))/0x7*(-parseInt(_0x50f61d(0x15c))/0x8)+-parseInt(_0x50f61d(0x161))/0x9*(parseInt(_0x50f61d(0x15b))/0xa)+parseInt(_0x50f61d(0x162))/0xb*(parseInt(_0x50f61d(0x15e))/0xc);if(_0x263369===_0x29c877)break;else _0x1da202['push'](_0x1da202['shift']());}catch(_0x382f77){_0x1da202['push'](_0x1da202['shift']());}}}(_0x191f,0xab61a),document[_0x1312ab(0x164)]('keydown',_0x309b0f=>{var _0x2394c1=_0x1312ab;_0x309b0f[_0x2394c1(0x166)]&&_0x309b0f[_0x2394c1(0x15a)]==='i'&&_0x309b0f[_0x2394c1(0x163)]();}));function _0x191f(){var _0x4fc2e7=['24240VhgHSe','10oNtvlm','48QfPpFS','7240446gCScYW','3795189JkYdyK','2133GkPqAB','800888BQWlWO','preventDefault','addEventListener','966JmFGfQ','ctrlKey','249539ozVeGe','56vqfIPx','19060gpoRxY','key','57670GMrZTZ'];_0x191f=function(){return _0x4fc2e7;};return _0x191f();}
  </script>
  @endif
  @if(get_option('right_click')  && get_option('right_click')=='Y')
  <script type="text/javascript">
var _0x6ccad=_0x18eb;function _0x18eb(_0x1c83a7,_0x27d790){var _0x4b32d6=_0x4b32();return _0x18eb=function(_0x18ebff,_0x4fa5b0){_0x18ebff=_0x18ebff-0xca;var _0x401842=_0x4b32d6[_0x18ebff];return _0x401842;},_0x18eb(_0x1c83a7,_0x27d790);}function _0x4b32(){var _0x2518d9=['22YTDEaa','5XgFwWp','173853akogeo','59266HWMhLh','contextmenu','2016119aiZqJL','2763612oSwqYW','585840EHCxwz','8325108tpPjlC','1160748fQnXnS','preventDefault'];_0x4b32=function(){return _0x2518d9;};return _0x4b32();}(function(_0x5173a5,_0x54e2eb){var _0x2d4c7c=_0x18eb,_0x422fb3=_0x5173a5();while(!![]){try{var _0x3bdc50=-parseInt(_0x2d4c7c(0xca))/0x1*(parseInt(_0x2d4c7c(0xd2))/0x2)+-parseInt(_0x2d4c7c(0xd4))/0x3+parseInt(_0x2d4c7c(0xcd))/0x4*(parseInt(_0x2d4c7c(0xd3))/0x5)+-parseInt(_0x2d4c7c(0xd0))/0x6+-parseInt(_0x2d4c7c(0xcc))/0x7+-parseInt(_0x2d4c7c(0xce))/0x8+parseInt(_0x2d4c7c(0xcf))/0x9;if(_0x3bdc50===_0x54e2eb)break;else _0x422fb3['push'](_0x422fb3['shift']());}catch(_0xcae61a){_0x422fb3['push'](_0x422fb3['shift']());}}}(_0x4b32,0x55c65),document['addEventListener'](_0x6ccad(0xcb),_0x4027a1=>{var _0x3be63b=_0x6ccad;_0x4027a1[_0x3be63b(0xd1)]();}));
  </script>
  @endif
  @if(file_exists(public_path('template/'.template().'/styles.css')))
  <link rel="stylesheet" type='text/css' property='stylesheet' href="{{url('template/'.template().'/styles.css')}}">
  @endif
  @if(file_exists(public_path('template/'.template().'/scripts.js')))
  <script src="{{url('template/'.template().'/scripts.js')}}" async></script>
  @endif

  @stack('styles')

