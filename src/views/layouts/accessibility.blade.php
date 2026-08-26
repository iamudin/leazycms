@if(get_option('accessibility_widget') && get_option('accessibility_widget') == 'Y')
<!-- Widget Aksesibilitas Ramah Disabilitas & Pembaca Layar Bahasa Indonesia -->
<div id="lzA11yApp">
    <!-- Floating Trigger Button (Kiri Bawah) -->
    <button id="lzA11yTrigger" class="lz-a11y-trigger" aria-label="Buka Menu Aksesibilitas" title="Menu Aksesibilitas & Pembaca Suara">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26" fill="currentColor">
            <circle cx="12" cy="4" r="2"/>
            <path d="M19 13v-2c-1.54.02-3.09-.75-4.07-1.83l-1.29-1.43c-.17-.19-.38-.34-.61-.45-.49-.23-1.07-.2-1.54.09l-3.95 2.45c-.48.3-.77.82-.77 1.39v4.78c0 .55.45 1 1 1s1-.45 1-1v-4.09l2.23-1.38v8.69c0 .55.45 1 1 1s1-.45 1-1v-5.2l1.62-1.01c.78.89 1.93 1.51 3.38 1.58v2.01c0 .55.45 1 1 1s1-.45 1-1z"/>
        </svg>
        <span class="lz-a11y-badge" id="lzA11yBadge" style="display:none;">0</span>
    </button>

    <!-- Overlay Backdrop -->
    <div id="lzA11yBackdrop" class="lz-a11y-backdrop"></div>

    <!-- Panel Modal Aksesibilitas -->
    <div id="lzA11yPanel" class="lz-a11y-panel" role="dialog" aria-modal="true" aria-labelledby="lzA11yTitle">
        <!-- Header -->
        <div class="lz-a11y-header">
            <div class="lz-a11y-title-wrap">
                <div class="lz-a11y-icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <circle cx="12" cy="4" r="2"/>
                        <path d="M19 13v-2c-1.54.02-3.09-.75-4.07-1.83l-1.29-1.43c-.17-.19-.38-.34-.61-.45-.49-.23-1.07-.2-1.54.09l-3.95 2.45c-.48.3-.77.82-.77 1.39v4.78c0 .55.45 1 1 1s1-.45 1-1v-4.09l2.23-1.38v8.69c0 .55.45 1 1 1s1-.45 1-1v-5.2l1.62-1.01c.78.89 1.93 1.51 3.38 1.58v2.01c0 .55.45 1 1 1s1-.45 1-1z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="lzA11yTitle" class="lz-a11y-title">Pusat Aksesibilitas</h2>
                    <p class="lz-a11y-subtitle">Fitur bantuan tunanetra, tunarungu & kognitif</p>
                </div>
            </div>
            <div class="lz-a11y-header-actions">
                <button id="lzA11yReset" class="lz-a11y-reset-btn" title="Reset Semua Pengaturan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset
                </button>
                <button id="lzA11yClose" class="lz-a11y-close-btn" aria-label="Tutup Panel">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="lz-a11y-body">
            <!-- SECTION 1: PEMBACA SUARA (TEXT TO SPEECH BAHASA INDONESIA) -->
            <div class="lz-a11y-section">
                <div class="lz-a11y-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                    <span>Pembaca Suara (Teks ke Suara Indonesia)</span>
                </div>
                
                <div class="lz-a11y-tts-box">
                    <div class="lz-a11y-tts-actions">
                        <button id="lzA11yTtsPlay" class="lz-a11y-btn-primary" title="Mulai membaca berita/artikel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            <span>Baca Berita / Halaman</span>
                        </button>
                        <button id="lzA11yTtsPause" class="lz-a11y-btn-secondary" style="display:none;" title="Jeda suara">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                            <span>Jeda</span>
                        </button>
                        <button id="lzA11yTtsStop" class="lz-a11y-btn-danger" style="display:none;" title="Hentikan suara">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="4" width="16" height="16"/></svg>
                            <span>Stop</span>
                        </button>
                    </div>

                    <!-- TTS Mode Baca Seleksi Teks -->
                    <div class="lz-a11y-toggle-row" style="margin-top: 10px;">
                        <label class="lz-a11y-toggle-label" for="lzA11yReadSelection">
                            <span class="lz-a11y-label-title">Baca Teks yang Diseleksi</span>
                            <span class="lz-a11y-label-desc">Blok / sorot kalimat apa saja untuk mendengarkannya</span>
                        </label>
                        <input type="checkbox" id="lzA11yReadSelection" class="lz-a11y-checkbox">
                    </div>

                    <!-- Speed Control -->
                    <div class="lz-a11y-speed-control">
                        <span class="lz-a11y-speed-label">Kecepatan Suara:</span>
                        <div class="lz-a11y-speed-btns">
                            <button class="lz-a11y-speed-btn" data-speed="0.8">0.8x</button>
                            <button class="lz-a11y-speed-btn active" data-speed="1.0">1.0x</button>
                            <button class="lz-a11y-speed-btn" data-speed="1.2">1.2x</button>
                            <button class="lz-a11y-speed-btn" data-speed="1.5">1.5x</button>
                        </div>
                    </div>

                    <!-- TTS Live Status Indicator -->
                    <div id="lzA11yTtsStatus" class="lz-a11y-tts-status" style="display:none;">
                        <span class="lz-a11y-pulse-dot"></span>
                        <span id="lzA11yTtsStatusText">Sedang membaca teks...</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: PENGLIHATAN & KONTRAST (TUNANETRA & LOW VISION) -->
            <div class="lz-a11y-section">
                <div class="lz-a11y-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/></svg>
                    <span>Penglihatan & Teks (Low Vision / Tunanetra)</span>
                </div>

                <div class="lz-a11y-grid">
                    <!-- Ukuran Teks -->
                    <div class="lz-a11y-card">
                        <span class="lz-a11y-card-title">Ukuran Teks</span>
                        <div class="lz-a11y-btn-group">
                            <button id="lzA11yFontDec" class="lz-a11y-tool-btn" title="Kecilkan Teks">A-</button>
                            <button id="lzA11yFontReset" class="lz-a11y-tool-btn" title="Ukuran Normal">100%</button>
                            <button id="lzA11yFontInc" class="lz-a11y-tool-btn" title="Besarkan Teks">A+</button>
                        </div>
                    </div>

                    <!-- Spasi Teks -->
                    <button class="lz-a11y-feature-btn" data-feature="text-spacing">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 6H3"/><path d="M21 12H3"/><path d="M21 18H3"/><path d="m6 9-3 3 3 3"/><path d="m18 9 3 3-3 3"/></svg>
                        </div>
                        <span>Jarak Teks Luas</span>
                    </button>

                    <!-- Font Disleksia -->
                    <button class="lz-a11y-feature-btn" data-feature="dyslexia">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
                        </div>
                        <span>Font Ramah Disleksia</span>
                    </button>

                    <!-- Kontras Tinggi -->
                    <button class="lz-a11y-feature-btn" data-feature="high-contrast">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20Z"/></svg>
                        </div>
                        <span>Kontras Tinggi</span>
                    </button>

                    <!-- Mode Monokrom (Hitam Putih) -->
                    <button class="lz-a11y-feature-btn" data-feature="grayscale">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="2" x2="12" y2="22"/></svg>
                        </div>
                        <span>Monokrom / Abu</span>
                    </button>

                    <!-- Mode Invert (Warna Terbalik) -->
                    <button class="lz-a11y-feature-btn" data-feature="invert">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 15 6 6m-6-6v4.8m0-4.8h4.8"/><path d="M9 19.8V15m0 0H4.2m4.8 0L3 21"/><path d="M15 4.2V9m0 0h4.8m-4.8 0 6-6"/><path d="M9 4.2V9m0 0H4.2m4.8 0L3 3"/></svg>
                        </div>
                        <span>Balikkan Warna</span>
                    </button>

                    <!-- Kursor Besar -->
                    <button class="lz-a11y-feature-btn" data-feature="big-cursor">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="m13 13 6 6"/></svg>
                        </div>
                        <span>Kursor Besar</span>
                    </button>

                    <!-- Sorot Tautan -->
                    <button class="lz-a11y-feature-btn" data-feature="highlight-links">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <span>Sorot Semua Link</span>
                    </button>

                    <!-- Garis Penuntun Baca (Reading Ruler) -->
                    <button class="lz-a11y-feature-btn" data-feature="reading-ruler">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="2" y1="12" x2="22" y2="12"/><line x1="6" y1="8" x2="6" y2="16"/><line x1="18" y1="8" x2="18" y2="16"/></svg>
                        </div>
                        <span>Garis Panduan Baca</span>
                    </button>

                    <!-- Masker Fokus Baca (Reading Mask) -->
                    <button class="lz-a11y-feature-btn" data-feature="reading-mask">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>
                        </div>
                        <span>Masker Fokus Baca</span>
                    </button>
                </div>
            </div>

            <!-- SECTION 3: KOGNITIF, MOTORIK & TUNARUNGU -->
            <div class="lz-a11y-section">
                <div class="lz-a11y-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Kognitif, Motorik & Tunarungu</span>
                </div>

                <div class="lz-a11y-grid">
                    <!-- Hentikan Animasi -->
                    <button class="lz-a11y-feature-btn" data-feature="stop-anim">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        </div>
                        <span>Hentikan Animasi</span>
                    </button>

                    <!-- Sembunyikan Gambar -->
                    <button class="lz-a11y-feature-btn" data-feature="hide-images">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="3" y1="3" x2="21" y2="21"/><circle cx="9" cy="9" r="2"/></svg>
                        </div>
                        <span>Sembunyikan Gambar</span>
                    </button>

                    <!-- Navigasi Struktur Judul (Headings) -->
                    <button id="lzA11yHeadingsBtn" class="lz-a11y-feature-btn">
                        <div class="lz-a11y-btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        </div>
                        <span>Daftar Judul Artikel</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reading Ruler Element -->
    <div id="lzA11yRuler" class="lz-a11y-ruler" style="display:none;"></div>

    <!-- Reading Mask Elements -->
    <div id="lzA11yMaskTop" class="lz-a11y-mask lz-a11y-mask-top" style="display:none;"></div>
    <div id="lzA11yMaskBottom" class="lz-a11y-mask lz-a11y-mask-bottom" style="display:none;"></div>

    <!-- Modal Struktur Headings Navigasi -->
    <div id="lzA11yHeadingsModal" class="lz-a11y-headings-modal" style="display:none;">
        <div class="lz-a11y-headings-content">
            <div class="lz-a11y-headings-header">
                <h3>Navigasi Judul Halaman</h3>
                <button id="lzA11yHeadingsClose">&times;</button>
            </div>
            <div id="lzA11yHeadingsList" class="lz-a11y-headings-list"></div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   1. ACCESSIBILITY TRIGGER & PANEL STYLES
   ========================================================================== */
