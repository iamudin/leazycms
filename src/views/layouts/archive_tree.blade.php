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

<div id="{{ $widgetId }}" class="leazy-archive-calendar" style="width: 100%; box-sizing: border-box; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.4; color: var(--cal-text);">
    <style>
        #{{ $widgetId }} {
            /* Light Mode (Default) */
            --cal-bg: #ffffff;
            --cal-border: #e2e8f0;
            --cal-text: #1e293b;
            --cal-text-muted: #64748b;
            --cal-text-heading: #0f172a;
            --cal-divider: #f1f5f9;
            --cal-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);

            --cal-btn-bg: #f1f5f9;
            --cal-btn-color: #475569;
            --cal-btn-hover-bg: #e2e8f0;
            --cal-btn-hover-color: #0f172a;

            --cal-select-bg: #ffffff;
            --cal-select-border: #cbd5e1;
            --cal-select-color: #0f172a;

            --cal-day-inactive-bg: #fafafa;
            --cal-day-inactive-color: #94a3b8;
            --cal-day-sunday-color: #ef4444;
            --cal-day-saturday-color: #0284c7;

            --cal-primary: #0284c7;
            --cal-primary-hover: #0369a1;
            --cal-primary-light-bg: #e0f2fe;
            --cal-primary-light-text: #0284c7;

            --cal-badge-neutral-bg: #f1f5f9;
            --cal-badge-neutral-color: #475569;

            --cal-link-neutral-color: #475569;
            --cal-link-neutral-hover: #0284c7;

            --cal-empty-bg: #f8fafc;
            --cal-empty-border: #cbd5e1;
        }

        /* Dark Mode: Tailwind (.dark), Bootstrap 5.3 ([data-bs-theme="dark"]), & Generic dark themes */
        html.dark #{{ $widgetId }},
        body.dark #{{ $widgetId }},
        .dark #{{ $widgetId }},
        html[data-bs-theme="dark"] #{{ $widgetId }},
        body[data-bs-theme="dark"] #{{ $widgetId }},
        [data-bs-theme="dark"] #{{ $widgetId }},
        html[data-theme="dark"] #{{ $widgetId }},
        body[data-theme="dark"] #{{ $widgetId }},
        [data-theme="dark"] #{{ $widgetId }},
        html[data-mode="dark"] #{{ $widgetId }},
        body[data-mode="dark"] #{{ $widgetId }},
        [data-mode="dark"] #{{ $widgetId }},
        .dark-mode #{{ $widgetId }},
        .dark-theme #{{ $widgetId }},
        #{{ $widgetId }}.dark,
        #{{ $widgetId }}[data-bs-theme="dark"],
        #{{ $widgetId }}[data-theme="dark"] {
            --cal-bg: #1e293b;
            --cal-border: #334155;
            --cal-text: #e2e8f0;
            --cal-text-muted: #94a3b8;
            --cal-text-heading: #f8fafc;
            --cal-divider: #334155;
            --cal-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);

            --cal-btn-bg: #334155;
            --cal-btn-color: #cbd5e1;
            --cal-btn-hover-bg: #475569;
            --cal-btn-hover-color: #ffffff;

            --cal-select-bg: #0f172a;
            --cal-select-border: #475569;
            --cal-select-color: #f8fafc;

            --cal-day-inactive-bg: rgba(255, 255, 255, 0.04);
            --cal-day-inactive-color: #64748b;
            --cal-day-sunday-color: #f87171;
            --cal-day-saturday-color: #38bdf8;

            --cal-primary: #0284c7;
            --cal-primary-hover: #0ea5e9;
            --cal-primary-light-bg: rgba(2, 132, 199, 0.22);
            --cal-primary-light-text: #38bdf8;

            --cal-badge-neutral-bg: #334155;
            --cal-badge-neutral-color: #94a3b8;

            --cal-link-neutral-color: #cbd5e1;
            --cal-link-neutral-hover: #38bdf8;

            --cal-empty-bg: #0f172a;
            --cal-empty-border: #334155;
        }

        /* Explicit Light Mode Override */
        html.light #{{ $widgetId }},
        body.light #{{ $widgetId }},
        .light #{{ $widgetId }},
        html[data-bs-theme="light"] #{{ $widgetId }},
        body[data-bs-theme="light"] #{{ $widgetId }},
        [data-bs-theme="light"] #{{ $widgetId }},
        html[data-theme="light"] #{{ $widgetId }},
        body[data-theme="light"] #{{ $widgetId }},
        [data-theme="light"] #{{ $widgetId }},
        html[data-mode="light"] #{{ $widgetId }},
        body[data-mode="light"] #{{ $widgetId }},
        [data-mode="light"] #{{ $widgetId }},
        #{{ $widgetId }}.light,
        #{{ $widgetId }}[data-bs-theme="light"],
        #{{ $widgetId }}[data-theme="light"] {
            --cal-bg: #ffffff;
            --cal-border: #e2e8f0;
            --cal-text: #1e293b;
            --cal-text-muted: #64748b;
            --cal-text-heading: #0f172a;
            --cal-divider: #f1f5f9;
            --cal-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);

            --cal-btn-bg: #f1f5f9;
            --cal-btn-color: #475569;
            --cal-btn-hover-bg: #e2e8f0;
            --cal-btn-hover-color: #0f172a;

            --cal-select-bg: #ffffff;
            --cal-select-border: #cbd5e1;
            --cal-select-color: #0f172a;

            --cal-day-inactive-bg: #fafafa;
            --cal-day-inactive-color: #94a3b8;
            --cal-day-sunday-color: #ef4444;
            --cal-day-saturday-color: #0284c7;

            --cal-primary: #0284c7;
            --cal-primary-hover: #0369a1;
            --cal-primary-light-bg: #e0f2fe;
            --cal-primary-light-text: #0284c7;

            --cal-badge-neutral-bg: #f1f5f9;
            --cal-badge-neutral-color: #475569;

            --cal-link-neutral-color: #475569;
            --cal-link-neutral-hover: #0284c7;

            --cal-empty-bg: #f8fafc;
            --cal-empty-border: #cbd5e1;
        }

        #{{ $widgetId }} * { box-sizing: border-box; }
        #{{ $widgetId }} .cal-card {
            background: var(--cal-bg);
            border: 1px solid var(--cal-border);
            border-radius: 16px;
            padding: 14px;
            box-shadow: var(--cal-shadow);
            overflow: hidden;
            transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
        }
        #{{ $widgetId }} .cal-empty-state {
            padding: 16px 20px;
            background: var(--cal-empty-bg);
            border: 1px dashed var(--cal-empty-border);
            border-radius: 12px;
            color: var(--cal-text-muted);
            font-size: 13px;
            text-align: center;
            transition: background-color 0.2s, border-color 0.2s, color 0.2s;
        }
        #{{ $widgetId }} .cal-header-divider {
            border-bottom: 1px solid var(--cal-divider);
            transition: border-color 0.2s;
        }
        #{{ $widgetId }} .cal-footer-divider {
            border-top: 1px solid var(--cal-divider);
            transition: border-color 0.2s;
        }
        #{{ $widgetId }} .cal-btn {
            border: none;
            background: var(--cal-btn-bg);
            color: var(--cal-btn-color);
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s;
        }
        #{{ $widgetId }} .cal-btn:hover:not(:disabled) {
            background: var(--cal-btn-hover-bg) !important;
            color: var(--cal-btn-hover-color) !important;
        }
        #{{ $widgetId }} .cal-btn:active:not(:disabled) {
            transform: scale(0.95);
        }
        #{{ $widgetId }} .cal-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed !important;
        }
        #{{ $widgetId }} .cal-select {
            border: 1px solid var(--cal-select-border);
            background: var(--cal-select-bg);
            color: var(--cal-select-color);
            font-weight: 700;
            font-size: 12.5px;
            border-radius: 8px;
            padding: 4px 8px;
            outline: none;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s, color 0.2s, box-shadow 0.2s;
        }
        #{{ $widgetId }} .cal-select:focus {
            border-color: var(--cal-primary);
            box-shadow: 0 0 0 2px var(--cal-primary-light-bg);
        }
        #{{ $widgetId }} .cal-select option {
            background: var(--cal-select-bg);
            color: var(--cal-select-color);
        }
        #{{ $widgetId }} .cal-icon-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--cal-primary-light-bg);
            color: var(--cal-primary-light-text);
            font-size: 13px;
            transition: background-color 0.2s, color 0.2s;
        }
        #{{ $widgetId }} .cal-title {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            color: var(--cal-text-heading);
            letter-spacing: -0.01em;
            transition: color 0.2s;
        }
        #{{ $widgetId }} .cal-day-header {
            padding: 4px 0;
            transition: color 0.2s;
        }
        #{{ $widgetId }} .cal-inactive-day {
            min-height: 38px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border-radius: 8px;
            background: var(--cal-day-inactive-bg);
            color: var(--cal-day-inactive-color);
            padding: 2px 1px;
            transition: background-color 0.2s, color 0.2s;
        }
        #{{ $widgetId }} .cal-inactive-day.cal-sunday {
            color: var(--cal-day-sunday-color);
        }
        #{{ $widgetId }} .cal-active-day {
            position: relative;
            min-height: 38px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--cal-primary);
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(2, 132, 199, 0.3);
            transition: all 0.15s ease-in-out;
            padding: 2px 1px;
        }
        #{{ $widgetId }} .cal-active-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -1px rgba(2, 132, 199, 0.45) !important;
            background: var(--cal-primary-hover) !important;
            color: #ffffff !important;
        }
        #{{ $widgetId }} .cal-active-count {
            font-size: 9px;
            font-weight: 800;
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
            padding: 1px 4px;
            border-radius: 4px;
            line-height: 1;
            margin-top: 2px;
        }
        #{{ $widgetId }} .cal-month-link {
            color: var(--cal-primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }
        #{{ $widgetId }} .cal-month-link:hover {
            text-decoration: underline !important;
            color: var(--cal-primary-hover) !important;
        }
        #{{ $widgetId }} .cal-month-badge {
            font-size: 10.5px;
            font-weight: 700;
            background: var(--cal-primary-light-bg);
            color: var(--cal-primary-light-text);
            padding: 1px 7px;
            border-radius: 10px;
            transition: background-color 0.2s, color 0.2s;
        }
        #{{ $widgetId }} .cal-year-link {
            color: var(--cal-link-neutral-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }
        #{{ $widgetId }} .cal-year-link:hover {
            text-decoration: underline !important;
            color: var(--cal-link-neutral-hover) !important;
        }
        #{{ $widgetId }} .cal-year-badge {
            font-size: 10.5px;
            font-weight: 700;
            background: var(--cal-badge-neutral-bg);
            color: var(--cal-badge-neutral-color);
            padding: 1px 7px;
            border-radius: 10px;
            transition: background-color 0.2s, color 0.2s;
        }
    </style>

    @if(empty($tree))
        <div class="cal-empty-state">
            <i class="fa fa-calendar-times" style="margin-right: 6px; color: var(--cal-text-muted); font-size: 16px;"></i> Belum ada arsip kalender untuk {{ $module->title ?? ucfirst($type) }}.
        </div>
    @else
        <div class="cal-card">
            
            <!-- Judul Widget Arsip -->
            <div class="cal-header-divider" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="cal-icon-badge">
                        <i class="fa fa-calendar-alt"></i>
                    </span>
                    <h4 class="cal-title">
                        Arsip {{ $module->title ?? ucfirst($type) }}
                    </h4>
                </div>
            </div>

            <!-- Header Kalender: Navigasi Antar Periode yang Tersedia -->
            <div class="cal-header-divider" style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 12px; padding-bottom: 10px;">
                
                <button type="button" id="{{ $widgetId }}_btn_prev" class="cal-btn" onclick="window['{{ $widgetId }}_prevPeriod']()" title="Periode Sebelumnya">
                    <i class="fa fa-chevron-left"></i>
                </button>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <!-- Dropdown Bulan (Hanya bulan yang ada data pada tahun terpilih) -->
                    <select id="{{ $widgetId }}_select_month" class="cal-select" onchange="window['{{ $widgetId }}_onMonthChange']()">
                        @foreach($defaultMonths as $mNum)
                            <option value="{{ $mNum }}" {{ $mNum === $defaultMonth ? 'selected' : '' }}>
                                {{ $monthNames[$mNum] ?? $mNum }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Dropdown Tahun (Hanya tahun yang ada data) -->
                    <select id="{{ $widgetId }}_select_year" class="cal-select" onchange="window['{{ $widgetId }}_onYearChange']()">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $defaultYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="{{ $widgetId }}_btn_next" class="cal-btn" onclick="window['{{ $widgetId }}_nextPeriod']()" title="Periode Berikutnya">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>

            <!-- Nama Hari (Minggu - Sabtu) -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; font-weight: 700; font-size: 11px; margin-bottom: 6px;">
                <div class="cal-day-header" style="color: var(--cal-day-sunday-color);" title="Minggu">Min</div>
                <div class="cal-day-header" style="color: var(--cal-text-muted);" title="Senin">Sen</div>
                <div class="cal-day-header" style="color: var(--cal-text-muted);" title="Selasa">Sel</div>
                <div class="cal-day-header" style="color: var(--cal-text-muted);" title="Rabu">Rab</div>
                <div class="cal-day-header" style="color: var(--cal-text-muted);" title="Kamis">Kam</div>
                <div class="cal-day-header" style="color: var(--cal-text-muted);" title="Jumat">Jum</div>
                <div class="cal-day-header" style="color: var(--cal-day-saturday-color);" title="Sabtu">Sab</div>
            </div>

            <!-- Grid Tanggal -->
            <div id="{{ $widgetId }}_grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;">
                <!-- Dibuat dinamis oleh JavaScript -->
            </div>

            <!-- Footer: Quick Links & Summary -->
            <div class="cal-footer-divider" style="margin-top: 12px; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 11.5px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                    <a id="{{ $widgetId }}_month_link" href="{{ url($type . '/archive/' . $defaultYear . '/' . $defaultMonth) }}" class="cal-month-link">
                        <i class="fa fa-calendar-check"></i> <span id="{{ $widgetId }}_month_label">Arsip Bulan {{ $monthNames[$defaultMonth] ?? $defaultMonth }} {{ $defaultYear }}</span>
                    </a>
                    <span id="{{ $widgetId }}_month_badge" class="cal-month-badge">
                        {{ $tree[$defaultYear]['months'][$defaultMonth]['count'] ?? 0 }} post
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                    <a id="{{ $widgetId }}_year_link" href="{{ url($type . '/archive/' . $defaultYear) }}" class="cal-year-link">
                        <i class="fa fa-folder-open"></i> <span id="{{ $widgetId }}_year_label">Semua Arsip Tahun {{ $defaultYear }}</span>
                    </a>
                    <span id="{{ $widgetId }}_year_badge" class="cal-year-badge">
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
                        html += '<a href="' + dayUrl + '" class="cal-active-day" title="' + titleText + '">' +
                                    '<span style="font-size: 12px; line-height: 1.1; font-weight: 800;">' + d + '</span>' +
                                    '<span class="cal-active-count">' + count + '</span>' +
                                '</a>';
                    } else {
                        var sundayClass = isSunday ? ' cal-sunday' : '';
                        html += '<div class="cal-inactive-day' + sundayClass + '">' +
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
