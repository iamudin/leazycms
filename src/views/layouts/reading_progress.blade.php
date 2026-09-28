<!-- Reading Progress Bar -->
<div id="readingProgressContainer" class="reading-progress-container" aria-hidden="true">
    <div id="readingProgressBar" class="reading-progress-bar"></div>
</div>

<style>
.reading-progress-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 1.5px;
    background: transparent;
    z-index: 99999999;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.reading-progress-container.is-active {
    opacity: 1;
}
.reading-progress-bar {
    height: 100%;
    width: 0%;
    background: var(--reading-progress-color, linear-gradient(90deg, #2563eb 0%, #3b82f6 50%, #06b6d4 100%));
    box-shadow: 0 0 10px rgba(37, 99, 235, 0.7), 0 0 4px rgba(6, 182, 212, 0.5);
    border-radius: 0 2px 2px 0;
    transition: width 0.1s cubic-bezier(0.1, 0.9, 0.2, 1), background 0.3s ease, box-shadow 0.3s ease;
}
@keyframes progressGlow {
    0%, 100% { box-shadow: 0 0 10px rgba(16, 185, 129, 0.8), 0 0 4px rgba(5, 150, 105, 0.6); }
    50% { box-shadow: 0 0 18px rgba(16, 185, 129, 1), 0 0 8px rgba(5, 150, 105, 0.8); }
}
.reading-progress-bar.is-finished {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%) !important;
    animation: progressGlow 1.5s infinite ease-in-out;
}
</style>

<script>
(function () {
    if (window.__readingProgressInitialized) return;
    window.__readingProgressInitialized = true;

    function initReadingProgressBar() {
        const container = document.getElementById('readingProgressContainer');
        const bar = document.getElementById('readingProgressBar');
        if (!container || !bar) return;

        let ticking = false;

        function updateProgress() {
            const target = document.querySelector('.summernote-content');
            if (!target) {
                container.classList.remove('is-active');
                bar.style.width = '0%';
                bar.classList.remove('is-finished');
                return;
            }

            const rect = target.getBoundingClientRect();
            const windowHeight = window.innerHeight || document.documentElement.clientHeight;

            // Deteksi header sticky/fixed jika ada
            let headerOffset = 0;
            const header = document.querySelector('header.sticky, header.fixed, nav.sticky, nav.fixed, #main-navbar');
            if (header) {
                const headerRect = header.getBoundingClientRect();
                headerOffset = Math.max(0, headerRect.bottom);
            }

            // Posisi awal pembacaan (.summernote-content) relatif terhadap batas baca atas
            const startPoint = rect.top - headerOffset;

            // Sebelum mencapai .summernote-content: sembunyikan progress bar (0%)
            if (startPoint > 0) {
                container.classList.remove('is-active');
                bar.style.width = '0%';
                bar.classList.remove('is-finished');
                return;
            }

            // Tampilkan progress bar saat mulai memasuki .summernote-content
            container.classList.add('is-active');

            const targetHeight = target.offsetHeight;
            const availableHeight = Math.max(1, windowHeight - headerOffset);

            // Total jarak scroll dari awal div sampai selesai div penutupnya:
            // Konten panjang: selesai saat dasar div penutup mencapai dasar viewport
            // Konten pendek: selesai saat div penutup telah terlewati
            let totalScrollable = targetHeight > availableHeight
                ? (targetHeight - availableHeight)
                : targetHeight;

            if (totalScrollable <= 0) totalScrollable = 1;

            const scrolled = Math.max(0, -startPoint);
            let percentage = (scrolled / totalScrollable) * 100;

            // Jika posisi scroll telah mencapai bagian paling bawah halaman
            const doc = document.documentElement;
            const body = document.body;
            const scrollTop = window.pageYOffset || doc.scrollTop || (body ? body.scrollTop : 0) || 0;
            const scrollHeight = Math.max(
                body ? body.scrollHeight : 0, doc ? doc.scrollHeight : 0,
                body ? body.offsetHeight : 0, doc ? doc.offsetHeight : 0
            );
            if (scrollTop + windowHeight >= scrollHeight - 3) {
                percentage = 100;
            }

            percentage = Math.min(100, Math.max(0, percentage));
            bar.style.width = percentage + '%';

            if (percentage >= 99) {
                bar.classList.add('is-finished');
            } else {
                bar.classList.remove('is-finished');
            }
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateProgress();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        window.addEventListener('resize', updateProgress, { passive: true });
        window.addEventListener('load', updateProgress);
        document.addEventListener('lazyloaded', updateProgress);
        updateProgress();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReadingProgressBar);
    } else {
        initReadingProgressBar();
    }
})();
</script>
