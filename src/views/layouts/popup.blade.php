@if($banners)
  <div id="autoModal" class="lz-popup-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="lz-popup-dialog">
      <div class="lz-popup-card">

        <!-- HEADER -->
        <div class="lz-popup-header">
          <div class="lz-popup-header-content">
            <div class="lz-popup-badge">
              <span class="lz-popup-pulse"></span>
              <span>Informasi</span>
            </div>
            <h3 id="bannerTitle" class="lz-popup-title">Loading...</h3>
          </div>

          <!-- CLOSE BUTTON -->
          <button type="button" class="lz-popup-close-btn" id="closeBtn" aria-label="Tutup Popup" title="Tutup">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
              stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <!-- CONTENT / IMAGE AREA -->
        <div id="modal-data" class="lz-popup-body">
          <a id="bannerLink" href="#" class="lz-popup-link" rel="noopener noreferrer">
            <div class="lz-popup-img-wrap">
              <div class="lz-popup-skeleton" id="bannerSkeleton"></div>
              <img id="bannerImage" src="" alt="Banner" class="lz-popup-img lz-fade">
            </div>
          </a>

          <!-- NAV BUTTONS -->
          <button type="button" class="lz-popup-nav lz-prev-btn" id="prevBtnPopup" aria-label="Banner Sebelumnya"
            title="Sebelumnya">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <button type="button" class="lz-popup-nav lz-next-btn" id="nextBtnPopup" aria-label="Banner Berikutnya"
            title="Berikutnya">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>

          <!-- DOTS PAGINATION -->
          <div class="lz-popup-dots-wrap" id="popupDotsWrap"></div>
        </div>

        <!-- CAPTION (Optional) -->
        <div id="bannerCaptionWrap" class="lz-popup-caption-wrap" style="display: none;">
          <p id="bannerCaption" class="lz-popup-caption"></p>
        </div>

        <!-- FOOTER -->
        <div class="lz-popup-footer">
          <div class="lz-popup-counter" id="popupCounterWrap">
            <span id="popupCurrentNum">1</span><span class="lz-popup-counter-sep">/</span><span
              id="popupTotalNum">1</span>
          </div>

          <div class="lz-popup-footer-actions">
            <button type="button" id="dontShowBtn" class="lz-popup-dont-show-btn">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>Jangan tampilkan lagi (10 menit)</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <style>
    /* ============================================================
                 MODERN RESPONSIVE POPUP STYLES (LEAZYCMS)
                 Scoped under #autoModal / .lz-popup-* to prevent collisions
                 ============================================================ */
    #autoModal.lz-popup-modal {
      position: fixed;
      inset: 0;
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
      box-sizing: border-box;

      opacity: 0;
      visibility: hidden;
      transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s ease;

      background: rgba(15, 23, 42, 0.72);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    #autoModal.lz-popup-modal.show {
      opacity: 1;
      visibility: visible;
    }

    #autoModal .lz-popup-dialog {
      width: 100%;
      max-width: 680px;
      margin: auto;
      box-sizing: border-box;

      transform: scale(0.94) translateY(18px);
      opacity: 0;
      transition: transform 0.38s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
    }

    #autoModal.lz-popup-modal.show .lz-popup-dialog {
      transform: scale(1) translateY(0);
      opacity: 1;
    }

    #autoModal .lz-popup-card {
      background: #ffffff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.12);
      display: flex;
      flex-direction: column;
      position: relative;
    }

    /* Header */
    #autoModal .lz-popup-header {
      padding: 14px 20px;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    #autoModal .lz-popup-header-content {
      flex: 1;
      min-width: 0;
    }

    #autoModal .lz-popup-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #38bdf8;
      background: rgba(56, 189, 248, 0.14);
      padding: 2px 8px;
      border-radius: 9999px;
      margin-bottom: 3px;
    }

    #autoModal .lz-popup-pulse {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #38bdf8;
      box-shadow: 0 0 8px #38bdf8;
      display: inline-block;
      animation: lz-pulse-glow 2s infinite;
    }

    @keyframes lz-pulse-glow {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(0.85);
      }
    }

    #autoModal .lz-popup-title {
      margin: 0;
      font-size: 15px;
      font-weight: 600;
      color: #f8fafc;
      line-height: 1.35;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #autoModal .lz-popup-close-btn {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #cbd5e1;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
      flex-shrink: 0;
      padding: 0;
      outline: none;
    }

    #autoModal .lz-popup-close-btn:hover {
      background: rgba(239, 68, 68, 0.88);
      border-color: rgba(239, 68, 68, 0.95);
      color: #ffffff;
      transform: rotate(90deg) scale(1.05);
    }

    /* Body / Banner View */
    #autoModal .lz-popup-body {
      position: relative;
      width: 100%;
      background: #090d16;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 220px;
      max-height: min(65vh, 500px);
      user-select: none;
      -webkit-user-select: none;
    }

    #autoModal .lz-popup-link {
      display: block;
      width: 100%;
      height: 100%;
      text-decoration: none;
    }

    #autoModal .lz-popup-img-wrap {
      position: relative;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #autoModal .lz-popup-skeleton {
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.03) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.03) 75%);
      background-size: 200% 100%;
      animation: lz-shimmer 1.6s infinite;
      z-index: 1;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }

    #autoModal .lz-popup-skeleton.active {
      opacity: 1;
    }

    @keyframes lz-shimmer {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }

    #autoModal .lz-popup-img {
      width: 100%;
      height: auto;
      max-height: min(65vh, 500px);
      object-fit: contain;
      display: block;
      margin: 0 auto;
      transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    #autoModal .lz-popup-link[target="_blank"]:hover .lz-popup-img {
      transform: scale(1.015);
    }

    #autoModal .lz-fade {
      opacity: 0;
    }

    #autoModal .lz-fade.lz-show {
      opacity: 1;
    }

    /* Navigation Arrows */
    #autoModal .lz-popup-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.18);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
      z-index: 5;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
      padding: 0;
      outline: none;
    }

    #autoModal .lz-popup-nav:hover {
      background: rgba(15, 23, 42, 0.95);
      transform: translateY(-50%) scale(1.08);
      border-color: rgba(56, 189, 248, 0.5);
      color: #38bdf8;
    }

    #autoModal .lz-popup-nav:active {
      transform: translateY(-50%) scale(0.96);
    }

    #autoModal .lz-prev-btn {
      left: 12px;
    }

    #autoModal .lz-next-btn {
      right: 12px;
    }

    /* Dots Pagination */
    #autoModal .lz-popup-dots-wrap {
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 9999px;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      z-index: 5;
    }

    #autoModal .lz-popup-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.4);
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      border: none;
      padding: 0;
      outline: none;
    }

    #autoModal .lz-popup-dot.active {
      width: 20px;
      border-radius: 9999px;
      background: #38bdf8;
      box-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
    }

    /* Optional Caption */
    #autoModal .lz-popup-caption-wrap {
      padding: 10px 18px;
      background: #f8fafc;
      border-top: 1px solid #f1f5f9;
      font-size: 13px;
      color: #475569;
      line-height: 1.45;
    }

    #autoModal .lz-popup-caption {
      margin: 0;
    }

    /* Footer */
    #autoModal .lz-popup-footer {
      padding: 12px 18px;
      background: #ffffff;
      border-top: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    #autoModal .lz-popup-counter {
      font-size: 12px;
      font-weight: 600;
      color: #64748b;
      background: #f1f5f9;
      padding: 4px 10px;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      letter-spacing: 0.3px;
    }

    #autoModal .lz-popup-counter-sep {
      opacity: 0.6;
      font-weight: 400;
    }

    #autoModal .lz-popup-dont-show-btn {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #64748b;
      font-size: 12px;
      font-weight: 500;
      padding: 6px 14px;
      border-radius: 9999px;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      outline: none;
    }

    #autoModal .lz-popup-dont-show-btn:hover {
      background: #f1f5f9;
      border-color: #cbd5e1;
      color: #0f172a;
    }

    #autoModal .lz-popup-dont-show-btn:active {
      transform: scale(0.97);
    }

    /* ============================================================
                 RESPONSIVE BREAKPOINTS
                 ============================================================ */
    @media (max-width: 576px) {
      #autoModal.lz-popup-modal {
        padding: 10px;
      }

      #autoModal .lz-popup-dialog {
        max-width: 100%;
      }

      #autoModal .lz-popup-card {
        border-radius: 16px;
      }

      #autoModal .lz-popup-header {
        padding: 12px 14px;
      }

      #autoModal .lz-popup-title {
        font-size: 13.5px;
        max-width: 250px;
      }

      #autoModal .lz-popup-body {
        min-height: 180px;
        max-height: min(58vh, 380px);
      }

      #autoModal .lz-popup-img {
        max-height: min(58vh, 380px);
      }

      #autoModal .lz-popup-nav {
        width: 32px;
        height: 32px;
      }

      #autoModal .lz-prev-btn {
        left: 8px;
      }

      #autoModal .lz-next-btn {
        right: 8px;
      }

      #autoModal .lz-popup-footer {
        padding: 10px 14px;
        flex-direction: column-reverse;
        gap: 8px;
        align-items: stretch;
      }

      #autoModal .lz-popup-dont-show-btn {
        width: 100%;
        justify-content: center;
        font-size: 11px;
        padding: 8px 12px;
      }

      #autoModal .lz-popup-counter {
        align-self: center;
      }
    }
  </style>

  <script>
    (function () {
      const modalPopup = document.getElementById('autoModal');
      const closeBtn = document.getElementById('closeBtn');
      const dontShowBtn = document.getElementById('dontShowBtn');
      const bannerTitle = document.getElementById('bannerTitle');
      const bannerImage = document.getElementById('bannerImage');
      const bannerLink = document.getElementById('bannerLink');
      const prevBtnPopup = document.getElementById('prevBtnPopup');
      const nextBtnPopup = document.getElementById('nextBtnPopup');
      const popupDotsWrap = document.getElementById('popupDotsWrap');
      const popupCounterWrap = document.getElementById('popupCounterWrap');
      const popupCurrentNum = document.getElementById('popupCurrentNum');
      const popupTotalNum = document.getElementById('popupTotalNum');
      const bannerSkeleton = document.getElementById('bannerSkeleton');
      const bannerCaptionWrap = document.getElementById('bannerCaptionWrap');
      const bannerCaption = document.getElementById('bannerCaption');

      /* DATA dari Laravel */
      const rawBanners = @json($banners);
      const banners = Array.isArray(rawBanners) ? rawBanners : (rawBanners ? [rawBanners] : []);

      if (!banners.length) return;

      let currentIndex = 0;
      let startXPopup = 0;
      let startYPopup = 0;
      let autoSlideTimer = null;

      /* SETUP PAGINASI & KONTROL */
      popupTotalNum.textContent = banners.length;

      if (banners.length <= 1) {
        prevBtnPopup.style.display = 'none';
        nextBtnPopup.style.display = 'none';
        popupDotsWrap.style.display = 'none';
        popupCounterWrap.style.display = 'none';
      } else {
        popupDotsWrap.innerHTML = '';
        banners.forEach((_, idx) => {
          const dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'lz-popup-dot' + (idx === 0 ? ' active' : '');
          dot.setAttribute('aria-label', 'Lihat banner ' + (idx + 1));
          dot.onclick = () => {
            if (currentIndex !== idx) {
              currentIndex = idx;
              showBanner(currentIndex);
            }
          };
          popupDotsWrap.appendChild(dot);
        });
      }

      /* TAMPILKAN BANNER */
      function showBanner(index) {
        bannerImage.classList.remove('lz-show');
        if (bannerSkeleton) bannerSkeleton.classList.add('active');

        setTimeout(() => {
          const b = banners[index];
          bannerTitle.textContent = b.name || 'Pengumuman';

          // Link & Target
          if (b.link && b.link !== '#' && b.link.trim() !== '') {
            bannerLink.href = b.link;
            bannerLink.target = '_blank';
            bannerLink.style.cursor = 'pointer';
          } else {
            bannerLink.href = 'javascript:void(0);';
            bannerLink.removeAttribute('target');
            bannerLink.style.cursor = 'default';
          }

          // Deskripsi (jika ada)
          if (b.description && b.description.trim() !== '') {
            bannerCaption.textContent = b.description;
            bannerCaptionWrap.style.display = 'block';
          } else {
            bannerCaptionWrap.style.display = 'none';
          }

          // Preload & Display Image
          const imgLoader = new Image();
          imgLoader.onload = () => {
            bannerImage.src = b.image;
            bannerImage.alt = b.description || b.name || 'Banner';
            if (bannerSkeleton) bannerSkeleton.classList.remove('active');
            bannerImage.classList.add('lz-show');
          };
          imgLoader.onerror = () => {
            bannerImage.src = b.image;
            if (bannerSkeleton) bannerSkeleton.classList.remove('active');
            bannerImage.classList.add('lz-show');
          };
          imgLoader.src = b.image;

          // Update Counter & Dots
          popupCurrentNum.textContent = index + 1;
          const dots = popupDotsWrap.querySelectorAll('.lz-popup-dot');
          dots.forEach((d, i) => {
            d.classList.toggle('active', i === index);
          });
        }, 220);
      }

      /* NAVIGASI */
      function nextBanner() {
        currentIndex = (currentIndex + 1) % banners.length;
        showBanner(currentIndex);
      }

      function prevBanner() {
        currentIndex = (currentIndex - 1 + banners.length) % banners.length;
        showBanner(currentIndex);
      }

      nextBtnPopup.onclick = nextBanner;
      prevBtnPopup.onclick = prevBanner;

      /* GESTUR SWIPE PADA MOBILE */
      bannerImage.addEventListener('touchstart', e => {
        startXPopup = e.touches[0].clientX;
        startYPopup = e.touches[0].clientY;
      }, { passive: true });

      bannerImage.addEventListener('touchend', e => {
        const diffX = startXPopup - e.changedTouches[0].clientX;
        const diffY = startYPopup - e.changedTouches[0].clientY;

        // Cek swipe horizontal lebih dominan daripada vertikal
        if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY) && banners.length > 1) {
          diffX > 0 ? nextBanner() : prevBanner();
        }
      }, { passive: true });

      /* TUTUP MODAL */
      function closeModal(setDontShow = false) {
        if (setDontShow) {
          try {
            const time = Date.now() + (10 * 60 * 1000); // 10 menit
            localStorage.setItem('dontShowModalUntil', time);
          } catch (e) { }
        }
        if (autoSlideTimer) {
          clearInterval(autoSlideTimer);
          autoSlideTimer = null;
        }
        modalPopup.classList.remove('show');
        modalPopup.setAttribute('aria-hidden', 'true');
      }

      closeBtn.onclick = () => closeModal(false);
      dontShowBtn.onclick = () => closeModal(true);

      modalPopup.addEventListener('click', e => {
        if (e.target === modalPopup) closeModal(false);
      });

      document.addEventListener('keydown', e => {
        if (!modalPopup.classList.contains('show')) return;
        if (e.key === 'Escape') {
          closeModal(false);
        } else if (banners.length > 1) {
          if (e.key === 'ArrowRight') nextBanner();
          if (e.key === 'ArrowLeft') prevBanner();
        }
      });

      /* AUTO SLIDE (HANYA JIKA > 1 BANNER) */
      function startAutoSlide() {
        if (banners.length > 1 && !autoSlideTimer) {
          autoSlideTimer = setInterval(nextBanner, 6500);
        }
      }

      function stopAutoSlide() {
        if (autoSlideTimer) {
          clearInterval(autoSlideTimer);
          autoSlideTimer = null;
        }
      }

      modalPopup.addEventListener('mouseenter', stopAutoSlide);
      modalPopup.addEventListener('mouseleave', startAutoSlide);
      modalPopup.addEventListener('touchstart', stopAutoSlide, { passive: true });

      /* BUKA MODAL SAAT LOAD */
      function initModal() {
        let expire = null;
        try {
          expire = localStorage.getItem('dontShowModalUntil');
        } catch (e) { }

        if (!expire || Date.now() > Number(expire)) {
          setTimeout(() => {
            modalPopup.classList.add('show');
            modalPopup.setAttribute('aria-hidden', 'false');
            showBanner(currentIndex);
            startAutoSlide();
          }, 800);
        }
      }

      if (document.readyState === 'complete') {
        initModal();
      } else {
        window.addEventListener('load', initModal);
      }
    })();
  </script>
@endif