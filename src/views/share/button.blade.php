<style>
    .share-button img {
        height: 20px;
        padding-right: 10px;
        margin-top: 10px;
    }
    .share-button img:hover{
        cursor: pointer;
    }
</style>
<div class="share-button">
    <small class="alert-copied" style="display: none">Copied</small>
    <img onclick="copyToClipboard()" title="Copy URL" src="{{ asset('backend/images/copy.svg') }}" alt="Copy this url">
    <img onclick="window.open('https\://www.facebook.com/sharer/sharer.php?u={{ $url }}')" title="Bagikan ke Facebook" src="{{ asset('backend/images/facebook.svg') }}" alt="Share to Facebook">
    <img onclick="window.open('https\://api.whatsapp.com/send?text={{ $url }}')" title="Bagikan ke Whatsapp"
        src="{{ asset('backend/images/whatsapp.svg') }}" alt="Share to Whatsapp">
    <img onclick="window.open('https\://t.me/share/url?url={{ $url }}')" title="Bagikan ke Telegram" src="{{ asset('backend/images/telegram.svg') }}" alt="Share to Telegram">
</div>
<script>
    function copyToClipboard() {
        var urlToCopy = "{{ url()->full() }}";
        var input = document.createElement('input');
        input.value = urlToCopy;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        alert('URL Sukses Disalin');
        setTimeout(() => {}, 500);

    }
</script>
