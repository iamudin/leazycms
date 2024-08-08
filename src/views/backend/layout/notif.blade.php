      <!-- @php 
      $surat = DB::table('view_posts')->wherePostType('permohonan')->whereNull('post_pin')->select('post_type','data_field','created_at','post_id')->get();
      @endphp
    <li class="dropdown show" title="Pemberitahuan {{collect($surat)->first()}}">
    
      <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i>  @if(count($surat)>0)<span class="badge badge-warning">{{count($surat)}}</span>@endif</a>
      <ul class="app-notification dropdown-menu dropdown-menu-right">
      @if(count($surat)>0)<li class="app-notification__title">{{count($surat)}} Permohonan baru</li>@endif
        <div class="app-notification__content">
          @forelse($surat as $r)
          <li><a class="app-notification__item" href="{{admin_url('permohonan/edit/'.enc64($r->post_id))}}"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-primary"></i><i class="fa fa-envelope fa-stack-1x fa-inverse"></i></span></span>
              <div>
                <p class="app-notification__message">{{_field($r,'jenis_pelayanan')}}<br><small><i class="fa fa-user" aria-hidden="true"></i> {{_field($r,'nama_pemohon')}}</small></p>
                <p class="app-notification__meta"><small>{{time_ago($r->created_at)}}</small></p>
              </div></a>
          </li>
          
        @empty
        <li><a class="app-notification__item" href="javascript:;">
              <div>
                <p class="app-notification__message">Tidak ada permohonan baru</p>
              </div></a>
          </li>
        @endforelse

        </div>
      </ul>
    </li> -->
    <!-- User Menu-->