
    <li class="dropdown" title="Pemberitahuan">
        @php
        $total_notifikasi = notifications()->get_unread_notifications();
        @endphp
      <a class="app-nav__item" href="javascript:void(0)" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i>
        @if($total_notifikasi->count()) <span class="badge badge-warning">{{$total_notifikasi->count()}}</span>@endif
    </a>
      <ul class="app-notification dropdown-menu dropdown-menu-right">
      @if($total_notifikasi->count()) <li class="app-notification__title">{{$total_notifikasi->count()}} Pemberitahun baru</li>@endif
        <div class="app-notification__content">
          @forelse($total_notifikasi as $r)
          <li><a class="app-notification__item" href="{{ route('notifreader',$r->id) }}"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
              <div>
                <p class="app-notification__message">{{ $r->message }}</p>
                <p class="app-notification__meta"><small>{{$r->created_at->diffForHumans()}}</small></p>
              </div></a>
          </li>

        @empty
        <li><a class="app-notification__item" href="javascript:void(0)">
              <div>
                <p class="app-notification__message">Tidak ada pemberitahuan</p>
              </div></a>
          </li>
        @endforelse

        </div>
      </ul>
    </li>