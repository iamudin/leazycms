@php
    $statusText = $status_text ?? 'Aktif';
    $statusType = 'info';
    $statusLower = strtolower($statusText);
    if (str_contains($statusLower, 'terima') || str_contains($statusLower, 'lulus') || str_contains($statusLower, 'aktif') || str_contains($statusLower, 'sukses') || str_contains($statusLower, 'publish')) {
        $statusType = 'success';
    } elseif (str_contains($statusLower, 'proses') || str_contains($statusLower, 'tunggu') || str_contains($statusLower, 'review') || str_contains($statusLower, 'pending') || str_contains($statusLower, 'verifikasi')) {
        $statusType = 'warning';
    } elseif (str_contains($statusLower, 'tolak') || str_contains($statusLower, 'gugur') || str_contains($statusLower, 'tidak') || str_contains($statusLower, 'batal')) {
        $statusType = 'danger';
    }
@endphp

<div class="lz-result-card" style="margin-top: 1rem;">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding-bottom: 0.875rem; border-bottom: 1px solid #e2e8f0;">
        <div>
            <span style="font-size: 0.75rem; font-weight: 700; color: #0d9488; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.25rem;">
                Hasil Ditemukan
            </span>
            <h4 style="font-size: 1.125rem; font-weight: 800; margin: 0; color: inherit;">
                {{ $post->title }}
            </h4>
        </div>
        <span class="lz-badge lz-badge-{{ $statusType }}">
            @if($statusType === 'success')
                <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            @elseif($statusType === 'warning')
                <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif($statusType === 'danger')
                <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            @else
                <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @endif
            <span>{{ $statusText }}</span>
        </span>
    </div>

    <table class="lz-track-table">
        <tbody>
            <tr>
                <td class="lz-track-label">Tanggal Masuk:</td>
                <td class="lz-track-val">{{ $post->created_at ? $post->created_at->translatedFormat('d F Y, H:i') : '-' }}</td>
            </tr>
            @if(!empty($post->category))
                <tr>
                    <td class="lz-track-label">Kategori:</td>
                    <td class="lz-track-val">{{ is_object($post->category) ? $post->category->name : $post->category }}</td>
                </tr>
            @endif

            @foreach($fields_data as $fLabel => $fVal)
                @if(!empty($fVal) && !is_array($fVal))
                    <tr>
                        <td class="lz-track-label">{{ $fLabel }}:</td>
                        <td class="lz-track-val">
                            @if(str_starts_with((string)$fVal, 'http://') || str_starts_with((string)$fVal, 'https://'))
                                <a href="{{ $fVal }}" target="_blank" rel="noopener noreferrer" style="color: #0d9488; text-decoration: underline;">
                                    Buka Tautan <i class="fa fa-external-link"></i>
                                </a>
                            @else
                                {{ $fVal }}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    @if(!empty($post->web_url) || !empty($post->url))
        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; text-align: right;">
            <a href="{{ url($post->url) }}" class="lz-track-link" style="font-size: 0.8125rem; font-weight: 700; color: #0d9488; text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem;">
                <span>Lihat Informasi Selengkapnya</span>
                <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    @endif
</div>
