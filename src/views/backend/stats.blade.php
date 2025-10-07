<div class="row mt-3">
    <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm border-left-primary">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Pengunjung</h6>
                <h4 class="font-weight-bold text-primary">{{ $stats['total_visitors'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm border-left-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">Pengunjung Unik</h6>
                <h4 class="font-weight-bold text-success">{{ $stats['unique_visitors'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm border-left-info">
            <div class="card-body">
                <h6 class="text-muted mb-1">Hari Ini</h6>
                <h4 class="font-weight-bold text-info">{{ $stats['today_visitors'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm border-left-danger">
            <div class="card-body">
                <h6 class="text-muted mb-1">Error 404</h6>
                <h4 class="font-weight-bold text-danger">{{ $stats['total_404'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Grafik & Pie -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="font-weight-bold text-primary mb-0">Tren Kunjungan 7 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <canvas id="visitorTrend" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="font-weight-bold text-primary mb-0">Perangkat Pengunjung</h6>
            </div>
            <div class="card-body">
                <canvas id="deviceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Lokasi & Browser -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="font-weight-bold text-primary mb-0">Negara / Kota Teratas</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Negara</th>
                            <th>Kota</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_countries'] ?? [] as $row)
                            <tr>
                                <td>{{ $row->country ?? '-' }}</td>
                                <td>{{ $row->city ?? '-' }}</td>
                                <td>{{ $row->total ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="font-weight-bold text-primary mb-0">Browser Terpopuler</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Browser</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_browsers'] ?? [] as $row)
                            <tr>
                                <td>{{ ucfirst($row->browser) }}</td>
                                <td>{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Grafik Tren Pengunjung
    const ctxTrend = document.getElementById('visitorTrend').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: @json($stats['trend_labels'] ?? []),
            datasets: [{
                label: 'Jumlah Pengunjung',
                data: @json($stats['trend_values'] ?? []),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Pie Chart Perangkat
    const ctxDevice = document.getElementById('deviceChart').getContext('2d');
    new Chart(ctxDevice, {
        type: 'pie',
        data: {
            labels: @json(array_column($stats['top_devices'] ?? [], 'device')),
            datasets: [{
                data: @json(array_column($stats['top_devices'] ?? [], 'total')),
                backgroundColor: ['#007bff', '#28a745', '#ffc107']
            }]
        },
        options: { responsive: true }
    });
</script>