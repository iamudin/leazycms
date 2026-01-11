
@if(!isset($tag) || empty($tag))
    <strong id="realtime_clock"></strong>
    @php $tag = 'realtime_clock'; @endphp
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        setInterval(() => {
            let now = new Date();
            let h = String(now.getHours()).padStart(2, '0');
            let i = String(now.getMinutes()).padStart(2, '0');
            let s = String(now.getSeconds()).padStart(2, '0');

            document.getElementById('{{$tag}}').innerHTML = h + ":" + i + ":" + s;
        }, 1000);
    });
</script>