#lzA11yApp {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1f2937;
    position: relative;
    z-index: 99999;
}

/* Floating Button (Kiri Bawah) */
.lz-a11y-trigger {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 99999;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #1d4ed8;
    color: #ffffff;
    border: 2px solid #ffffff;
    box-shadow: 0 4px 16px rgba(29, 78, 216, 0.4), 0 2px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    padding: 0;
}

.lz-a11y-trigger:hover {
    background: #1e40af;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 24px rgba(29, 78, 216, 0.5);
}

.lz-a11y-trigger:active {
    transform: translateY(-1px) scale(0.96);
}

.lz-a11y-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    background: #ef4444;
    color: #ffffff;
    font-size: 10px;
    font-weight: bold;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
}

/* Backdrop */
.lz-a11y-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(2px);
    z-index: 99998;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.lz-a11y-backdrop.active {
    opacity: 1;
    visibility: visible;
}

/* Panel Modal */
.lz-a11y-panel {
    position: fixed;
    bottom: 80px;
    left: 20px;
    width: 380px;
    max-width: calc(100vw - 40px);
    max-height: calc(100vh - 110px);
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
    z-index: 99999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.lz-a11y-panel.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

/* Header */
.lz-a11y-header {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.lz-a11y-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.lz-a11y-icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #dbeafe;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lz-a11y-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.lz-a11y-subtitle {
    margin: 2px 0 0 0;
    font-size: 12px;
    color: #64748b;
    line-height: 1.2;
}

.lz-a11y-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lz-a11y-reset-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.lz-a11y-reset-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.lz-a11y-close-btn {
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #94a3b8;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.lz-a11y-close-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

/* Body */
.lz-a11y-body {
    padding: 16px 20px;
    overflow-y: auto;
    max-height: calc(100vh - 200px);
}

.lz-a11y-section {
    margin-bottom: 20px;
}

.lz-a11y-section-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

/* TTS Box */
.lz-a11y-tts-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px;
}

.lz-a11y-tts-actions {
    display: flex;
    gap: 8px;
}

.lz-a11y-btn-primary, .lz-a11y-btn-secondary, .lz-a11y-btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    flex: 1;
}

