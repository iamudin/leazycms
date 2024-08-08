<style>
    .share-button a img {
        height: 20px;
        padding-right: 30px;
        margin-top:10px;
    }
</style>
<div class="share-button">
    <small><b>Bagikan :</b></small><br>

    <a href="javascript:void(0)" onclick="copyToClipboard()">
        <small class="alert-copied" style="display: none">Copied</small>
        <img title="Copy URL" src="{{ asset('backend/images/copy.svg') }}" alt="">
    </a>
    <a  href="https://www.facebook.com/sharer/sharer.php?u={{ url()->full() }}"
        target="_blank" rel="noopener noreferrer">
        <img title="Bagikan ke Facebook" src="{{ asset('backend/images/facebook.svg') }}" alt="">
    </a>
    <a href="https://api.whatsapp.com/send?text={{ url()->full() }}" target="_blank" rel="noopener noreferrer">
        <img title="Bagikan ke Whatsapp" src="{{ asset('backend/images/whatsapp.svg') }}" alt="">
    </a>

    <a href="https://t.me/share/url?url={{ url()->full() }}" target="_blank" rel="noopener noreferrer">
        <img title="Bagikan ke Telegram" src="{{ asset('backend/images/telegram.svg') }}" alt="">
    </a>

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
        setTimeout(() => {
        }, 500);

    }
</script>
