@php
    $postId = $post->id ?? rand(100, 999);
    $rawContent = (string)($post->content ?? '');

    // Normalisasi tag penutup blok HTML menjadi pemisah baris untuk membaca persis isi kolom content
    $processed = preg_replace('/<\/(p|h1|h2|h3|h4|h5|h6|li|blockquote|div|tr)>/i', "$0\n\n", $rawContent);
    $processed = preg_replace('/<br\s*\/?>/i', "\n", $processed);
    $processed = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $processed);
    $processed = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $processed);
    $plainContent = trim(strip_tags($processed));
    $plainContent = html_entity_decode($plainContent, ENT_QUOTES, 'UTF-8');

    // Pecah menjadi array paragraf bersih khusus dari kolom content
    $contentChunks = array_values(array_filter(array_map('trim', explode("\n", $plainContent)), function($p) {
        return mb_strlen($p) > 2;
    }));
@endphp

@if(!empty($contentChunks))
<!-- Simple Article Text-to-Speech Button -->
<div class="lz-detail-tts-wrap" id="lzDetailTts_{{ $postId }}">
    <button type="button" class="lz-tts-btn-play" id="lzDetailTtsPlay_{{ $postId }}" title="Dengarkan artikel">
        <svg class="lz-icon-play" width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        <svg class="lz-icon-pause" style="display:none;" width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        <span class="lz-tts-txt">Dengarkan</span>
    </button>

    <div class="lz-tts-wave" id="lzDetailTtsWave_{{ $postId }}" style="display:none;">
        <span></span><span></span><span></span>
    </div>

    <button type="button" class="lz-tts-btn-speed" id="lzDetailTtsSpeed_{{ $postId }}" title="Klik untuk ubah kecepatan suara">1.0x</button>

    <button type="button" class="lz-tts-btn-stop" id="lzDetailTtsStop_{{ $postId }}" style="display:none;" title="Hentikan suara">
        <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><rect width="24" height="24" rx="3"/></svg>
    </button>
</div>

<style>
.lz-detail-tts-wrap {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 3px 8px;
    border-radius: 20px;
    margin: 8px 0 12px 0;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 12px;
    line-height: 1;
    color: #334155;
    user-select: none;
    vertical-align: middle;
}

.lz-tts-btn-play {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: transparent;
    border: none;
    padding: 3px 6px;
    font-size: 12px;
    font-weight: 600;
    color: #2563eb;
    cursor: pointer;
    border-radius: 12px;
    transition: opacity 0.2s ease;
    line-height: 1;
}

.lz-tts-btn-play:hover {
    opacity: 0.8;
}

.lz-tts-btn-speed {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
    line-height: 1.2;
}

.lz-tts-btn-speed:hover {
    color: #2563eb;
    border-color: #93c5fd;
    background: #eff6ff;
}

.lz-tts-btn-stop {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    background: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    padding: 0;
    transition: background 0.2s ease;
}

.lz-tts-btn-stop:hover {
    background: #ef4444;
    color: #ffffff;
}

.lz-tts-wave {
    display: flex;
    align-items: center;
    gap: 2px;
    height: 12px;
    padding: 0 2px;
}

.lz-tts-wave span {
    display: inline-block;
    width: 2px;
    height: 100%;
    background: #2563eb;
    border-radius: 1px;
    animation: lzDetailWave 1s ease-in-out infinite alternate;
}
.lz-tts-wave span:nth-child(1) { animation-delay: 0s; height: 35%; }
.lz-tts-wave span:nth-child(2) { animation-delay: 0.2s; height: 100%; }
.lz-tts-wave span:nth-child(3) { animation-delay: 0.4s; height: 60%; }

@keyframes lzDetailWave {
    0% { transform: scaleY(0.3); }
    100% { transform: scaleY(1); }
}
</style>