.lz-a11y-btn-primary {
    background: #2563eb;
    color: #ffffff;
}

.lz-a11y-btn-primary:hover {
    background: #1d4ed8;
}

.lz-a11y-btn-secondary {
    background: #e2e8f0;
    color: #1e293b;
}

.lz-a11y-btn-secondary:hover {
    background: #cbd5e1;
}

.lz-a11y-btn-danger {
    background: #fee2e2;
    color: #dc2626;
}

.lz-a11y-btn-danger:hover {
    background: #fecaca;
}

.lz-a11y-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-top: 1px solid #f1f5f9;
}

.lz-a11y-toggle-label {
    cursor: pointer;
    display: flex;
    flex-direction: column;
}

.lz-a11y-label-title {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
}

.lz-a11y-label-desc {
    font-size: 11px;
    color: #64748b;
}

.lz-a11y-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #2563eb;
    cursor: pointer;
}

.lz-a11y-speed-control {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
}

.lz-a11y-speed-label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
}

.lz-a11y-speed-btns {
    display: flex;
    gap: 4px;
}

.lz-a11y-speed-btn {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
}

.lz-a11y-speed-btn.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}

.lz-a11y-tts-status {
    margin-top: 10px;
    padding: 8px 12px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    font-size: 12px;
    color: #1d4ed8;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.lz-a11y-pulse-dot {
    width: 8px;
    height: 8px;
    background: #2563eb;
    border-radius: 50%;
    animation: waPulse 1.5s infinite;
}

