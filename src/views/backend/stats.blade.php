    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📊 Statistik Pengunjung</h3>
        <form method="GET" class="form-inline">
            <label class="mr-2">Filter Domain:</label>
            <select name="domain" class="form-control" onchange="this.form.submit()">
                <option value="">Semua</option>
                @foreach($domains as $d)
                    <option value="{{ $d }}" {{ $domain == $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted">Pengunjung Unik</h5>
                    <h3>{{ number_format($stats->unique_visitors ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted">Total Page Views</h5>
                    <h3>{{ number_format($stats->total_pageviews ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted">Error 404</h5>
                    <h3 class="text-danger">{{ number_format($stats->total_404 ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <strong>Tren Kunjungan 7 Hari Terakhir</strong>
        </div>
        <div class="card-body">
            <canvas id="visitorChart" height="100"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-info text-white">📱 Perangkat</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($deviceData as $d)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $d->device ?: 'Tidak Diketahui' }}
                                <span class="badge badge-primary badge-pill">{{ $d->total }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-success text-white">🌍 Negara</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($countryData as $c)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $c->country ?: 'Tidak Diketahui' }}
                                <span class="badge badge-success badge-pill">{{ $c->total }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('visitorChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData->pluck('date')) !!},
            datasets: [{
                label: 'Kunjungan Harian',
                data: {!! json_encode($chartData->pluck('total')) !!},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>