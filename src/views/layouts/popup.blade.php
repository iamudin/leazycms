 <style>
    /* Gunakan style yang sama seperti sebelumnya, ditambahkan tombol navigasi */
    .modals {
      position: fixed;
      z-index: 1000;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(231, 231, 231, 0.9);
      overflow: auto;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.4s ease, visibility 0.4s ease;
    }

    .modals.show {
      opacity: 1;
      visibility: visible;
    }

    .modals-inner {
      max-width: 800px;
      margin: 60px auto;
      background: #111;
      padding: 0;
      border-radius: 10px;
      text-align: center;
      transform: translateY(-20px);
      transition: transform 0.4s ease;
      position: relative;
    }

    .modals.show .modals-inner {
      transform: translateY(0);
    }

    .modals-content {
      width: 100%;
    }
    #modal-data {
      max-height: 80vh;
      overflow: auto;
    }
    .modals-header h3 {
      padding: 0 0 12px 0;
      font-size: 20px;
      line-height: 1em;
      color: white;
    }

    .modals-footer {
      padding: 10px;
      color: white;
      font-size: 16px;
      border-bottom-left-radius: 10px;
      border-bottom-right-radius: 10px;
      background: #222;
    }

    .modals-header {
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
      padding: 18px 0 10px 0;
    }

    .closes-btn {
      position: absolute;
      top: 15px;
      right: 25px;
      color: rgb(193, 0, 0);
      font-size: 35px;
      font-weight: bold;
      cursor: pointer;
    }

    .closes-btn:hover {
      color: #bbb;
    }
    .fade {
  opacity: 0;
  transition: opacity 0.4s ease;
}

.fade.show {
  opacity: 1;
}
    .nav-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 10px;
      border-radius:10px;
      color: white;
      background: rgba(0,0,0,0.5);
      padding: 10px;
      cursor: pointer;
      user-select: none;
    }

    .prev-btn {
      left: 10px;
    }

    .next-btn {
      right: 10px;
    }

    label {
      cursor: pointer;
    }
  </style>

  <div id="autoModal" class="modals">
    <span class="closes-btn" id="closeBtn">&times;</span>
    <div class="modals-inner">
      <div class="modals-header">
        <h3 id="bannerTitle">Loading...</h3>
      </div>
      <div id="modal-data">
      <a id="bannerLink" href="#" title="">
        <img id="bannerImage" src="" class="modals-content fade show">
      </a>
      </div>
      <div class="modals-footer">
        <label>
          <input type="checkbox" id="dontShowAgain" /> Jangan tampilkan lagi di sesi ini
        </label>
      </div>

      <!-- Navigasi kiri-kanan -->
      @if(count($banners)>1)
      <span class="nav-btn prev-btn" id="prevBtn">&#10094;</span>
      <span class="nav-btn next-btn" id="nextBtn">&#10095;</span>
      @endif
    </div>
  </div>

  <script>
    const modal = document.getElementById('autoModal');
    const closeBtn = document.getElementById('closeBtn');
    const dontShowAgain = document.getElementById('dontShowAgain');
    const bannerTitle = document.getElementById('bannerTitle');
    const bannerImage = document.getElementById('bannerImage');
    let startX = 0;

bannerImage.addEventListener("touchstart", function(e) {
  startX = e.touches[0].clientX;
}, false);

bannerImage.addEventListener("touchend", function(e) {
  let endX = e.changedTouches[0].clientX;
  let diffX = startX - endX;

  if (Math.abs(diffX) > 50) {
    if (diffX > 0) {
      currentIndex = (currentIndex + 1) % banners.length;
    } else {
      currentIndex = (currentIndex - 1 + banners.length) % banners.length;
    }
    showBanner(currentIndex);
  }
}, false);

    const bannerLink = document.getElementById('bannerLink');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    const banners = @json($banners);

    let currentIndex = 0;

    function showBanner(index) {
  bannerImage.classList.remove('show');
  bannerImage.classList.add('fade');

  setTimeout(() => {
    const banner = banners[index];
    bannerTitle.textContent = banner.name;
    bannerImage.src = banner.image;
    bannerImage.alt = banner.description;
    bannerLink.href = banner.link || "javascript::void(0)";
    bannerLink.title = banner.description;
    bannerImage.classList.add('show');
  }, 400);
}


    window.addEventListener('load', function () {
        const expireTime = localStorage.getItem('dontShowModalUntil');
  if (!expireTime || Date.now() > parseInt(expireTime)) {
    setTimeout(() => {
      modal.classList.add('show');
      showBanner(currentIndex);
    }, 300);
  }
});

    function closeModal() {
  if (dontShowAgain.checked) {
    const oneHourLater = Date.now() + 10 * 60 * 1000;
    localStorage.setItem('dontShowModalUntil', oneHourLater);
  }
  modal.classList.remove('show');
}

    function nextBanner() {
      currentIndex = (currentIndex + 1) % banners.length;
      showBanner(currentIndex);
    }

    function prevBanner() {
      currentIndex = (currentIndex - 1 + banners.length) % banners.length;
      showBanner(currentIndex);
    }

    closeBtn.onclick = closeModal;
    nextBtn.onclick = nextBanner;
    prevBtn.onclick = prevBanner;

    modal.onclick = function (e) {
      if (e.target === modal) {
        closeModal();
      }
    };
  </script>