/* Feature Grid */
.lz-a11y-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.lz-a11y-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.lz-a11y-card-title {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.lz-a11y-btn-group {
    display: flex;
    gap: 4px;
}

.lz-a11y-tool-btn {
    flex: 1;
    height: 32px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.15s ease;
}

.lz-a11y-tool-btn:hover {
    background: #e2e8f0;
}

.lz-a11y-feature-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
}

.lz-a11y-feature-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.lz-a11y-feature-btn.active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
    box-shadow: inset 0 0 0 1px #2563eb;
}

.lz-a11y-feature-btn .lz-a11y-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
}

.lz-a11y-feature-btn.active .lz-a11y-btn-icon {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}

.lz-a11y-feature-btn span {
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
}

/* Reading Ruler */
.lz-a11y-ruler {
    position: fixed;
    left: 0;
    width: 100vw;
    height: 32px;
    background: rgba(255, 230, 0, 0.35);
    border-top: 2px solid #eab308;
    border-bottom: 2px solid #eab308;
    pointer-events: none;
    z-index: 99990;
    transform: translateY(-50%);
}

/* Reading Mask */
.lz-a11y-mask {
    position: fixed;
    left: 0;
    width: 100vw;
    background: rgba(0, 0, 0, 0.7);
    pointer-events: none;
    z-index: 99990;
}

/* Headings Modal */
.lz-a11y-headings-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 440px;
    max-width: 90vw;
    max-height: 80vh;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    z-index: 100000;
    overflow: hidden;
}

.lz-a11y-headings-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.lz-a11y-headings-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.lz-a11y-headings-header button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #94a3b8;
}

.lz-a11y-headings-list {
    padding: 16px 20px;
    overflow-y: auto;
    max-height: 60vh;
}

.lz-a11y-heading-item {
    display: block;
    padding: 8px 12px;
    margin-bottom: 6px;
    border-radius: 8px;
    color: #1e293b;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    background: #f8fafc;
    border-left: 3px solid #2563eb;
    transition: all 0.15s ease;
}

.lz-a11y-heading-item:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

/* Highlight sentence being read */
.lz-a11y-tts-reading-highlight {
    background-color: #fef08a !important;
    color: #000000 !important;
    border-radius: 4px;
    outline: 2px solid #eab308;
    transition: all 0.2s ease;
}

/* ==========================================================================
   2. GLOBAL ACCESSIBILITY CLASSES APPLIED TO BODY/HTML
   ========================================================================== */
/* Kontras Tinggi (High Contrast) */
html.lz-a11y-high-contrast body {
    background-color: #000000 !important;
    color: #ffffff !important;
}
html.lz-a11y-high-contrast a {
    color: #fde047 !important;
    text-decoration: underline !important;
}
html.lz-a11y-high-contrast * {
    border-color: #475569 !important;
}

/* Monokrom / Grayscale */
html.lz-a11y-grayscale {
    filter: grayscale(100%) !important;
}

/* Invert Colors */
html.lz-a11y-invert {
    filter: invert(100%) hue-rotate(180deg) !important;
}
html.lz-a11y-invert img, html.lz-a11y-invert video, html.lz-a11y-invert #lzA11yApp {
    filter: invert(100%) hue-rotate(180deg) !important;
}

/* Font Ramah Disleksia */
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&display=swap');
html.lz-a11y-dyslexia * {
    font-family: 'Lexend', 'Comic Sans MS', sans-serif !important;
    letter-spacing: 0.05em !important;
}

/* Spasi Teks Luas */
html.lz-a11y-text-spacing * {
    letter-spacing: 0.12em !important;
    word-spacing: 0.16em !important;
    line-height: 1.9 !important;
}

/* Sorot Semua Link */
html.lz-a11y-highlight-links a {
    background-color: #fef08a !important;
    color: #000000 !important;
    text-decoration: underline !important;
    font-weight: 700 !important;
    outline: 2px solid #eab308 !important;
}

