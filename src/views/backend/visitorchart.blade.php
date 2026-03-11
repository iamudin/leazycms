<div class="container-fluid mt-4">

    {{-- HEADER + DOMAIN SWITCH --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h4 class="font-weight-bold">Analytics Dashboard Monthly</h4>

        <form method="GET">
            <select name="domain" onchange="this.form.submit()" class="form-control">

                <option value="">All Domains</option>

                @foreach($domains as $d)
                    <option value="{{$d}}" {{$domain == $d ? 'selected' : ''}}>
                        {{$d}}
                    </option>
                @endforeach

            </select>
        </form>

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
                <div class="card-header font-weight-bold">
                    Page Views
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
                            <span>{{$p->key}}</span>
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
                            <span>{{$p->key}}</span>
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
                            <span>{{$p->key}}</span>
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
    
                    <table class="table table-sm table-striped mb-0">
    
                        <thead class="thead-light">
                            <tr>
                                <th>Page</th>
                                <th>Device</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
    
                        <tbody>
    
                            @forelse($realtimeList as $v)

                                <tr>
                                    <td>{{$v->current_page}}</td>
                                    <td>{{$v->device}}</td>
                                    <td>{{$v->last_seen_at}}</td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="text-center">No visitor online</td>
                                </tr>

                            @endforelse
    
                        </tbody>
    
                    </table>
    
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

            labels: {!! json_encode($pageChart->pluck('date')) !!},

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