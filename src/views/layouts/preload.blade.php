@if(get_option('preload') && get_option('preload') == 'Y')
  <style>
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
      z-index: 99999;
      transition: opacity 0.9s cubic-bezier(0.4, 0, 0.2, 1);
      -webkit-backdrop-filter: blur(10px);
      backdrop-filter: blur(10px);
    }

    .circular-spinner {
      position: relative;
      width: 68px;
      height: 68px;
    }

    /* Spinner dengan border tipis 2px - lebih seimbang */
    .spinner-circle {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background:
        radial-gradient(circle closest-side, rgba(255, 255, 255, 0.98) 76%, transparent 79%),
        conic-gradient(#2563eb 0deg, #2563eb 0deg, #e5e7eb 0deg, #e5e7eb 360deg);
      transition: background 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* Logo di tengah spinner */
    .spinner-logo {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 30px;
      height: 30px;
      object-fit: contain;
      z-index: 2;
    }

    /* Saat loading selesai → lingkaran utuh */
    .spinner-complete .spinner-circle {
      background: #2563eb;
    }
  </style>

  <!-- Preloader -->
  <div id="page-preloader">
    <div class="circular-spinner" id="circular-spinner">
      <div class="spinner-circle" id="spinner-circle"></div>
      <img src="{{ get_option('icon') }}" alt="Logo" class="spinner-logo">
    </div>
  </div>

  <script>
    const preloader = document.getElementById("page-preloader");
    const spinnerContainer = document.getElementById("circular-spinner");
    const spinnerCircle = document.getElementById("spinner-circle");

    let progress = 0;

    function updateProgress(newProgress) {
      progress = Math.min(Math.max(newProgress, progress), 100);

      const degrees = progress * 3.6;

      spinnerCircle.style.background = `
        radial-gradient(circle closest-side, rgba(255,255,255,0.98) 76%, transparent 79%),
        conic-gradient(#2563eb 0deg, #2563eb ${degrees}deg, #e5e7eb ${degrees}deg, #e5e7eb 360deg)
      `;
    }

    // Mulai loading
    updateProgress(3);

    // Saat DOM siap
    document.addEventListener("DOMContentLoaded", () => {
      updateProgress(67);
    });

    // Saat semua resource selesai
    window.addEventListener("load", () => {
      updateProgress(100);
      spinnerContainer.classList.add("spinner-complete");

      // Fade out preloader
      setTimeout(() => {
        preloader.style.opacity = "0";

        setTimeout(() => {
          preloader.style.display = "none";
          preloader.remove();
        }, 950);
      }, 300);
    });

    // Backup jika loading lambat
    setTimeout(() => {
      if (progress < 100) {
        updateProgress(100);
        spinnerContainer.classList.add("spinner-complete");
      }
    }, 6500);
  </script>
@endif