@php
    $type = $type ?? 'posts';
    $tree = $tree ?? [];
    $module = $module ?? get_module($type);
    $widgetId = 'leazy_archive_cal_' . substr(md5($type . microtime()), 0, 8);

    // Dapatkan daftar tahun yang memiliki data arsip
    $years = array_keys($tree);
    rsort($years);
    $defaultYear = !empty($years) ? $years[0] : (int)date('Y');

    // Dapatkan daftar bulan yang memiliki data pada tahun default
    $defaultMonths = !empty($tree[$defaultYear]['months']) ? array_keys($tree[$defaultYear]['months']) : [];
    rsort($defaultMonths);
    $defaultMonth = !empty($defaultMonths) ? $defaultMonths[0] : date('m');

    $monthNames = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
        '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
@endphp

<div id="{{ $widgetId }}" class="leazy-archive-calendar" style="width: 100%; box-sizing: border-box; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.4; color: #1e293b;">
    <style>
        #{{ $widgetId }} * { box-sizing: border-box; }
        #{{ $widgetId }} .cal-btn:hover:not(:disabled) { background: #e2e8f0 !important; color: #0f172a !important; }
        #{{ $widgetId }} .cal-btn:active:not(:disabled) { transform: scale(0.95); }
        #{{ $widgetId }} .cal-btn:disabled { opacity: 0.35; cursor: not-allowed !important; }
        #{{ $widgetId }} .cal-active-day:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.4) !important; background: #0369a1 !important; }
        #{{ $widgetId }} .cal-footer-link:hover { text-decoration: underline !important; color: #0284c7 !important; }
    </style>

    @if(empty($tree))
        <div style="padding: 16px 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; font-size: 13px; text-align: center;">
            <i class="fa fa-calendar-times" style="margin-right: 6px; color: #94a3b8; font-size: 16px;"></i> Belum ada arsip kalender untuk {{ $module->title ?? ucfirst($type) }}.
        </div>
    @else
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden;">
            
            <!-- Judul Widget Arsip -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px; background: #e0f2fe; color: #0284c7; font-size: 13px;">
                        <i class="fa fa-calendar-alt"></i>
                    </span>
                    <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; letter-spacing: -0.01em;">
                        Arsip {{ $module->title ?? ucfirst($type) }}
                    </h4>
                </div>
            </div>

            <!-- Header Kalender: Navigasi Antar Periode yang Tersedia -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                
                <button type="button" id="{{ $widgetId }}_btn_prev" class="cal-btn" onclick="window['{{ $widgetId }}_prevPeriod']()" style="border: none; background: #f1f5f9; color: #475569; width: 30px; height: 30px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.2s;" title="Periode Sebelumnya">
                    <i class="fa fa-chevron-left"></i>
                </button>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <!-- Dropdown Bulan (Hanya bulan yang ada data pada tahun terpilih) -->
                    <select id="{{ $widgetId }}_select_month" onchange="window['{{ $widgetId }}_onMonthChange']()" style="border: 1px solid #cbd5e1; background: #ffffff; color: #0f172a; font-weight: 700; font-size: 12.5px; border-radius: 8px; padding: 4px 8px; outline: none; cursor: pointer;">
                        @foreach($defaultMonths as $mNum)
                            <option value="{{ $mNum }}" {{ $mNum === $defaultMonth ? 'selected' : '' }}>
                                {{ $monthNames[$mNum] ?? $mNum }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Dropdown Tahun (Hanya tahun yang ada data) -->
                    <select id="{{ $widgetId }}_select_year" onchange="window['{{ $widgetId }}_onYearChange']()" style="border: 1px solid #cbd5e1; background: #ffffff; color: #0f172a; font-weight: 700; font-size: 12.5px; border-radius: 8px; padding: 4px 8px; outline: none; cursor: pointer;">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $defaultYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="{{ $widgetId }}_btn_next" class="cal-btn" onclick="window['{{ $widgetId }}_nextPeriod']()" style="border: none; background: #f1f5f9; color: #475569; width: 30px; height: 30px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all 0.2s;" title="Periode Berikutnya">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>

            <!-- Nama Hari (Minggu - Sabtu) -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; font-weight: 700; font-size: 11px; margin-bottom: 6px;">
                <div style="color: #ef4444; padding: 4px 0;" title="Minggu">Min</div>
                <div style="color: #64748b; padding: 4px 0;" title="Senin">Sen</div>
                <div style="color: #64748b; padding: 4px 0;" title="Selasa">Sel</div>
                <div style="color: #64748b; padding: 4px 0;" title="Rabu">Rab</div>
                <div style="color: #64748b; padding: 4px 0;" title="Kamis">Kam</div>
                <div style="color: #64748b; padding: 4px 0;" title="Jumat">Jum</div>
                <div style="color: #0284c7; padding: 4px 0;" title="Sabtu">Sab</div>
            </div>

            <!-- Grid Tanggal -->
            <div id="{{ $widgetId }}_grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;">
                <!-- Dibuat dinamis oleh JavaScript -->
            </div>

            <!-- Footer: Quick Links & Summary -->
            <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 6px; font-size: 11.5px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                    <a id="{{ $widgetId }}_month_link" href="{{ url($type . '/archive/' . $defaultYear . '/' . $defaultMonth) }}" class="cal-footer-link" style="color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa fa-calendar-check"></i> <span id="{{ $widgetId }}_month_label">Arsip Bulan {{ $monthNames[$defaultMonth] ?? $defaultMonth }} {{ $defaultYear }}</span>
                    </a>
                    <span id="{{ $widgetId }}_month_badge" style="font-size: 10.5px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 1px 7px; border-radius: 10px;">
                        {{ $tree[$defaultYear]['months'][$defaultMonth]['count'] ?? 0 }} post
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                    <a id="{{ $widgetId }}_year_link" href="{{ url($type . '/archive/' . $defaultYear) }}" class="cal-footer-link" style="color: #475569; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa fa-folder-open"></i> <span id="{{ $widgetId }}_year_label">Semua Arsip Tahun {{ $defaultYear }}</span>
                    </a>
                    <span id="{{ $widgetId }}_year_badge" style="font-size: 10.5px; font-weight: 700; background: #f1f5f9; color: #475569; padding: 1px 7px; border-radius: 10px;">
                        {{ $tree[$defaultYear]['count'] ?? 0 }} post
                    </span>
                </div>
            </div>

        </div>

        <script>
        (function() {
            var treeData = @json($tree);
            var monthNames = @json($monthNames);
            var baseUrl = "{{ url($type . '/archive') }}";
            var widgetId = "{{ $widgetId }}";

            // Bangun daftar periode yang ada secara berurutan (dari terlama ke terbaru)
            var availablePeriods = [];
            var sortedYears = Object.keys(treeData).sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); });
            
            sortedYears.forEach(function(y) {
                if (treeData[y] && treeData[y].months) {
                    var sortedMonths = Object.keys(treeData[y].months).sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); });
                    sortedMonths.forEach(function(m) {
                        availablePeriods.push({ year: y, month: m });
                    });
                }
            });

            // Set indeks periode aktif (default: periode terbaru yang ada di data)
            var currentPeriodIndex = availablePeriods.length > 0 ? availablePeriods.length - 1 : 0;
            var currentYear = availablePeriods.length > 0 ? availablePeriods[currentPeriodIndex].year : "{{ $defaultYear }}";
            var currentMonth = availablePeriods.length > 0 ? availablePeriods[currentPeriodIndex].month : "{{ $defaultMonth }}";

            function pad2(n) {
                return (n < 10 ? '0' : '') + parseInt(n, 10);
            }

            function updateMonthOptions(year, selectedMonth) {
                var selMonth = document.getElementById(widgetId + '_select_month');
                if (!selMonth) return;

                selMonth.innerHTML = '';
                if (treeData[year] && treeData[year].months) {
                    var months = Object.keys(treeData[year].months).sort(function(a, b) {
                        return parseInt(b, 10) - parseInt(a, 10);
                    });

                    months.forEach(function(m) {
                        var opt = document.createElement('option');
                        opt.value = m;
                        opt.innerText = monthNames[m] || ('Bulan ' + m);
                        if (m === selectedMonth) {
                            opt.selected = true;
                        }
                        selMonth.appendChild(opt);
                    });
                }
            }

            function renderCalendar() {
                var grid = document.getElementById(widgetId + '_grid');
                var selMonth = document.getElementById(widgetId + '_select_month');
                var selYear = document.getElementById(widgetId + '_select_year');
                var btnPrev = document.getElementById(widgetId + '_btn_prev');
                var btnNext = document.getElementById(widgetId + '_btn_next');
                var monthLink = document.getElementById(widgetId + '_month_link');
                var monthLabel = document.getElementById(widgetId + '_month_label');
                var monthBadge = document.getElementById(widgetId + '_month_badge');
                var yearLink = document.getElementById(widgetId + '_year_link');
                var yearLabel = document.getElementById(widgetId + '_year_label');
                var yearBadge = document.getElementById(widgetId + '_year_badge');

                if (!grid) return;

                var y = parseInt(currentYear, 10);
                var m = parseInt(currentMonth, 10);
                var mStr = pad2(m);

                selYear.value = y.toString();
                updateMonthOptions(currentYear, mStr);
                selMonth.value = mStr;

                // Update status tombol prev & next berdasarkan ketersediaan periode data
                if (btnPrev) btnPrev.disabled = (currentPeriodIndex <= 0);
                if (btnNext) btnNext.disabled = (currentPeriodIndex >= availablePeriods.length - 1);

                var monthData = (treeData[y] && treeData[y].months && treeData[y].months[mStr]) ? treeData[y].months[mStr] : null;
                var yearData = treeData[y] || null;

                var mCount = monthData ? monthData.count : 0;
                var yCount = yearData ? yearData.count : 0;
                var mName = monthNames[mStr] || ('Bulan ' + m);

                monthLink.href = baseUrl + '/' + y + '/' + mStr;
                monthLabel.innerText = 'Arsip ' + mName + ' ' + y;
                monthBadge.innerText = mCount + ' post';

                yearLink.href = baseUrl + '/' + y;
                yearLabel.innerText = 'Semua Arsip ' + y;
                yearBadge.innerText = yCount + ' post';

                // Hitung hari kalender
                var firstDayIndex = new Date(y, m - 1, 1).getDay(); // 0 = Minggu, 1 = Senin...
                var totalDaysInMonth = new Date(y, m, 0).getDate(); // 28, 29, 30, 31

                var activeDays = (monthData && monthData.days) ? monthData.days : {};

                var html = '';

                // Sel kosong sebelum tanggal 1
                for (var b = 0; b < firstDayIndex; b++) {
                    html += '<div style="min-height: 38px; border-radius: 8px;"></div>';
                }

                // Render setiap tanggal dalam bulan ini
                for (var d = 1; d <= totalDaysInMonth; d++) {
                    var dStr = pad2(d);
                    var count = activeDays[dStr] || 0;
                    var dayOfWeek = (firstDayIndex + d - 1) % 7;
                    var isSunday = (dayOfWeek === 0);

                    if (count > 0) {
                        var dayUrl = baseUrl + '/' + y + '/' + mStr + '/' + dStr;
                        var titleText = 'Tanggal ' + d + ' ' + mName + ' ' + y + ' (' + count + ' Postingan)';
                        html += '<a href="' + dayUrl + '" class="cal-active-day" title="' + titleText + '" style="position: relative; min-height: 38px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 8px; text-decoration: none; box-shadow: 0 1px 3px rgba(2,132,199,0.3); transition: all 0.15s; padding: 2px 1px;">' +
                                    '<span style="font-size: 12px; line-height: 1.1; font-weight: 800;">' + d + '</span>' +
                                    '<span style="font-size: 9px; font-weight: 800; background: rgba(255,255,255,0.25); color: #ffffff; padding: 1px 4px; border-radius: 4px; line-height: 1; margin-top: 2px;">' + count + '</span>' +
                                '</a>';
                    } else {
                        var colorStyle = isSunday ? 'color: #f87171;' : 'color: #94a3b8;';
                        html += '<div style="min-height: 38px; display: flex; flex-direction: column; align-items: center; justify-content: center; ' + colorStyle + ' font-size: 12px; border-radius: 8px; background: #fafafa; padding: 2px 1px;">' +
                                    '<span style="font-size: 12px; line-height: 1.1;">' + d + '</span>' +
                                '</div>';
                    }
                }

                grid.innerHTML = html;
            }

            function syncPeriodIndex() {
                for (var i = 0; i < availablePeriods.length; i++) {
                    if (availablePeriods[i].year === currentYear && availablePeriods[i].month === currentMonth) {
                        currentPeriodIndex = i;
                        return;
                    }
                }
            }

            window[widgetId + '_onYearChange'] = function() {
                var selYear = document.getElementById(widgetId + '_select_year');
                currentYear = selYear.value;

                // Pilih bulan terbaru yang ada pada tahun tersebut
                if (treeData[currentYear] && treeData[currentYear].months) {
                    var months = Object.keys(treeData[currentYear].months).sort(function(a, b) {
                        return parseInt(b, 10) - parseInt(a, 10);
                    });
                    if (months.length > 0) {
                        currentMonth = months[0];
                    }
                }

                syncPeriodIndex();
                renderCalendar();
            };

            window[widgetId + '_onMonthChange'] = function() {
                var selMonth = document.getElementById(widgetId + '_select_month');
                currentMonth = selMonth.value;
                syncPeriodIndex();
                renderCalendar();
            };

            window[widgetId + '_prevPeriod'] = function() {
                if (currentPeriodIndex > 0) {
                    currentPeriodIndex--;
                    currentYear = availablePeriods[currentPeriodIndex].year;
                    currentMonth = availablePeriods[currentPeriodIndex].month;
                    renderCalendar();
                }
            };

            window[widgetId + '_nextPeriod'] = function() {
                if (currentPeriodIndex < availablePeriods.length - 1) {
                    currentPeriodIndex++;
                    currentYear = availablePeriods[currentPeriodIndex].year;
                    currentMonth = availablePeriods[currentPeriodIndex].month;
                    renderCalendar();
                }
            };

            renderCalendar();
        })();
        </script>
    @endif
</div>
