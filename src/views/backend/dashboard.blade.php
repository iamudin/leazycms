@extends('cms::backend.layout.app', ['title' => 'Dashboard'])
@section('content')
  <div class="row">
    <div class="col-lg-12 mb-3">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h3 style="font-weight:normal; margin: 0;">
          <i class="fa fa-dashboard"></i> Dashboard
        </h3>

        @auth
          @php
            $hour = date('H');
            if ($hour < 11) {
              $greeting = 'Selamat Pagi';
              $icon = 'fa-sun-o text-warning';
            } elseif ($hour < 15) {
              $greeting = 'Selamat Siang';
              $icon = 'fa-sun-o text-warning';
            } elseif ($hour < 18) {
              $greeting = 'Selamat Sore';
              $icon = 'fa-cloud text-info';
            } else {
              $greeting = 'Selamat Malam';
              $icon = 'fa-moon-o text-primary';
            }
            $loginAt = auth()->user()->last_login_at ? auth()->user()->last_login_at->timestamp : time();
            $diffSeconds = max(0, time() - $loginAt);
          @endphp
          <style>
            .dashboard-greeting {
              font-size: 13px;
              background: white;
              color: #333;
              padding: 8px 20px;
              border-radius: 50px;
              box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
              display: flex;
              align-items: center;
              gap: 12px;
              border: 1px solid #eee;
            }

            .dashboard-greeting .separator {
              opacity: 0.3;
            }

            @media (max-width: 768px) {
              .dashboard-greeting {
                border-radius: 15px;
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                width: 100%;
              }

              .dashboard-greeting .separator {
                display: none;
              }
            }
          </style>
          <div class="dashboard-greeting">
            <span><i class="fa {{ $icon }}"></i> {{ $greeting }}, <strong>{{ auth()->user()->name }}</strong>!</span>
            <span class="separator">|</span>

            <span title="Durasi sesi aktif" class="text-success">
              <i class="fa fa-user-clock"></i> Sesi Aktif: <strong id="session-duration"
                data-diff="{{ $diffSeconds }}">00:00:00</strong>
            </span>
            <span class="separator">|</span>
            <span class="text-muted"><i class="fa fa-network-wired"></i> IP: {{ get_client_ip() ?? 'N/A' }}</span>
          </div>
        @endauth
      </div>
    </div>
    <div class="col-lg-12">

      <div class="row">
        @php
            $cardColors = [
                ['bg' => 'linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%)'], // Blue
                ['bg' => 'linear-gradient(135deg, #198754 0%, #146c43 100%)'], // Green
                ['bg' => 'linear-gradient(135deg, #6f42c1 0%, #59359a 100%)'], // Purple
                ['bg' => 'linear-gradient(135deg, #fd7e14 0%, #e85d04 100%)'], // Orange
                ['bg' => 'linear-gradient(135deg, #d63384 0%, #a10b54 100%)'], // Pink
                ['bg' => 'linear-gradient(135deg, #0dcaf0 0%, #087990 100%)'], // Cyan
                ['bg' => 'linear-gradient(135deg, #dc3545 0%, #b02a37 100%)'], // Red
                ['bg' => 'linear-gradient(135deg, #20c997 0%, #178a68 100%)'], // Teal
                ['bg' => 'linear-gradient(135deg, #6610f2 0%, #520dc2 100%)'], // Indigo
                ['bg' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)'], // Amber
                ['bg' => 'linear-gradient(135deg, #495057 0%, #343a40 100%)'], // Dark Gray
                ['bg' => 'linear-gradient(135deg, #e83e8c 0%, #c21a68 100%)'], // Hot Pink
                ['bg' => 'linear-gradient(135deg, #087990 0%, #055160 100%)'], // Deep Cyan
                ['bg' => 'linear-gradient(135deg, #b02a37 0%, #842029 100%)'], // Deep Red
                ['bg' => 'linear-gradient(135deg, #146c43 0%, #0f5132 100%)'], // Deep Green
                ['bg' => 'linear-gradient(135deg, #a0522d 0%, #8b4513 100%)'], // Saddle Brown
                ['bg' => 'linear-gradient(135deg, #17a2b8 0%, #117a8b 100%)'], // Info Blue
                ['bg' => 'linear-gradient(135deg, #d946ef 0%, #c026d3 100%)'], // Fuchsia
                ['bg' => 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)'], // Light Blue
                ['bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)'], // Emerald Green
            ];
        @endphp
        @foreach(collect($type ?? []) as $row)
          @php
            $color = $cardColors[$loop->index % count($cardColors)];
          @endphp
          <div title="Klik untuk selengkapnya" class="pointer col-6 col-md-4 col-lg-3 mb-3"
            onclick="location.href='{{Route::has($row->name) ? route($row->name) : null}}'">
            <div style="position: relative; overflow: hidden; background: {{ $color['bg'] }}; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
              <h4 class="counter-effect" data-target="{{$posts[$row->name] ?? '0'}}" style="margin: 0; font-weight: bold; font-size: 26px; color: #ffffff; position: relative; z-index: 2;">0</h4>
              <p style="margin: 5px 0 0; font-size: 14px; color: rgba(255,255,255,0.85); position: relative; z-index: 2;">{{$row->title}}</p>
              
              <i class="fa {{$row->icon}}" style="position: absolute; right: -5px; bottom: -10px; font-size: 70px; color: rgba(255,255,255,0.2); z-index: 0; transform: rotate(-15deg);"></i>
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
    <div class="col-lg-12 mb-4">
      <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 14px; border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom: 1px solid #f1f5f9;">
          <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background: rgba(13, 110, 253, 0.08); color: #0d6efd; font-size: 16px;">
              <i class="fa fa-pencil-square-o"></i>
            </div>
            <div>
              <h5 class="m-0 font-weight-bold" style="font-weight: 700; color: #0f172a; font-size: 15px;">5 Konten Terakhir Dibuat</h5>
              <small class="text-muted" style="font-size: 11px;">Aktivitas penulisan dan pembaruan konten terbaru oleh tim editor.</small>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="{{ route('panel.dashboard') }}" class="btn btn-sm btn-light border text-secondary" style="font-size: 11px; font-weight: 600; border-radius: 8px; padding: 5px 12px;" title="Refresh Data">
              <i class="fa fa-refresh mr-1"></i> Refresh
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
            <thead style="background-color: #f8fafc; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #64748b;">
              <tr>
                <th style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; width: 140px;">Waktu</th>
                <th style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; width: 130px;">Modul</th>
                <th style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">Judul Konten</th>
                <th style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; width: 140px;">Pembuat</th>
                <th style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; width: 100px; text-align: center;">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($latest as $row)
                @php
                  $modInfo = get_module($row->type);
                  $icon = $modInfo->icon ?? 'fa-file-text-o';
                  $editRoute = Route::has($row->type . '.show') ? route($row->type . '.show', $row->id) : (Route::has($row->type) ? route($row->type) : '#');
                @endphp
                <tr style="transition: background-color 0.15s ease-in-out;">
                  <td style="padding: 12px 16px; vertical-align: middle;">
                    <span class="badge bg-light text-dark border" style="font-weight: 500; font-size: 11px; padding: 5px 8px; border-radius: 6px;" title="{{ $row->created_at->format('d M Y H:i') }}">
                      <i class="fa fa-clock-o text-muted mr-1"></i> {{ $row->created_at->diffForHumans() }}
                    </span>
                  </td>
                  <td style="padding: 12px 16px; vertical-align: middle;">
                    <span class="d-inline-flex align-items-center gap-1 font-weight-bold" style="font-size: 11px; color: #334155; background: #f1f5f9; padding: 4px 10px; border-radius: 20px; border: 1px solid #e2e8f0;">
                      <i class="fa {{ $icon }} text-primary mr-1"></i> {{ str($row->type)->headline() }}
                    </span>
                  </td>
                  <td style="padding: 12px 16px; vertical-align: middle;">
                    <span  class="font-weight-bold text-decoration-none" style="font-weight: 600; color: #0f172a; text-decoration: none;" onmouseover="this.style.color='#0d6efd';" onmouseout="this.style.color='#0f172a';" >
                      {{ $row->title }}
                    </span>
                  </td>
                  <td style="padding: 12px 16px; vertical-align: middle;">
                    <div class="d-flex align-items-center gap-2">
                     
                      <span class="text-truncate" style="max-width: 110px; color: #334155; font-weight: 500;">
                        {{ $row->user?->name ?? 'Admin' }}<br><small class="badge badge-success">{{ ucfirst($row->user->level) }}</small>
                      </span>
                    </div>
                  </td>
                  <td style="padding: 12px 16px; vertical-align: middle; text-align: center;">
                    @if($row->status == 'draft')
                      <span class="badge" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 5px 10px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                        <i class="fa fa-circle text-warning mr-1" style="font-size: 7px;"></i> Draft
                      </span>
                    @else
                      <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 5px 10px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                        <i class="fa fa-check-circle text-success mr-1"></i> Publish
                      </span>
                    @endif
                  </td>
             
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fa fa-folder-open-o fa-2x mb-2 d-block opacity-50"></i>
                    Belum ada konten yang dibuat.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>



    <div class="col-lg-12 mb-3">
      @include('cms::backend.visitorchart')

    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sessionEl = document.getElementById('session-duration');
        if (sessionEl) {
          let diff = parseInt(sessionEl.getAttribute('data-diff')) || 0;

          const updateSessionTime = () => {     
            const h = String(Math.floor(diff / 3600)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            sessionEl.innerHTML = `${h}:${m}:${s}`;
          };
          updateSessionTime();

          setInterval(() => {
            diff++;
            updateSessionTime();
          }, 1000);
        }
        const counters = document.querySelectorAll('.counter-effect');
        counters.forEach(counter => {
          const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const speed = 20;
            const inc = target / speed;

            if (count < target) {
              counter.innerText = Math.ceil(count + inc);
              setTimeout(updateCount, 40);
            } else {
              counter.innerText = target;
            }
          };
          updateCount();
        });
      });
    </script>
  @endpush
@endsection