@if(get_option('whatsapp') && get_option('float_btn_whatsapp','N') == 'Y')
@php
    $waNumber = preg_replace('/[^0-9]/', '', get_option('whatsapp'));
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }
@endphp
<!-- Tombol Floating WhatsApp -->
<a href="https://wa.me/{{ $waNumber }}?text=Halo%2C%20saya%20ingin%20bertanya." 
   target="_blank" 
   rel="noopener noreferrer" 
   class="wa-float" 
   aria-label="Hubungi via WhatsApp" 
   title="Hubungi via WhatsApp">
    <span class="wa-status-dot"></span>
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="wa-icon">
        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.364 2.76 1.057 3.96L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
    </svg>
    <span class="wa-label">Hubungi</span>
</a>

<style>
.wa-float {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 999;
    height: 38px;
    padding: 0 13px 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #25D366;
    color: #ffffff !important;
    border-radius: 9999px;
    text-decoration: none !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35), 0 2px 5px rgba(0, 0, 0, 0.06);
    transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    cursor: pointer;
    box-sizing: border-box;
}

.wa-float .wa-status-dot {
    position: absolute;
    top: -1px;
    right: -1px;
    width: 9px;
    height: 9px;
    background-color: #22c55e;
    border: 1.5px solid #ffffff;
    border-radius: 50%;
    animation: waPulse 2s infinite;
    pointer-events: none;
}

@keyframes waPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

.wa-float .wa-icon {
    width: 18px;
    height: 18px;
    fill: #ffffff;
    flex-shrink: 0;
    display: block;
    transition: transform 0.25s ease;
}

.wa-float .wa-label {
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    color: #ffffff;
    user-select: none;
}

.wa-float:hover {
    background: #20ba5a;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(37, 211, 102, 0.45), 0 3px 8px rgba(0, 0, 0, 0.1);
    color: #ffffff !important;
}

.wa-float:hover .wa-icon {
    transform: scale(1.08);
}

.wa-float:active {
    transform: translateY(0) scale(0.97);
}
</style>
@endif