<script>
(() => {
    const root = document.getElementById('lzDetailTts_{{ $postId }}');
    if (!root) return;

    const playBtn = document.getElementById('lzDetailTtsPlay_{{ $postId }}');
    const stopBtn = document.getElementById('lzDetailTtsStop_{{ $postId }}');
    const speedBtn = document.getElementById('lzDetailTtsSpeed_{{ $postId }}');
    const wave = document.getElementById('lzDetailTtsWave_{{ $postId }}');
    const iconPlay = playBtn.querySelector('.lz-icon-play');
    const iconPause = playBtn.querySelector('.lz-icon-pause');
    const label = playBtn.querySelector('.lz-tts-txt');

    const speeds = [1.0, 1.2, 1.5, 0.8];
    let speedIndex = 0;
    let currentSpeed = 1.0;
    let isSpeaking = false;
    let isPaused = false;
    let currentAudio = null;
    let synth = window.speechSynthesis;
    let chunksQueue = [];
    let currentHighlightEl = null;

    function isFirefoxBrowser() {
        return navigator.userAgent.toLowerCase().includes('firefox');
    }

    function getIndonesianVoice() {
        if (isFirefoxBrowser() || !synth) return null;
        const voices = synth.getVoices() || [];
        return voices.find(v => {
            const lang = (v.lang || '').toLowerCase();
            const name = (v.name || '').toLowerCase();
            return (lang === 'id-id' || lang === 'id_id' || lang === 'id' || name.includes('indonesia') || name.includes('gadis') || name.includes('andika') || name.includes('ardhi'));
        }) || null;
    }

    // Toggle Speed saat tombol kecepatan diklik
    speedBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        speedIndex = (speedIndex + 1) % speeds.length;
        currentSpeed = speeds[speedIndex];
        speedBtn.textContent = currentSpeed + 'x';
        if (currentAudio) {
            currentAudio.playbackRate = currentSpeed;
        }
    });

    function resetUI() {
        isSpeaking = false;
        isPaused = false;
        chunksQueue = [];
        if (currentHighlightEl) {
            currentHighlightEl.classList.remove('lz-a11y-tts-reading-highlight');
            currentHighlightEl = null;
        }
        iconPlay.style.display = 'inline-block';
        iconPause.style.display = 'none';
        label.textContent = 'Dengarkan';
        stopBtn.style.display = 'none';
        wave.style.display = 'none';
    }

    function stopPlayback() {
        if (synth) {
            try { synth.cancel(); } catch(e) {}
        }
        if (currentAudio) {
            try { currentAudio.pause(); } catch(e) {}
            currentAudio = null;
        }
        resetUI();
    }

    stopBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        stopPlayback();
    });

    function extractArticleChunks() {
        const rawChunks = @json($contentChunks);
        const chunks = [];
        const container = document.querySelector('article, .news-content, .post-content, .entry-content, .detail-content, main');
        const domElements = container ? Array.from(container.querySelectorAll('p, h2, h3, h4, li, blockquote')) : [];

        rawChunks.forEach(text => {
            let matchedEl = null;
            if (domElements.length > 0) {
                matchedEl = domElements.find(el => {
                    const elText = el.innerText.trim();
                    return elText && (elText.includes(text.substring(0, 30)) || text.includes(elText.substring(0, 30)));
                });
            }
            chunks.push({
                text: text,
                element: matchedEl || null
            });
        });

        return chunks;
    }

    function splitSubSentences(text) {
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

    function playAudioChunk(text, onDone) {
        if (!isSpeaking || isPaused) return;
        const clean = encodeURIComponent(text.trim());
        const url = `{{ url('lz-tts') }}?text=${clean}`;
        const audio = new Audio(url);
        audio.playbackRate = currentSpeed;
        currentAudio = audio;

        audio.onended = () => {
            currentAudio = null;
            if (onDone) onDone();
        };

        audio.onerror = () => {
            currentAudio = null;
            const indVoice = getIndonesianVoice();
            if (synth && indVoice) {
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'id-ID';
                u.voice = indVoice;
                u.rate = currentSpeed;
                u.onend = onDone;
                u.onerror = onDone;
                synth.speak(u);
            } else if (onDone) {
                onDone();
            }
        };

        audio.play().catch(() => {
            const indVoice = getIndonesianVoice();
            if (synth && indVoice) {
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'id-ID';
                u.voice = indVoice;
                u.rate = currentSpeed;
                u.onend = onDone;
                u.onerror = onDone;
                synth.speak(u);
            } else if (onDone) {
                onDone();
            }
        });
    }

    let subQueue = [];

    function processSubSentences(onParagraphFinished) {
        if (!isSpeaking || isPaused) return;
        if (subQueue.length === 0) {
            if (onParagraphFinished) onParagraphFinished();
            return;
        }

        const sentence = subQueue.shift();
        const nativeVoice = getIndonesianVoice();

        if (nativeVoice && synth) {
            const u = new SpeechSynthesisUtterance(sentence);
            u.lang = 'id-ID';
            u.voice = nativeVoice;
            u.rate = currentSpeed;
            u.onend = () => {
                if (isSpeaking && !isPaused) processSubSentences(onParagraphFinished);
            };
            u.onerror = () => {
                if (isSpeaking && !isPaused) processSubSentences(onParagraphFinished);
            };
            synth.speak(u);
        } else {
            playAudioChunk(sentence, () => {
                if (isSpeaking && !isPaused) processSubSentences(onParagraphFinished);
            });
        }
    }

    function playNextParagraph() {
        if (!isSpeaking || isPaused) return;
        if (chunksQueue.length === 0) {
            stopPlayback();
            return;
        }

        const item = chunksQueue.shift();
        if (currentHighlightEl) {
            currentHighlightEl.classList.remove('lz-a11y-tts-reading-highlight');
            currentHighlightEl = null;
        }

        if (item.element) {
            currentHighlightEl = item.element;
            currentHighlightEl.classList.add('lz-a11y-tts-reading-highlight');
            try {
                currentHighlightEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch(e) {}
        }

        subQueue = splitSubSentences(item.text);
        processSubSentences(() => {
            playNextParagraph();
        });
    }

    function startPlayback() {
        stopPlayback();
        chunksQueue = extractArticleChunks();
        if (chunksQueue.length === 0) return;

        isSpeaking = true;
        isPaused = false;
        iconPlay.style.display = 'none';
        iconPause.style.display = 'inline-block';
        label.textContent = 'Jeda';
        stopBtn.style.display = 'inline-flex';
        wave.style.display = 'flex';

        playNextParagraph();
    }

    playBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (!isSpeaking) {
            startPlayback();
        } else if (isPaused) {
            isPaused = false;
            iconPlay.style.display = 'none';
            iconPause.style.display = 'inline-block';
            label.textContent = 'Jeda';
            wave.style.display = 'flex';
            if (currentAudio) {
                currentAudio.play();
            } else if (synth) {
                synth.resume();
            }
            if (!currentAudio && (!synth || !synth.speaking)) {
                processSubSentences(() => { playNextParagraph(); });
            }
        } else {
            isPaused = true;
            iconPlay.style.display = 'inline-block';
            iconPause.style.display = 'none';
            label.textContent = 'Lanjut';
            wave.style.display = 'none';
            if (currentAudio) {
                currentAudio.pause();
            } else if (synth) {
                synth.pause();
            }
        }
    });
})();
</script>
@endif