/* Kursor Besar */
html.lz-a11y-big-cursor, html.lz-a11y-big-cursor * {
    cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='36' height='36' viewBox='0 0 24 24' fill='%23000000' stroke='%23ffffff' stroke-width='1.5'%3E%3Cpath d='M3 3l7 17 2.5-7.5L20 10 3 3z'/%3E%3C/svg%3E"), auto !important;
}

/* Hentikan Animasi */
html.lz-a11y-stop-anim * {
    animation: none !important;
    transition: none !important;
}

/* Sembunyikan Gambar */
html.lz-a11y-hide-images img, 
html.lz-a11y-hide-images [style*="background-image"], 
html.lz-a11y-hide-images svg:not(#lzA11yApp svg) {
    visibility: hidden !important;
}

/* Responsive */
@media (max-width: 480px) {
    .lz-a11y-trigger {
        bottom: 15px;
        left: 15px;
        width: 42px;
        height: 42px;
    }
    .lz-a11y-panel {
        left: 10px;
        bottom: 65px;
        max-width: calc(100vw - 20px);
    }
}
</style>

<script>
(() => {
    // ---------------------------------------------------------
    // STATE MANAGEMENT & PERSISTENCE
    // ---------------------------------------------------------
    const STORAGE_KEY = 'lz_a11y_settings';
    let state = {
        fontSize: 0, // -2, -1, 0, 1, 2, 3
        features: {}, // featureName: boolean
        ttsSpeed: 1.0,
        readSelection: false
    };

    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            state = Object.assign(state, JSON.parse(saved));
        }
    } catch(e) {}

    // DOM Elements
    const trigger = document.getElementById('lzA11yTrigger');
    const panel = document.getElementById('lzA11yPanel');
    const backdrop = document.getElementById('lzA11yBackdrop');
    const closeBtn = document.getElementById('lzA11yClose');
    const resetBtn = document.getElementById('lzA11yReset');
    const badge = document.getElementById('lzA11yBadge');
    
    // TTS Elements
    const ttsPlayBtn = document.getElementById('lzA11yTtsPlay');
    const ttsPauseBtn = document.getElementById('lzA11yTtsPause');
    const ttsStopBtn = document.getElementById('lzA11yTtsStop');
    const ttsStatus = document.getElementById('lzA11yTtsStatus');
    const ttsStatusText = document.getElementById('lzA11yTtsStatusText');
    const readSelectionCheckbox = document.getElementById('lzA11yReadSelection');
    const speedBtns = document.querySelectorAll('.lz-a11y-speed-btn');

    // Tool Buttons
    const fontIncBtn = document.getElementById('lzA11yFontInc');
    const fontDecBtn = document.getElementById('lzA11yFontDec');
    const fontResetBtn = document.getElementById('lzA11yFontReset');
    const featureBtns = document.querySelectorAll('.lz-a11y-feature-btn[data-feature]');

    // Special Ruler & Mask Elements
    const ruler = document.getElementById('lzA11yRuler');
    const maskTop = document.getElementById('lzA11yMaskTop');
    const maskBottom = document.getElementById('lzA11yMaskBottom');

    // Headings Modal
    const headingsBtn = document.getElementById('lzA11yHeadingsBtn');
    const headingsModal = document.getElementById('lzA11yHeadingsModal');
    const headingsList = document.getElementById('lzA11yHeadingsList');
    const headingsClose = document.getElementById('lzA11yHeadingsClose');

    // ---------------------------------------------------------
    // TOGGLE PANEL
    // ---------------------------------------------------------
    function openPanel() {
        panel.classList.add('active');
        backdrop.classList.add('active');
    }

    function closePanel() {
        panel.classList.remove('active');
        backdrop.classList.remove('active');
    }

    trigger.addEventListener('click', () => {
        panel.classList.contains('active') ? closePanel() : openPanel();
    });

    closeBtn.addEventListener('click', closePanel);
    backdrop.addEventListener('click', closePanel);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePanel();
            if (headingsModal) headingsModal.style.display = 'none';
        }
    });

    // ---------------------------------------------------------
    // APPLY & SYNC SETTINGS
    // ---------------------------------------------------------
    function saveState() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch(e) {}
        updateBadge();
    }

    function updateBadge() {
        let activeCount = 0;
        if (state.fontSize !== 0) activeCount++;
        if (state.readSelection) activeCount++;
        for (let k in state.features) {
            if (state.features[k]) activeCount++;
        }
        if (activeCount > 0) {
            badge.style.display = 'flex';
            badge.textContent = activeCount;
        } else {
            badge.style.display = 'none';
        }
    }

    function applyFontSize() {
        const root = document.documentElement;
        if (state.fontSize === 0) {
            root.style.fontSize = '';
            fontResetBtn.textContent = '100%';
        } else {
            const scales = { '-2': '80%', '-1': '90%', '1': '115%', '2': '130%', '3': '145%' };
            root.style.fontSize = scales[state.fontSize] || '100%';
            fontResetBtn.textContent = scales[state.fontSize] || '100%';
        }
    }

    function applyFeatures() {
        const root = document.documentElement;
        featureBtns.forEach(btn => {
            const feat = btn.getAttribute('data-feature');
            const isActive = !!state.features[feat];
            btn.classList.toggle('active', isActive);

            if (feat === 'reading-ruler') {
                ruler.style.display = isActive ? 'block' : 'none';
            } else if (feat === 'reading-mask') {
                maskTop.style.display = isActive ? 'block' : 'none';
                maskBottom.style.display = isActive ? 'block' : 'none';
            } else {
                root.classList.toggle('lz-a11y-' + feat, isActive);
            }
        });

        // TTS Speed UI
        speedBtns.forEach(b => {
            const sp = parseFloat(b.getAttribute('data-speed'));
            b.classList.toggle('active', sp === state.ttsSpeed);
        });

        readSelectionCheckbox.checked = !!state.readSelection;
        root.classList.toggle('lz-a11y-read-selection-active', !!state.readSelection);
        applyFontSize();
        updateBadge();
    }

    // Font Controls
    fontIncBtn.addEventListener('click', () => {
        if (state.fontSize < 3) {
            state.fontSize++;
            applyFontSize();
            saveState();
        }
    });

    fontDecBtn.addEventListener('click', () => {
        if (state.fontSize > -2) {
            state.fontSize--;
            applyFontSize();
            saveState();
        }
    });

    fontResetBtn.addEventListener('click', () => {
        state.fontSize = 0;
        applyFontSize();
        saveState();
    });

    // Feature Toggles
    featureBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const feat = btn.getAttribute('data-feature');
            state.features[feat] = !state.features[feat];
            applyFeatures();
            saveState();
        });
    });

    // Speed Controls
    speedBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            state.ttsSpeed = parseFloat(btn.getAttribute('data-speed')) || 1.0;
            applyFeatures();
            saveState();
        });
    });

    // Selection Read Toggle
    readSelectionCheckbox.addEventListener('change', () => {
        state.readSelection = readSelectionCheckbox.checked;
        applyFeatures();
        saveState();
    });

    // Reset All
    resetBtn.addEventListener('click', () => {
        stopSpeech();
        state = {
            fontSize: 0,
            features: {},
            ttsSpeed: 1.0,
            readSelection: false
        };
        applyFeatures();
        saveState();
    });

    // ---------------------------------------------------------
    // READING RULER & MASK MOUSE TRACKER
    // ---------------------------------------------------------
    window.addEventListener('mousemove', (e) => {
        const y = e.clientY;
        if (state.features['reading-ruler']) {
            ruler.style.top = y + 'px';
        }
        if (state.features['reading-mask']) {
            const maskHeight = 60; // reading slit height
            maskTop.style.top = '0px';
            maskTop.style.height = Math.max(0, y - maskHeight / 2) + 'px';
            maskBottom.style.top = (y + maskHeight / 2) + 'px';
            maskBottom.style.height = (window.innerHeight - (y + maskHeight / 2)) + 'px';
        }
    }, { passive: true });

    // ---------------------------------------------------------
    // HEADINGS NAVIGATOR
    // ---------------------------------------------------------
    headingsBtn.addEventListener('click', () => {
        const headings = document.querySelectorAll('h1, h2, h3');
        headingsList.innerHTML = '';
        if (headings.length === 0) {
            headingsList.innerHTML = '<p style="color:#64748b;font-size:13px;">Tidak ditemukan judul di halaman ini.</p>';
        } else {
            headings.forEach((h, idx) => {
                const text = h.innerText.trim();
                if (text) {
                    const tag = h.tagName.toUpperCase();
                    const a = document.createElement('a');
                    a.className = 'lz-a11y-heading-item';
                    a.href = 'javascript:void(0)';
                    a.innerHTML = `<strong style="color:#2563eb;margin-right:6px;">[${tag}]</strong> ${text}`;
                    a.addEventListener('click', () => {
                        headingsModal.style.display = 'none';
                        closePanel();
                        h.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        h.style.outline = '3px solid #2563eb';
                        setTimeout(() => { h.style.outline = ''; }, 2000);
                    });
                    headingsList.appendChild(a);
                }
            });
        }
        headingsModal.style.display = 'block';
    });

    headingsClose.addEventListener('click', () => {
        headingsModal.style.display = 'none';
    });

    // ---------------------------------------------------------
    // TEXT TO SPEECH (SMART INDONESIAN DUAL-ENGINE)
    // ---------------------------------------------------------
    let synth = window.speechSynthesis;
    let ttsQueue = [];
    let isSpeaking = false;
    let isPaused = false;
    let currentHighlightEl = null;
    let currentAudio = null;
    let subSentenceQueue = [];

    // Deteksi apakah browser memiliki voice pack asli Bahasa Indonesia
    function getIndonesianVoice() {
        if (!synth) return null;
        const voices = synth.getVoices() || [];
        return voices.find(v => {
            const lang = (v.lang || '').toLowerCase();
            const name = (v.name || '').toLowerCase();
            return (lang === 'id-id' || lang === 'id_id' || lang === 'id' || name.includes('indonesia') || name.includes('gadis') || name.includes('andika') || name.includes('ardhi'));
        }) || null;
    }

    if (synth && synth.onvoiceschanged !== undefined) {
        synth.onvoiceschanged = () => {};
    }

    function removeHighlight() {
        if (currentHighlightEl) {
            currentHighlightEl.classList.remove('lz-a11y-tts-reading-highlight');
            currentHighlightEl = null;
        }
    }

    function stopSpeech() {
        if (synth) {
            try { synth.cancel(); } catch(e) {}
        }
        if (currentAudio) {
            try { currentAudio.pause(); } catch(e) {}
            currentAudio = null;
        }
        ttsQueue = [];
        subSentenceQueue = [];
        isSpeaking = false;
        isPaused = false;
        removeHighlight();
        ttsPlayBtn.style.display = 'inline-flex';
        ttsPauseBtn.style.display = 'none';
        ttsStopBtn.style.display = 'none';
        ttsStatus.style.display = 'none';
    }

    // Memecah teks panjang menjadi potongan kalimat (< 140 karakter)
    function splitIntoSubSentences(text) {
        const result = [];
        const raw = text.split(/(?<=[.!?,;\n])\s+/);
        raw.forEach(s => {
            s = s.trim();
            if (!s) return;
            if (s.length <= 140) {
                result.push(s);
            } else {
                const words = s.split(' ');
                let buf = '';
                words.forEach(w => {
                    if ((buf + ' ' + w).length <= 140) {
                        buf = buf ? buf + ' ' + w : w;
                    } else {
                        if (buf) result.push(buf);
                        buf = w;
                    }
                });
                if (buf) result.push(buf);
            }
        });
        return result;
    }

    // Putar kalimat menggunakan Audio Google TTS Indonesia via local backend endpoint
    function playAudioChunk(text, onDone) {
        if (!isSpeaking || isPaused) return;
        const clean = encodeURIComponent(text.trim());
        const url = `{{ url('lz-tts') }}?text=${clean}`;
        const audio = new Audio(url);
        audio.playbackRate = state.ttsSpeed || 1.0;
        currentAudio = audio;

        audio.onended = () => {
            currentAudio = null;
            if (onDone) onDone();
        };

        audio.onerror = () => {
            currentAudio = null;
            if (synth) {
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'id-ID';
                u.rate = state.ttsSpeed || 1.0;
                u.onend = onDone;
                u.onerror = onDone;
                synth.speak(u);
            } else if (onDone) {
                onDone();
            }
        };

        audio.play().catch(() => {
            if (synth) {
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'id-ID';
                u.rate = state.ttsSpeed || 1.0;
                u.onend = onDone;
                u.onerror = onDone;
                synth.speak(u);
            } else if (onDone) {
                onDone();
            }
        });
    }

    function processSubSentences(onParagraphFinished) {
        if (!isSpeaking || isPaused) return;
        if (subSentenceQueue.length === 0) {
            if (onParagraphFinished) onParagraphFinished();
            return;
        }

        const sentence = subSentenceQueue.shift();
        const hasNativeVoice = !!getIndonesianVoice();

        if (hasNativeVoice && synth) {
            const utterance = new SpeechSynthesisUtterance(sentence);
            utterance.lang = 'id-ID';
            utterance.rate = state.ttsSpeed || 1.0;
            const voice = getIndonesianVoice();
            if (voice) utterance.voice = voice;

            utterance.onend = () => {
                if (isSpeaking && !isPaused) {
                    processSubSentences(onParagraphFinished);
                }
            };
            utterance.onerror = () => {
                if (isSpeaking && !isPaused) {
                    processSubSentences(onParagraphFinished);
                }
            };
            synth.speak(utterance);
        } else {
            playAudioChunk(sentence, () => {
                if (isSpeaking && !isPaused) {
                    processSubSentences(onParagraphFinished);
                }
            });
        }
    }

    function speakNextParagraph() {
        if (!isSpeaking || isPaused) return;
        if (ttsQueue.length === 0) {
            stopSpeech();
            return;
        }

        const item = ttsQueue.shift();
        removeHighlight();

        if (item.element) {
            currentHighlightEl = item.element;
            currentHighlightEl.classList.add('lz-a11y-tts-reading-highlight');
            try {
                currentHighlightEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch(e) {}
        }

        subSentenceQueue = splitIntoSubSentences(item.text);
        processSubSentences(() => {
            speakNextParagraph();
        });
    }

    function extractArticleChunks() {
        const chunks = [];
        const articleContainer = document.querySelector('article, .news-content, .post-content, .entry-content, .detail-content, main') || document.body;
        
        const mainTitle = document.querySelector('h1');
        if (mainTitle && mainTitle.innerText.trim()) {
            chunks.push({
                text: 'Judul: ' + mainTitle.innerText.trim(),
                element: mainTitle
            });
        }

        const textElements = articleContainer.querySelectorAll('p, h2, h3, h4, li');
        textElements.forEach(el => {
            if (el.closest('#lzA11yApp, nav, footer, script, style, header')) return;

            const text = el.innerText.trim();
            if (text.length > 5) {
                chunks.push({
                    text: text,
                    element: el
                });
            }
        });

        return chunks;
    }

    function startFullPageSpeech() {
        stopSpeech();
        ttsQueue = extractArticleChunks();

        if (ttsQueue.length === 0) {
            alert('Tidak ditemukan teks artikel yang dapat dibaca di halaman ini.');
            return;
        }

        isSpeaking = true;
        isPaused = false;

        ttsPlayBtn.style.display = 'none';
        ttsPauseBtn.style.display = 'inline-flex';
        ttsStopBtn.style.display = 'inline-flex';
        ttsStatus.style.display = 'flex';
        ttsStatusText.textContent = 'Membaca Bahasa Indonesia...';

        speakNextParagraph();
    }

    ttsPlayBtn.addEventListener('click', startFullPageSpeech);

    ttsPauseBtn.addEventListener('click', () => {
        if (!isSpeaking) return;
        if (isPaused) {
            isPaused = false;
            ttsPauseBtn.querySelector('span').textContent = 'Jeda';
            ttsStatusText.textContent = 'Membaca Bahasa Indonesia...';
            if (currentAudio) {
                currentAudio.play();
            } else if (synth) {
                synth.resume();
            }
            if (!currentAudio && (!synth || !synth.speaking)) {
                processSubSentences(() => { speakNextParagraph(); });
            }
        } else {
            isPaused = true;
            ttsPauseBtn.querySelector('span').textContent = 'Lanjut';
            ttsStatusText.textContent = 'Dijeda';
            if (currentAudio) {
                currentAudio.pause();
            } else if (synth) {
                synth.pause();
            }
        }
    });

    ttsStopBtn.addEventListener('click', stopSpeech);

    // ---------------------------------------------------------
    // BACA HANYA TEKS YANG DI-SELECT / DI-BLOK SAJA
    // ---------------------------------------------------------
    function readSelectedTextOnly(targetEl) {
        if (!state.readSelection) return;
        if (targetEl && targetEl.closest('#lzA11yApp')) return;

        // Ambil teks yang secara nyata di-blok/select oleh pengguna
        const selection = window.getSelection();
        const selectedText = selection ? selection.toString().trim() : '';

        // Abaikan jika tidak ada teks yang di-select
        if (!selectedText || selectedText.length < 2) return;

        stopSpeech();
        removeHighlight();

        isSpeaking = true;
        isPaused = false;
        subSentenceQueue = splitIntoSubSentences(selectedText);

        ttsPlayBtn.style.display = 'none';
        ttsPauseBtn.style.display = 'inline-flex';
        ttsStopBtn.style.display = 'inline-flex';
        ttsStatus.style.display = 'flex';
        ttsStatusText.textContent = 'Membaca teks terpilih...';

        processSubSentences(() => {
            stopSpeech();
        });
    }

    document.addEventListener('mouseup', (e) => {
        if (!state.readSelection) return;
        setTimeout(() => {
            readSelectedTextOnly(e.target);
        }, 60);
    });

    document.addEventListener('keyup', (e) => {
        if (!state.readSelection) return;
        if (e.key && (e.key.includes('Arrow') || e.key === 'Shift')) {
            setTimeout(() => {
                readSelectedTextOnly(document.activeElement);
            }, 100);
        }
    });

    // ---------------------------------------------------------
    // INITIALIZATION
    // ---------------------------------------------------------
    applyFeatures();
})();
</script>
@endif
