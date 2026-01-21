@if(!isset($tag) || empty($tag))
    <strong id="realtime_timer"></strong>
    @php $tag = 'realtime_timer'; @endphp
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {

        let targetDate = new Date("{{$event_time}}").getTime();
        let countdownTimer = document.getElementById('{{$tag}}');

        let timer = setInterval(() => {
            let now = new Date().getTime();
            let distance = targetDate - now;

            if (distance <= 0) {
                clearInterval(timer);
                countdownTimer.innerHTML = "Acara sedang berlangsung";
                return;
            }

            let days = Math.floor(distance / (1000 * 60 * 60 * 24));
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let output = [];

            if (days > 0) output.push(days + ' Hari');
            if (hours > 0) output.push(String(hours).padStart(2, '0') + ' Jam');
            if (minutes > 0) output.push(String(minutes).padStart(2, '0') + ' Menit');

            // Detik tetap ditampilkan agar terlihat realtime
            output.push(String(seconds).padStart(2, '0') + ' Detik lagi');

            countdownTimer.innerHTML = output.join(' ');
        }, 1000);
    });
</script>