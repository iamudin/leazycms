@if($banners)
  <div id="autoModal" class="modals">
    <div class="modals-inner">

      <!-- CLOSE -->
      <div class="closes-btn" id="closeBtn">&times;</div>

      <!-- HEADER -->
      <div class="modals-header">
        <h3 id="bannerTitle" style="color:#ccc">Loading...</h3>
      </div>

      <!-- CONTENT -->
      <div id="modal-data">
        <a id="bannerLink" href="#">
          <img id="bannerImage" src="" class="modals-content fade show">
        </a>

        <!-- NAV -->
        <div class="nav-btn prev-btn" id="prevBtnPopup">&#10094;</div>
        <div class="nav-btn next-btn" id="nextBtnPopup">&#10095;</div>
      </div>

      <!-- FOOTER -->
      <div class="modals-footer">
    <button id="dontShowBtn" class="dont-show-btn">
      Jangan tampilkan lagi (10 menit)
    </button>
      </div>

    </div>
  </div>

  <style>

    .modals {
      position: fixed;
      inset: 0;
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;

      opacity: 0;
      visibility: hidden;
      transition: all 0.35s ease;

      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(6px);
    }

    .modals.show {
      opacity: 1;
      visibility: visible;
    }

    .modals-inner {
      width: 92%;
      max-width: 720px;
      background: #fff;
      border-radius: 16px;
      overflow: hidden;

      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);

      transform: scale(0.92) translateY(20px);
      opacity: 0;
      transition: all 0.35s ease;
    }

    .modals.show .modals-inner {
      transform: scale(1) translateY(0);
      opacity: 1;
    }

   .modals-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, #111, #333);
    color: #fff;

    display: flex;
    align-items: center;   /* 🔥 ini yang bikin vertical middle */
    justify-content: left;

    min-height: 60px;
    text-align: left;
  }

  .modals-header h3 {
    margin: 0;
  }

    #modal-data {
      max-height: 70vh;
      overflow-y: auto;
      overflow-x: hidden;
      width:100%;
      position: relative;
    }

    .modals-content {
      width: 100%;
      display: block;
      transition: transform 0.4s ease;
    }

    .modals-content:hover {
      transform: scale(1.03);
    }

    .modals-footer {
      padding: 14px 18px;
      background: #f9f9f9;
      text-align: right;
      font-size: 14px;
      color: #444;
    }


  /* turunkan nav */
  .nav-btn {
    z-index: 2;
  }

  /* modal content normal */
  #modal-data {
    position: relative;
    z-index: 1;
  }
    .closes-btn {
      position: absolute;
      top: 14px;
      right: 16px;
      width: 36px;
      height: 36px;

      border-radius: 50%;
      background: rgba(255, 255, 255, 0.15);

      display: flex;
      align-items: center;
      justify-content: center;

      color: #fff;
      font-size: 18px;
      cursor: pointer;
    }

    .closes-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .nav-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 42px;
      height: 42px;

      border-radius: 50%;
      background: rgba(0, 0, 0, 0.4);

      display: flex;
      align-items: center;
      justify-content: center;

      color: #fff;
      cursor: pointer;
    }

    .prev-btn {
      left: 12px;
    }

    .next-btn {
      right: 12px;
    }

    .nav-btn:hover {
      background: rgba(0, 0, 0, 0.7);
    }

    .fade {
      opacity: 0;
      transition: opacity 0.4s ease;
    }

    .fade.show {
      opacity: 1;
    }
    .dont-show-btn {
    background: linear-gradient(135deg, #d42d62, #a10000);
  }
  .dont-show-btn {
    padding: 4px 7px;
    border-radius: 8px;
    border: none;

    color: #fff;
    font-size: 10px;

    cursor: pointer;
    transition: all 0.2s ease;
  }

  .dont-show-btn:hover {
    background: #333;
  }

  .dont-show-btn:active {
    transform: scale(0.98);
  }
  </style>

  <script>
    const modalPopup = document.getElementById('autoModal');
    const closeBtn = document.getElementById('closeBtn');
    const dontShowBtn = document.getElementById('dontShowBtn');
    const bannerTitle = document.getElementById('bannerTitle');
    const bannerImage = document.getElementById('bannerImage');
    const bannerLink = document.getElementById('bannerLink');
    const prevBtnPopup = document.getElementById('prevBtnPopup');
    const nextBtnPopup = document.getElementById('nextBtnPopup');
    dontShowBtn.onclick = () => closeModal(true);
    closeBtn.onclick = () => closeModal(false);
    /* 🔥 DATA dari Laravel */
    const banners = @json($banners);

    let currentIndex = 0;
    let startXPopup = 0;

    /* 🔥 AUTO HIDE NAV JIKA 1 */
    if (banners.length <= 1) {
      prevBtnPopup.style.display = 'none';
      nextBtnPopup.style.display = 'none';
    }

    /* SWIPE */
    bannerImage.addEventListener("touchstart", e => {
      startXPopup = e.touches[0].clientX;
    });

    bannerImage.addEventListener("touchend", e => {
      let diff = startXPopup - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 50 && banners.length > 1) {
        diff > 0 ? nextBanner() : prevBanner();
      }
    });

    /* SHOW */
    function showBanner(index) {
      bannerImage.classList.remove('show');

      setTimeout(() => {
        const b = banners[index];
        bannerTitle.textContent = b.name;
        bannerImage.src = b.image;
        bannerImage.alt = b.description;
        bannerLink.href = b.link || "#";
        bannerImage.classList.add('show');
      }, 300);
    }

    /* NAV */
    function nextBanner() {
      currentIndex = (currentIndex + 1) % banners.length;
      showBanner(currentIndex);
    }

    function prevBanner() {
      currentIndex = (currentIndex - 1 + banners.length) % banners.length;
      showBanner(currentIndex);
    }

    /* OPEN */
    window.addEventListener('load', () => {
      const expire = localStorage.getItem('dontShowModalUntil');

      if (!expire || Date.now() > expire) {
        setTimeout(() => {
          modalPopup.classList.add('show');
          showBanner(currentIndex);
        }, 1000);
      }
    });

    /* CLOSE */
    function closeModal(setDontShow = false) {
        if (setDontShow) {
          const time = Date.now() + (10 * 60 * 1000);
          localStorage.setItem('dontShowModalUntil', time);
        }
        modalPopup.classList.remove('show');
      }

    /* EVENTS */
    nextBtnPopup.onclick = nextBanner;
    prevBtnPopup.onclick = prevBanner;

    modalPopup.onclick = e => {
      if (e.target === modalPopup) closeModal();
    };

    document.addEventListener('keydown', e => {
      if (e.key === "Escape") closeModal();
    });

    /* AUTO SLIDE (hanya kalau >1 banner) */
    let autoSlide = null;
    if (banners.length > 1) {
      autoSlide = setInterval(nextBanner, 7000);

      modalPopup.addEventListener('mouseenter', () => clearInterval(autoSlide));
      modalPopup.addEventListener('mouseleave', () => {
        autoSlide = setInterval(nextBanner, 7000);
      });
    }
  </script>
@endif