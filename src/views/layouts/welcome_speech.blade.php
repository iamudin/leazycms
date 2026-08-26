@if(get_option('welcome_speech_active', 'N') == 'Y' && !empty(get_option('welcome_speech')))
@php
    $welcomeText = addslashes(strip_tags(get_option('welcome_speech')));
@endphp
<!-- Welcome Voice Greeting (Text-to-Speech) -->
<div id="lzWelcomeToast" class="lz-welcome-toast" style="display:none;" aria-live="polite">
    <div class="lz-welcome-toast-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
    </div>
    <div class="lz-welcome-toast-content">
        <span class="lz-welcome-toast-desc">{{ Str::limit(get_option('welcome_speech'), 48) }}</span>
    </div>
    <button id="lzWelcomeStop" class="lz-welcome-toast-close" title="Hentikan Suara">&times;</button>
</div>

<style>
.lz-welcome-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 999999;
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #ffffff;
    padding: 8px 14px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.12);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    animation: lzSlideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    max-width: calc(100vw - 48px);
}

@keyframes lzSlideDown {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.lz-welcome-toast-icon {
    width: 28px;
    height: 28px;
    background: #2563eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    animation: lzPulseAudio 1.5s infinite;
}

@keyframes lzPulseAudio {
    0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
    100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
}

.lz-welcome-toast-content {
    display: flex;
    flex-direction: column;
}

.lz-welcome-toast-title {
    font-size: 12px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
}

.lz-welcome-toast-desc {
    font-size: 11px;
    color: #cbd5e1;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
}

.lz-welcome-toast-close {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    padding: 0 4px;
    margin-left: 2px;
    transition: color 0.2s ease;
}

.lz-welcome-toast-close:hover {
    color: #ffffff;
}

@media (max-width: 480px) {
    .lz-welcome-toast {
        top: 15px;
        right: 15px;
        left: 15px;
        border-radius: 16px;
    }
}
</style>

<script>
(() => {
    const speechText = "{!! $welcomeText !!}";
    if (!speechText) return;

    // Putar sekali per sesi browsing pengunjung
    const SESSION_KEY = 'lz_welcome_speech_played';
    if (sessionStorage.getItem(SESSION_KEY)) return;

    const toast = document.getElementById('lzWelcomeToast');
    const stopBtn = document.getElementById('lzWelcomeStop');
    let welcomeAudio = null;
    let played = false;

    function isFirefoxBrowser() {
        return navigator.userAgent.toLowerCase().includes('firefox');
    }

    function getIndonesianVoice() {
        if (!window.speechSynthesis) return null;
        const voices = window.speechSynthesis.getVoices() || [];
        return voices.find(v => {
            const lang = (v.lang || '').toLowerCase();
            const name = (v.name || '').toLowerCase();
            return (lang === 'id-id' || lang === 'id_id' || lang === 'id' || name.includes('indonesia') || name.includes('gadis') || name.includes('andika') || name.includes('ardhi'));
        }) || null;
    }

    function hideToast() {
        if (toast) {
            toast.style.transition = 'all 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-15px)';
            setTimeout(() => { toast.style.display = 'none'; }, 300);
        }
    }

    function stopWelcome() {
        if (window.speechSynthesis) {
            try { window.speechSynthesis.cancel(); } catch(e) {}
        }
        if (welcomeAudio) {
            try { welcomeAudio.pause(); } catch(e) {}
            welcomeAudio = null;
        }
        hideToast();
    }

    if (stopBtn) {
        stopBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            stopWelcome();
        });
    }

    function playWelcomeSpeech() {
        if (played) return;

        const nativeVoice = isFirefoxBrowser() ? null : getIndonesianVoice();

        if (nativeVoice && window.speechSynthesis) {
            played = true;
            sessionStorage.setItem(SESSION_KEY, '1');
            if (toast) toast.style.display = 'flex';

            const u = new SpeechSynthesisUtterance(speechText);
            u.lang = 'id-ID';
            u.rate = 1.0;
            u.voice = nativeVoice;
            u.onend = () => { hideToast(); };
            u.onerror = () => { hideToast(); };
            window.speechSynthesis.speak(u);
        } else {
            // Gunakan Audio Stream Bahasa Indonesia Asli via /lz-tts (Khusus Firefox & Browser tanpa Indonesian voice)
            const clean = encodeURIComponent(speechText);
            const url = `{{ url('lz-tts') }}?text=${clean}`;
            const audio = new Audio(url);
            welcomeAudio = audio;

            audio.onended = () => {
                hideToast();
            };
            audio.onerror = () => {
                hideToast();
            };

            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    // Autoplay berhasil
                    played = true;
                    sessionStorage.setItem(SESSION_KEY, '1');
                    if (toast) toast.style.display = 'flex';
                }).catch(() => {
                    // Autoplay diblokir oleh browser sebelum ada interaksi
                    // JANGAN fallback ke SpeechSynthesis agar tidak berlogat bahasa Inggris!
                    // Tunggu interaksi pertama pengguna (klik / scroll / sentuh)
                    played = false;
                });
            }
        }
    }

    // Coba putar otomatis saat halaman dimuat
    window.addEventListener('load', () => {
        setTimeout(() => {
            playWelcomeSpeech();
        }, 500);
    });

    // Fallback: Jika kebijakan browser memblokir autoplay tanpa gesture, putar pada interaksi pertama
    const triggerEvents = ['click', 'touchstart', 'scroll', 'keydown'];
    const handleFirstInteraction = () => {
        if (!played) {
            playWelcomeSpeech();
        }
        triggerEvents.forEach(evt => window.removeEventListener(evt, handleFirstInteraction));
    };

    triggerEvents.forEach(evt => window.addEventListener(evt, handleFirstInteraction, { passive: true, once: true }));
})();
</script>
@endif
