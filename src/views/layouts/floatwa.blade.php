@if(get_option('whatsapp') && is_main_domain())

<div class="wa-float" onmouseout="$('.wa-label').hide()" onmouseover="$('.wa-label').show()" onclick="location.href='https:\/\/wa.me/{{get_option('whatsapp')}}?text=Halo%2C%20saya%20ingin%20bertanya.'">
  <i class="fab fa-whatsapp fa-2x"></i>
   <span class="wa-label">Hubungi</span>
</div>
@endif