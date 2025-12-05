        {{-- HEADER --}}
 


        {{-- ROW 1: USER ONLINE + PAGE VIEWS --}}
        <div class="row">

            {{-- USER ONLINE --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <strong>User Online ({{ count($onlineUsers) }})</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Page</th>
                                    <th>IP</th>
                                    <th>Browser</th>
                                    <th>Location</th>
                                    <th>Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($onlineUsers as $v)
                                    <tr>
                                        <td>{{ $v->lastLog->page ?? '-' }}</td>
                                        <td>{{ $v->ip }}</td>
                                        <td>{{ $v->browser }}</td>
                                        <td>{{ $v->city }}, {{ $v->country }}</td>
                                        <td>{{ $v->lastLog->reference ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada user online.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            {{-- PAGE VIEWS --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong>Page Views</strong>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">

                            <div class="col-6 mb-3">
                                <div class="p-3 border rounded">
                                    <h5>Hari ini</h5>
                                    <h3 class="text-primary">{{ $pv_today }}</h3>
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                <div class="p-3 border rounded">
                                    <h5>Kemarin</h5>
                                    <h3 class="text-info">{{ $pv_yesterday }}</h3>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-3 border rounded">
                                    <h5>Minggu ini</h5>
                                    <h3 class="text-success">{{ $pv_week }}</h3>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-3 border rounded">
                                    <h5>Bulan ini</h5>
                                    <h3 class="text-warning">{{ $pv_month }}</h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>


        {{-- ROW 2: TOP 10 PAGES TODAY --}}
        <div class="row">
            <div class="col-md-6">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <strong>Top 10 Pages Today (200 OK)</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Page</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_pages as $p)
                                    <tr>
                                        <td>{{ $p->page }}</td>
                                        <td><strong>{{ $p->total }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Belum ada data hari ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>


            {{-- LIST 404 --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <strong>404 Logs</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Page</th>
                                    <th>IP</th>
                                    <th>Browser</th>
                                    <th>Location</th>
                                    <th>Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs_404 as $log)
                                    <tr>
                                        <td>{{ $log->page }}</td>
                                        <td>{{ $log->visitor->ip ?? '-' }}</td>
                                        <td>{{ $log->visitor->browser ?? '-' }}</td>
                                        <td>{{ ($log->visitor->city ?? '-') . ', ' . ($log->visitor->country ?? '-') }}</td>
                                        <td>{{ $log->reference ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data 404.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

