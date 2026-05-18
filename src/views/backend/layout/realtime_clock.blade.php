
@if(!isset($tag) || empty($tag))
    <strong id="realtime_clock"></strong>
    @php $tag = 'realtime_clock'; @endphp
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const showDate = "{{ (isset($show_date) && $show_date) ? 'true' : 'false' }}";
        const clockElement = document.getElementById('{{$tag}}');
        
        if (clockElement) {
            setInterval(() => {
                let now = new Date();
                let h = String(now.getHours()).padStart(2, '0');
                let i = String(now.getMinutes()).padStart(2, '0');
                let s = String(now.getSeconds()).padStart(2, '0');
                let timeString = h + ":" + i + ":" + s;

                if (showDate) {
                    const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    
                    let dayName = days[now.getDay()];
                    let day = now.getDate();
                    let monthName = months[now.getMonth()];
                    let year = now.getFullYear();
                    
                    timeString = dayName + ", " + day + " " + monthName + " " + year + " - " + timeString;
                }

                clockElement.innerHTML = timeString;
            }, 1000);
        }
    });
</script>