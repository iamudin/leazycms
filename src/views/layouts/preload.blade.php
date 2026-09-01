@php
  $preload_effect = request()->has('preview_preload') ? request()->query('preview_preload') : get_option('preload_effect', 'none');
  $preload_color = request()->has('preview_color') ? request()->query('preview_color') : get_option('preload_color', '#2563eb');
  if (empty($preload_color)) {
      $preload_color = '#2563eb';
  }
  $favicon = file_exists(public_path('favicon.ico')) ? url('favicon.ico') :  (get_option('favicon') && media(get_option('favicon'))->isExists()  ? url('favicon.icon') : main_domain('favicon.icon'));
  $cleanHex = ltrim($preload_color, '#');
  if (strlen($cleanHex) == 3) {
      $cleanHex = $cleanHex[0].$cleanHex[0].$cleanHex[1].$cleanHex[1].$cleanHex[2].$cleanHex[2];
  }
  $r = hexdec(substr($cleanHex, 0, 2));
  $g = hexdec(substr($cleanHex, 2, 2));
  $b = hexdec(substr($cleanHex, 4, 2));
@endphp

@if($preload_effect !== 'none')
  <style>
    :root {
      --preload-color: {{ $preload_color }};
      --preload-color-rgb: {{ $r }}, {{ $g }}, {{ $b }};
    }

    #page-preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.98);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 999999;
      transition: opacity 0.65s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.65s ease;
      -webkit-backdrop-filter: blur(8px);
      backdrop-filter: blur(8px);
    }

    #page-preloader.preload-top-bar-mode {
      background: transparent !important;
      backdrop-filter: none !important;
      -webkit-backdrop-filter: none !important;
      pointer-events: none !important;
      align-items: flex-start !important;
    }

    /* 1. Circular Progress */
    .preload-circular-wrap {
      position: relative;
      width: 72px;
      height: 72px;
    }
    .preload-spinner-circle {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background:
        radial-gradient(circle closest-side, rgba(255, 255, 255, 0.98) 76%, transparent 79%),
        conic-gradient(var(--preload-color) 0deg, var(--preload-color) 0deg, #e5e7eb 0deg, #e5e7eb 360deg);
      transition: background 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 4px 20px rgba(var(--preload-color-rgb), 0.15);
    }
    .preload-center-logo {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 32px;
      height: 32px;
      object-fit: contain;
      z-index: 2;
    }

    /* 2. Pulse Logo */
    .preload-pulse-wrap {
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 90px;
      height: 90px;
    }
    .preload-pulse-logo {
      width: 46px;
      height: 46px;
      object-fit: contain;
      z-index: 2;
      animation: preloadPulseLogo 1.6s ease-in-out infinite;
    }
    .preload-pulse-ring {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      border: 2px solid var(--preload-color);
      animation: preloadPulseWave 1.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
      opacity: 0;
    }
    .preload-pulse-ring:nth-child(2) {
      animation-delay: 0.6s;
    }
    @keyframes preloadPulseLogo {
      0%, 100% { transform: scale(0.92); opacity: 0.85; }
      50% { transform: scale(1.1); opacity: 1; }
    }
    @keyframes preloadPulseWave {
      0% { transform: scale(0.4); opacity: 0.9; }
      100% { transform: scale(1.35); opacity: 0; }
    }

    /* 3. Top Progress Bar */
    .preload-top-bar-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: rgba(var(--preload-color-rgb), 0.12);
      z-index: 999999;
      overflow: hidden;
    }
    .preload-top-bar {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--preload-color), #38bdf8, var(--preload-color));
      background-size: 200% 100%;
      box-shadow: 0 0 12px var(--preload-color), 0 0 6px var(--preload-color);
      transition: width 0.35s cubic-bezier(0.1, 0.9, 0.2, 1);
      animation: preloadBarShimmer 2s linear infinite;
    }
    @keyframes preloadBarShimmer {
      0% { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }

    /* 4. Dual Orbit Rings */
    .preload-dual-ring-wrap {
      position: relative;
      width: 76px;
      height: 76px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .preload-ring-outer {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      border: 3px solid transparent;
      border-top-color: var(--preload-color);
      border-bottom-color: var(--preload-color);
      animation: preloadSpin 1.4s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }
    .preload-ring-inner {
      position: absolute;
      width: 72%;
      height: 72%;
      border-radius: 50%;
      border: 2.5px solid transparent;
      border-left-color: rgba(var(--preload-color-rgb), 0.7);
      border-right-color: rgba(var(--preload-color-rgb), 0.7);
      animation: preloadSpinReverse 1s linear infinite;
    }
    @keyframes preloadSpin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    @keyframes preloadSpinReverse {
      0% { transform: rotate(360deg); }
      100% { transform: rotate(0deg); }
    }

    /* 5. Dots Wave Pulse */
    .preload-dots-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
    }
    .preload-dots-logo {
      width: 38px;
      height: 38px;
      object-fit: contain;
      opacity: 0.95;
    }
    .preload-dots-container {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .preload-dot {
      width: 11px;
      height: 11px;
      border-radius: 50%;
      background: var(--preload-color);
      animation: preloadDotBounce 1.4s ease-in-out infinite both;
    }
    .preload-dot:nth-child(1) { animation-delay: -0.32s; }
    .preload-dot:nth-child(2) { animation-delay: -0.16s; }
    .preload-dot:nth-child(3) { animation-delay: 0s; }
    .preload-dot:nth-child(4) { animation-delay: 0.16s; }
    @keyframes preloadDotBounce {
      0%, 80%, 100% {
        transform: scale(0.4);
        opacity: 0.35;
      }
      40% {
        transform: scale(1.15);
        opacity: 1;
        box-shadow: 0 0 10px rgba(var(--preload-color-rgb), 0.6);
      }
    }

    /* 6. Geometric Cube Morph */
    .preload-cube-wrap {
      position: relative;
      width: 48px;
      height: 48px;
    }
    .preload-cube {
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--preload-color), rgba(var(--preload-color-rgb), 0.7));
      border-radius: 12px;
      animation: preloadCubeMorph 1.6s ease-in-out infinite;
      box-shadow: 0 8px 24px rgba(var(--preload-color-rgb), 0.3);
    }
    @keyframes preloadCubeMorph {
      0% {
        transform: perspective(120px) rotateX(0deg) rotateY(0deg);
        border-radius: 12px;
      }
      50% {
        transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg);
        border-radius: 50%;
      }
      100% {
        transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);
        border-radius: 12px;
      }
    }

    /* 7. Line Shimmer & Brand */
    .preload-line-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
    }
    .preload-line-logo {
      width: 44px;
      height: 44px;
      object-fit: contain;
      animation: preloadLogoFloat 2s ease-in-out infinite;
    }
    .preload-line-bar {
      position: relative;
      width: 130px;
      height: 3px;
      background: #e5e7eb;
      border-radius: 4px;
      overflow: hidden;
    }
    .preload-line-shimmer {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 45%;
      background: linear-gradient(90deg, transparent, var(--preload-color), transparent);
      border-radius: 4px;
      animation: preloadLaser 1.5s ease-in-out infinite;
    }
    @keyframes preloadLogoFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
    }
    @keyframes preloadLaser {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(250%); }
    }

    /* 8. Glassmorphism Card */
    .preload-glass-card {
      position: relative;
      padding: 24px 30px;
      background: rgba(255, 255, 255, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.95);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08), 0 0 25px rgba(var(--preload-color-rgb), 0.12);
      border-radius: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }
    .preload-glass-spinner {
      position: relative;
      width: 58px;
      height: 58px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .preload-glass-ring {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      border: 3px solid rgba(var(--preload-color-rgb), 0.15);
      border-top-color: var(--preload-color);
      animation: preloadSpin 0.9s linear infinite;
    }

    /* 9. Minimalist Sleek Arc */
    .preload-arc-wrap {
      position: relative;
      width: 52px;
      height: 52px;
    }
    .preload-arc-spinner {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      border: 3px solid rgba(var(--preload-color-rgb), 0.15);
      border-top-color: var(--preload-color);
      border-right-color: var(--preload-color);
      animation: preloadSpin 0.8s cubic-bezier(0.5, 0.1, 0.4, 0.9) infinite;
    }
  </style>

  <!-- Preloader Container -->
  <div id="page-preloader" class="{{ $preload_effect === 'top_bar' ? 'preload-top-bar-mode' : '' }}" data-effect="{{ $preload_effect }}" style="--preload-color: {{ $preload_color }};">

    @if($preload_effect === 'circular')
      <div class="preload-circular-wrap" id="circular-spinner">
        <div class="preload-spinner-circle" id="spinner-circle"></div>
        <img src="{{ $favicon }}" alt="Logo" class="preload-center-logo">
      </div>

    @elseif($preload_effect === 'pulse_logo')
      <div class="preload-pulse-wrap">
        <div class="preload-pulse-ring"></div>
        <div class="preload-pulse-ring"></div>
        <img src="{{ $favicon }}" alt="Logo" class="preload-pulse-logo">
      </div>

    @elseif($preload_effect === 'top_bar')
      <div class="preload-top-bar-container">
        <div class="preload-top-bar" id="preloader-top-bar"></div>
      </div>

    @elseif($preload_effect === 'dual_ring')
      <div class="preload-dual-ring-wrap">
        <div class="preload-ring-outer"></div>
        <div class="preload-ring-inner"></div>
        <img src="{{ $favicon }}" alt="Logo" class="preload-center-logo" style="width:26px; height:26px;">
      </div>

    @elseif($preload_effect === 'dots_wave')
      <div class="preload-dots-wrap">
        <img src="{{ $favicon }}" alt="Logo" class="preload-dots-logo">
        <div class="preload-dots-container">
          <div class="preload-dot"></div>
          <div class="preload-dot"></div>
          <div class="preload-dot"></div>
          <div class="preload-dot"></div>
        </div>
      </div>

    @elseif($preload_effect === 'cube_morph')
      <div class="preload-cube-wrap">
        <div class="preload-cube"></div>
      </div>

    @elseif($preload_effect === 'line_shimmer')
      <div class="preload-line-wrap">
        <img src="{{ $favicon }}" alt="Logo" class="preload-line-logo">
        <div class="preload-line-bar">
          <div class="preload-line-shimmer"></div>
        </div>
      </div>

    @elseif($preload_effect === 'glass_card')
      <div class="preload-glass-card">
        <div class="preload-glass-spinner">
          <div class="preload-glass-ring"></div>
          <img src="{{ $favicon }}" alt="Logo" class="preload-center-logo" style="width:28px; height:28px;">
        </div>
      </div>

    @elseif($preload_effect === 'minimal_spinner')
      <div class="preload-arc-wrap">
        <div class="preload-arc-spinner"></div>
      </div>
    @endif

  </div>

  <script>
    (function () {
      const preloader = document.getElementById("page-preloader");
      if (!preloader) return;

      const topBar = document.getElementById("preloader-top-bar");
      const spinnerCircle = document.getElementById("spinner-circle");
      const effect = preloader.getAttribute("data-effect") || "circular";
      const color = "{{ $preload_color }}";

      let progress = 0;
      let isCompleted = false;

      function updateProgress(newProgress) {
        progress = Math.min(Math.max(newProgress, progress), 100);

        if (topBar) {
          topBar.style.width = progress + "%";
        }

        if (spinnerCircle) {
          const degrees = progress * 3.6;
          spinnerCircle.style.background = `
            radial-gradient(circle closest-side, rgba(255,255,255,0.98) 76%, transparent 79%),
            conic-gradient(${color} 0deg, ${color} ${degrees}deg, #e5e7eb ${degrees}deg, #e5e7eb 360deg)
          `;
        }
      }

      function finishPreloader() {
        if (isCompleted) return;
        isCompleted = true;
        updateProgress(100);

        const fadeDelay = effect === 'top_bar' ? 180 : 260;
        setTimeout(() => {
          preloader.style.opacity = "0";
          preloader.style.pointerEvents = "none";

          setTimeout(() => {
            if (preloader && preloader.parentNode) {
              preloader.parentNode.removeChild(preloader);
            }
          }, 650);
        }, fadeDelay);
      }

      // Initial progress
      updateProgress(10);

      // DOMContentLoaded
      if (document.readyState === "interactive" || document.readyState === "complete") {
        updateProgress(70);
      } else {
        document.addEventListener("DOMContentLoaded", () => {
          updateProgress(70);
        });
      }

      // Full Load
      if (document.readyState === "complete") {
        finishPreloader();
      } else {
        window.addEventListener("load", finishPreloader);
      }

      // Safety timeout in case of long assets or hang
      setTimeout(() => {
        finishPreloader();
      }, 3500);
    })();
  </script>
@endif