<div class="container-fluid mt-2 px-0">
    @php
        $showDomain = $showDomain ?? (config('modules.multisite_enabled') && is_main_domain() && empty($domain));
        $months = $months ?? [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $selectedMonth = $selectedMonth ?? request('month', date('m'));
        $selectedYear = $selectedYear ?? (int) request('year', date('Y'));
        $availableYears = $availableYears ?? [date('Y')];

        $chartLabels = $pageChart->map(function($p) use ($selectedMonth, $months) {
            if ($selectedMonth === 'all') {
                $parts = explode('-', $p->date);
                $m = $parts[1] ?? '';
                return ($months[$m] ?? $m) . (isset($parts[0]) ? ' ' . $parts[0] : '');
            }
            return date('d M', strtotime($p->date));
        });
    @endphp

    {{-- HEADER + FILTER (BULAN, TAHUN, DOMAIN) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                <div>
                    <h5 class="font-weight-bold m-0 text-dark" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-chart-line text-primary"></i> Analytics Dashboard
                    </h5>
                    <div class="text-muted mt-1" style="font-size: 12px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <span>
                            <i class="fa fa-calendar-o mr-1"></i> Periode: 
                            <strong class="text-dark">
                                @if($selectedMonth === 'all')
                                    Tahun {{ $selectedYear }} (Semua Bulan)
                                @else
                                    {{ $months[$selectedMonth] ?? $selectedMonth }} {{ $selectedYear }}
                                @endif
                            </strong>
                        </span>
                        @if(isset($totalViews))
                            <span class="badge badge-light border text-primary" style="font-size: 11px; font-weight: 600;">
                                <i class="fa fa-eye mr-1"></i> {{ number_format($totalViews) }} Page Views
                            </span>
                        @endif
                        @if(isset($uniquePeriod))
                            <span class="badge badge-light border text-success" style="font-size: 11px; font-weight: 600;">
                                <i class="fa fa-users mr-1"></i> {{ number_format($uniquePeriod) }} Pengunjung Unik
                            </span>
                        @endif
                        @if(!empty($domain))
                            <span class="badge badge-light border text-secondary" style="font-size: 11px; font-weight: 600;">
                                <i class="fa fa-globe mr-1"></i> {{ $domain }}
                            </span>
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ route('panel.dashboard') }}" class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    {{-- Filter Bulan --}}
                    <div class="input-group input-group-sm" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-muted border-right-0" style="border-radius: 8px 0 0 8px; font-size: 12px;" title="Filter Bulan">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                        <select name="month" onchange="this.form.submit()" class="form-control form-control-sm border-left-0" style="border-radius: 0 8px 8px 0; font-size: 12px; font-weight: 500; min-width: 130px; height: 32px;" title="Pilih Bulan">
                            <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach($months as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>
                                    {{ $mName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tahun --}}
                    <div class="input-group input-group-sm" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-muted border-right-0" style="border-radius: 8px 0 0 8px; font-size: 12px;" title="Filter Tahun">
                                <i class="fa fa-clock-o"></i>
                            </span>
                        </div>
                        <select name="year" onchange="this.form.submit()" class="form-control form-control-sm border-left-0" style="border-radius: 0 8px 8px 0; font-size: 12px; font-weight: 500; min-width: 90px; height: 32px;" title="Pilih Tahun">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Domain --}}
                    @if(isset($domains) && count($domains) > 0)
                        <div class="input-group input-group-sm" style="width: auto;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light text-muted border-right-0" style="border-radius: 8px 0 0 8px; font-size: 12px;" title="Filter Domain">
                                    <i class="fa fa-globe"></i>
                                </span>
                            </div>
                            <select name="domain" onchange="this.form.submit()" class="form-control form-control-sm border-left-0" style="border-radius: 0 8px 8px 0; font-size: 12px; font-weight: 500; min-width: 140px; height: 32px;" title="Pilih Domain">
                                <option value="">Semua Domain</option>
                                @foreach($domains as $d)
                                    <option value="{{ $d }}" {{ $domain == $d ? 'selected' : '' }}>
                                        {{ $d }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(request()->has('month') || request()->has('year') || request()->has('domain'))
                        <a href="{{ route('panel.dashboard') }}" class="btn btn-sm btn-light border text-secondary" style="font-size: 11px; font-weight: 600; border-radius: 8px; padding: 4px 10px; height: 32px; display: inline-flex; align-items: center;" title="Reset Filter">
                            <i class="fa fa-refresh mr-1"></i> Reset
                        </a>
                    @endif
                </form>

            </div>
        </div>
    </div>


    {{-- STATS --}}
    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <small>Realtime Visitors</small>
                    <h3>{{$realtimeVisitors}}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-purple shadow" style="background:#6f42c1">
                <div class="card-body">
                    <small>Unique Today</small>
                    <h3>{{$uniqueToday}}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <small>Top Pages</small>
                    <h3>{{$topPages->count()}}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <small>Keywords</small>
                    <h3>{{$topKeywords->count()}}</h3>
                </div>
            </div>
        </div>

    </div>


    {{-- CHART --}}
    <div class="row">

        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-line-chart text-primary mr-1"></i> Page Views</span>
                    <small class="badge badge-light border font-weight-normal text-muted" style="font-size: 11px;">
                        {{ $selectedMonth === 'all' ? 'Tahun ' . $selectedYear : ($months[$selectedMonth] ?? $selectedMonth) . ' ' . $selectedYear }}
                    </small>
                </div>
                <div class="card-body">
                    <canvas id="pageChart"></canvas>
                </div>
            </div>
        </div>
            <div class="col-md-4 mb-4">

                <div class="card shadow">

                    <div class="card-header font-weight-bold">
                        Device Distribution
                    </div>

                    <div class="card-body">

                        <canvas id="deviceChart"></canvas>

                    </div>

                </div>

            </div>



    </div>


    {{-- TABLES --}}
    <div class="row">

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header font-weight-bold">
                    Top Pages
                </div>

                <ul class="list-group list-group-flush">

                    @foreach($topPages as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                @if($showDomain && isset($p->domain))
                                    <small class="text-muted d-block">{{$p->domain}}</small>
                                @endif
                                {{$p->key}}
                            </span>
                            <strong>{{$p->total}}</strong>
                        </li>
                    @endforeach

                </ul>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header font-weight-bold">
                    Top Keywords
                </div>

                <ul class="list-group list-group-flush">

                    @foreach($topKeywords as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                @if($showDomain && isset($p->domain))
                                    <small class="text-muted d-block">{{$p->domain}}</small>
                                @endif
                                {{$p->key}}
                            </span>
                            <strong>{{$p->total}}</strong>
                        </li>
                    @endforeach

                </ul>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header font-weight-bold">
                    Referrers
                </div>

                <ul class="list-group list-group-flush">

                    @foreach($topReferrers as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                @if($showDomain && isset($p->domain))
                                    <small class="text-muted d-block">{{$p->domain}}</small>
                                @endif
                                {{$p->key}}
                            </span>
                            <strong>{{$p->total}}</strong>
                        </li>
                    @endforeach

                </ul>
            </div>
        </div>

    </div>
    <div class="row">


        <div class="col-md-12">

            <div class="card shadow mb-4">

                <div class="card-header font-weight-bold">
                    Realtime Visitors (Last 5 Minutes)
                </div>

                <div class="card-body p-0">
        <div class="table-responsive">

                    <table class="table table-sm table-striped mb-0">

                        <thead class="thead-light">
                            <tr>
                                @if($showDomain)
                                    <th>Domain</th>
                                @endif
                                <th>Page</th>
                                <th>Device</th><th>IP</th>
                                <th>Referer</th><th>UA</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($realtimeList as $v)

                                <tr>
                                    @if($showDomain)
                                        <td>{{$v->domain}}</td>
                                    @endif
                                    <td>{{$v->current_page}}</td>
                                    <td>{{$v->device}}</td>
                                    <td>{{$v->ip}}</td>
                                    <td>{{$v->referrer}}</td><td>{{$v->user_agent}}</td>
                                    <td>{{$v->last_seen_at}}</td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="{{$showDomain ? 7 : 6}}" class="text-center">No visitor online</td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>
                </div>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const deviceChart = new Chart(document.getElementById('deviceChart'), {

        type: 'doughnut',

        data: {

            labels: {!! json_encode($deviceSummary->pluck('key')) !!},

            datasets: [{

                data: {!! json_encode($deviceSummary->pluck('total')) !!},

                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107'
                ]

            }]

        }

    });
    const pageChart = new Chart(document.getElementById('pageChart'), {

        type: 'line',

        data: {

            labels: {!! json_encode($chartLabels) !!},

            datasets: [{

                label: 'Page Views',

                data: {!! json_encode($pageChart->pluck('total')) !!},

                borderColor: '#007bff',

                backgroundColor: 'rgba(0,123,255,0.2)',

                tension: 0.4

            }]

        }

    });



</script>
