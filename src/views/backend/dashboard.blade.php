@extends('cms::backend.layout.app', ['title' => 'Dashboard'])
@section('content')
        <div class="row">
        <div class="col-lg-12 mb-1">

            <h3 style="font-weight:normal;"> <i class="fa fa-dashboard"></i> Dashboard </h3>

        </div>
            <div class="col-lg-12">

          <div class="row">
            @foreach($type as $row)
                  <div title="Klik untuk selengkapnya" class="pointer col-md-6 col-lg-3" onclick="location.href='{{Route::has($row->name) ? route($row->name) : ''}}'">
                    <div class="widget-small danger coloured-icon"><i class="icon fa {{$row->icon}} fa-3x"></i>
                      <div class="info pl-3">
                        <p class="mt-2 text-muted">{{$row->title}}</p>
                        <h2><b>{{$posts[$row->name] ?? '0'}}</b></h2>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>

      </div>
        @if(config('modules.view_stats'))
          <div class="col-lg-12 mb-2">
            @includeIf(config('modules.view_stats'))
          </div>
        @endif
        <div class="col-lg-12 mb-3">
          <div class="card" style="padding:15px">
          <h4 for="" style="margin-bottom:20px"><i class="fa fa-pencil" aria-hidden="true"></i> 5 Terakhir Dibuat</h4>
          <div class="table-responsive">
            <table class="table" style="font-size:small">
          <thead><tr>
            <th width="150px">Waktu</th>
            <th width="100px">Modul</th>
            <th>Judul</th>
            <th  width="100px">Author</th>
            <th  width="50px">Status</th>
          </tr></thead>
          <tbody>
            @foreach($latest as $row)
            <tr>
                <td><code>{{ $row->created_at->diffForHumans() }}</code></td>
                <td>{{ str($row->type)->headline() }}</td>
                <td><span class="text-primary">{{$row->title }}</span></td>
                <td>{{ $row->user->name }}</td>
                <td>{!! $row->status == 'draft' ? '<badge class="badge badge-warning">Draft</badge>' : '<badge class="badge badge-success">Publish</badge>' !!}</td>
            </tr>
            @endforeach
          </tbody>
          </table>
          </div>
        </div>
        </div>



        <div class="col-lg-12 mb-3">
                <div class="row mb-4">
                  <div class="col">
                    <h3 class="font-weight-bold">Visitor Dashboard</h3>
                    <small class="text-muted">Domain: {{ $currentDomain }}</small>
                  </div>
                  <div class="col text-right">
                    <form method="GET">
                      <select name="domain" class="form-control" onchange="this.form.submit()">
                        @foreach($domains as $domain)
                          <option value="{{ $domain }}" {{ $domain == $currentDomain ? 'selected' : '' }}>
                            {{ $domain }}
                          </option>
                        @endforeach
                      </select>
                    </form>
                  </div>
                </div>

                <div class="visitor">
             
                </div>


        </div>
      </div>
  @push('scripts')
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        loadVisitor("domain={{ urlencode($currentDomain) }}");

      });

      function loadVisitor(domain=null){

        const url = "{{ route('panel.dashboard') }}?view_visitor=true&"+domain; // ganti dengan URL milikmu
        document.querySelectorAll('.visitor').forEach(el => {
          el.innerHTML = `
            <div class="text-center p-2">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `;
        });
        fetch(url)
          .then(response => response.text())
          .then(data => {
            document.querySelectorAll('.visitor').forEach(el => {
              el.innerHTML = data;
            });
          })
          .catch(err => console.error("Load visitor error:", err));
      }
    </script>

  @endpush
@endsection
