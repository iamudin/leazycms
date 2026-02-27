    <style>
        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #1cc88a, #13855c);
        }

        .bg-gradient-warning {
            background: linear-gradient(45deg, #f6c23e, #dda20a);
        }

        .card {
            border-radius: 12px;
        }

        select.form-control {
            border-radius: 30px;
            padding: 5px 15px;
        }
    </style>


        {{-- ================= DOMAIN SWITCHER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <h3 class="font-weight-bold text-dark mb-0">
                Statistik Domain:
                <span class="text-primary">{{ $currentDomain }}</span>
            </h3>

            <form method="GET" class="form-inline">
                <label class="mr-2 font-weight-bold">Pilih Domain:</label>
                <select name="domain" class="form-control shadow-sm" onchange="this.form.submit()">
                    @foreach($domains as $domain)
                        <option value="{{ $domain }}" {{ $currentDomain == $domain ? 'selected' : '' }}>
                            {{ $domain }}
                        </option>
                    @endforeach
                </select>
            </form>

        </div>

        {{-- ================= SUMMARY CARDS ================= --}}
        <div class="row">

            <!-- Visitor Today -->
            <div class="col-md-4 mb-3">
                <div class="card shadow border-0 bg-gradient-primary text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase">Visitor Hari Ini</h6>
                        <h3 class="font-weight-bold mb-0">
                            {{ number_format($today->total ?? 0) }}
                        </h3>
                        <small>Unique: {{ number_format($today->unique ?? 0) }}</small>
                    </div>
                </div>
            </div>

            <!-- Online Now -->
            <div class="col-md-4 mb-3">
                <div class="card shadow border-0 bg-gradient-success text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase">Online Sekarang</h6>
                        <h3 class="font-weight-bold mb-0">
                            {{ number_format($online ?? 0) }}
                        </h3>
                        <small>Realtime 5 menit terakhir</small>
                    </div>
                </div>
            </div>

            <!-- Total 30 Days -->
            <div class="col-md-4 mb-3">
                <div class="card shadow border-0 bg-gradient-warning text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase">Total 30 Hari</h6>
                        <h3 class="font-weight-bold mb-0">
                            {{ number_format($last30->sum('total')) }}
                        </h3>
                        <small>Akumulasi traffic</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= CHART ================= --}}
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-dark font-weight-bold">
                    Grafik Visitor 30 Hari Terakhir
                </h5>
            </div>
            <div class="card-body">
                <canvas id="chart" height="100"></canvas>
            </div>
        </div>

        {{-- ================= ONLINE RANKING ================= --}}
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-dark font-weight-bold">
                    Ranking Online Semua Domain
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Domain</th>
                            <th class="text-right">Online</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ranking as $index => $r)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $r->domain }}</td>
                                <td class="text-right">
                                    <span class="badge badge-success px-3 py-2">
                                        {{ $r->total }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    Tidak ada data online
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>



    {{-- ================= CHART JS ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('chart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($last30->pluck('date')) !!},
                datasets: [{
                    label: 'Visitor',
                    data: {!! json_encode($last30->pluck('total')) !!},
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderColor: '#4e73df',
                    borderWidth: 3,
                    pointBackgroundColor: '#4e73df',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    </script>
