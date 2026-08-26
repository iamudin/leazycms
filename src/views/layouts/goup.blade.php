<!-- Tombol Go Up -->
<button id="goUpBtn" class="go-up-btn" aria-label="Kembali ke atas" title="Kembali ke atas">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="m18 15-6-6-6 6"/>
    </svg>
</button>

<style>
.go-up-btn {
    position: fixed;
    bottom: 68px;
    right: 20px;
    z-index: 999;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    color: #374151;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    opacity: 0;
    visibility: hidden;
    transform: translateY(16px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}

.go-up-btn svg {
    transition: transform 0.2s ease;
}

.go-up-btn:hover {
    color: #111827;
    background: #ffffff;
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.15);
}

.go-up-btn:hover svg {
    transform: translateY(-2px);
}

.go-up-btn:active {
    transform: translateY(-1px) scale(0.96);
}

.go-up-btn.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

@if(empty(get_option('whatsapp')) && get_option('float_btn_whatsapp','N') == 'N')
.go-up-btn {
    bottom: 20px !important;
}
@endif
</style>

<script>
(() => {
    const goUpBtn = document.getElementById("goUpBtn");
    if (!goUpBtn) return;

    window.addEventListener("scroll", () => {
        if (window.scrollY > 250) {
            goUpBtn.classList.add("show");
        } else {
            goUpBtn.classList.remove("show");
        }
    }, { passive: true });

    goUpBtn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
})();
</script>
