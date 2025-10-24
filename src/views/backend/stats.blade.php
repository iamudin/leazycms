    <style>
        body {
            background-color: #f5f6fa;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .stat-card h5 {
            font-size: 15px;
            color: #666;
        }
        .stat-card h3 {
            font-weight: 700;
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
<div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="font-weight-bold">📈 Statistik Pengunjung</h3>

        <form method="GET" class="form-inline">
            <label class="mr-2">Domain:</label>
            <select name="domain" class="form-control" onchange="this.form.submit()">
                <option value="">Semua Domain</option>
                @foreach($domains as $d)
                    <option value="{{ $d }}" {{ $domain == $d ? 'selected' : '' }}>
                        {{ $d }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- =============== STATISTIC CARDS =============== --}}
    <div class="row">
        <div class="col-md-2 mb-3">
            <div class="card stat-card text-center p-3">
                <h5>Online</h5>
                <h3 class="text-success">{{ number_format($onlineVisitors) }}</h3>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card stat-card text-center p-3">
                <h5>Pengunjung Unik</h5>
                <h3>{{ number_format($uniqueVisitors) }}</h3>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card stat-card text-center p-3">
                <h5>View Hari Ini</h5>
                <h3>{{ number_format($pageViewToday) }}</h3>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card stat-card text-center p-3">
                <h5>Kemarin</h5>
                <h3>{{ number_format($pageViewYesterday) }}</h3>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card stat-card text-center p-3">
                <h5>Minggu Ini</h5>
                <h3>{{ number_format($pageViewWeek) }}</h3>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card stat-card text-center p-3">
                <h5>Bulan Ini</h5>
                <h3>{{ number_format($pageViewMonth) }}</h3>
            </div>
        </div>
    </div>

    {{-- =============== CHART =============== --}}
    <div class="card my-4">
        <div class="card-body">
            <h5 class="mb-3 font-weight-bold">📊 Tren 7 Hari Terakhir</h5>
            <canvas id="chartView"></canvas>
        </div>
    </div>

    {{-- =============== TOP TABLES =============== --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3">🔥 Top 10 Page View (200)</h5>
                    <table class="table table-sm table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Halaman</th>
                                <th class="text-right">Total View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPages as $row)
                                <tr>
                                    <td>{{ $row->page }}</td>
                                    <td class="text-right">{{ number_format($row->total_view) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3">⚠️ Top 10 Error 404</h5>
                    <table class="table table-sm table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Halaman</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top404 as $row)
                                <tr>
                                    <td>{{ $row->page }}</td>
                                    <td class="text-right">{{ number_format($row->total_notfound) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- =============== DEVICE & COUNTRY =============== --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3">💻 Distribusi Perangkat</h5>
                    <table class="table table-sm table-striped">
                        <thead class="thead-light">
                            <tr><th>Perangkat</th><th class="text-right">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse($deviceData as $row)
                                <tr><td>{{ $row->device ?? 'Tidak Diketahui' }}</td><td class="text-right">{{ $row->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

               <div class="card mb-4">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3"><i class="fa fa-link"></i> Sumber Trafik</h5>
                    <table class="table table-bordered table-striped table-sm mb-0">
    <thead class="thead-light">
        <tr>
            <th style="width: 5%">#</th>
            <th>Referer</th>
            <th class="text-center">Unique Visitors</th>
            <th class="text-center">Total Views</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($topReferers as $index => $ref)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    @php
                        $display = strlen($ref->reference) > 60 ? substr($ref->reference, 0, 60) . '...' : $ref->reference;
                    @endphp
                    <a href="{{ $ref->reference }}" target="_blank" title="{{ $ref->reference }}">
                        {{ $display }}
                    </a>
                </td>
                <td class="text-center">{{ number_format($ref->unique_visitors) }}</td>
                <td class="text-center">{{ number_format($ref->total_view) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Tidak ada data referer</td>
            </tr>
        @endforelse
    </tbody>
</table>

        </div>
        </div>

       
        <div class="card mb-3">
            <div class="card-header bg-light font-weight-bold">Top Browser</div>
            <div class="card-body ">
                <table class="table table-sm table-striped mb-0">
                    <tbody>
                        @forelse ($browserData as $b)
                            <tr>
                                <td>{{ $b->browser ?: 'Unknown' }}</td>
                                <td class="text-right">{{ number_format($b->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
       
    </div>

        <div class="card mb-3">
            <div class="card-header bg-light font-weight-bold">Top OS</div>
            <div class="card-body ">
                <table class="table table-sm table-striped mb-0">
                    <tbody>
                        @forelse ($osData as $o)
                            <tr>
                                <td>{{ $o->os ?: 'Unknown' }}</td>
                                <td class="text-right">{{ number_format($o->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3">🌍 Distribusi Negara</h5>
                    <table class="table table-sm table-striped">
                        <thead class="thead-light">
                            <tr><th>Negara</th><th class="text-right">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse($countryData as $row)
                                <tr><td>{{ $row->country ?? 'Tidak Diketahui' }}</td><td class="text-right">{{ $row->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- =============== SCRIPT CHART =============== --}}
<script>
    const chartCtx = document.getElementById('chartView').getContext('2d');
    const chartData = {
        labels: @json($chartData->pluck('date')),
        datasets: [{
            label: 'Page View',
            data: @json($chartData->pluck('total')),
            backgroundColor: 'rgba(54, 162, 235, 0.3)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    };
    new Chart(chartCtx, {
        type: 'line',
        data: chartData,
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
</script>
{{-- Chart.js --}}
