@php
    $notice = $notice ?? (function_exists('get_master_tenant_notice') ? get_master_tenant_notice() : null);
    $isPreview = $isPreview ?? false;
@endphp

@if(!empty($notice) || $isPreview)
@php
    $noticeType = $notice['type'] ?? 'warning';
    
    // Theme configurations
    $themes = [
        'warning' => [
            'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
            'glow'     => 'rgba(245, 158, 11, 0.35)',
            'badge'    => 'Pemberitahuan Penting',
            'badge_bg' => 'rgba(255, 255, 255, 0.25)',
            'icon'     => !empty($notice['icon']) ? $notice['icon'] : 'fa-database',
            'btn'      => 'background: #d97706; border-color: #b45309; color: #fff;',
        ],
        'danger' => [
            'gradient' => 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)',
            'glow'     => 'rgba(239, 68, 68, 0.35)',
            'badge'    => 'Peringatan Sistem',
            'badge_bg' => 'rgba(255, 255, 255, 0.25)',
            'icon'     => !empty($notice['icon']) ? $notice['icon'] : 'fa-shield',
            'btn'      => 'background: #dc2626; border-color: #b91c1c; color: #fff;',
        ],
        'info' => [
            'gradient' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
            'glow'     => 'rgba(2, 132, 199, 0.35)',
            'badge'    => 'Informasi Tenant',
            'badge_bg' => 'rgba(255, 255, 255, 0.25)',
            'icon'     => !empty($notice['icon']) ? $notice['icon'] : 'fa-info-circle',
            'btn'      => 'background: #0284c7; border-color: #0369a1; color: #fff;',
        ],
        'success' => [
            'gradient' => 'linear-gradient(135deg, #10b981 0%, #047857 100%)',
            'glow'     => 'rgba(16, 185, 129, 0.35)',
            'badge'    => 'Pengumuman Resmi',
            'badge_bg' => 'rgba(255, 255, 255, 0.25)',
            'icon'     => !empty($notice['icon']) ? $notice['icon'] : 'fa-check-circle',
            'btn'      => 'background: #059669; border-color: #047857; color: #fff;',
        ],
        'primary' => [
            'gradient' => 'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)',
            'glow'     => 'rgba(79, 70, 229, 0.35)',
            'badge'    => 'Pesan Administrator Pusat',
            'badge_bg' => 'rgba(255, 255, 255, 0.25)',
            'icon'     => !empty($notice['icon']) ? $notice['icon'] : 'fa-bullhorn',
            'btn'      => 'background: #4f46e5; border-color: #3730a3; color: #fff;',
        ],
    ];

    $curTheme = $themes[$noticeType] ?? $themes['warning'];
    $iconName = !empty($notice['icon']) ? $notice['icon'] : $curTheme['icon'];
    if (!str_starts_with($iconName, 'fa-') && !str_starts_with($iconName, 'fa ')) {
        $iconName = 'fa-' . $iconName;
    }
    if (!str_contains($iconName, 'fa ')) {
        $iconName = 'fa ' . $iconName;
    }
@endphp

<style>
    #tenantNoticeDashboardModal .modal-content {
        border-radius: 18px;
        border: none;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
    }
    #tenantNoticeDashboardModal .modal-header {
        border-bottom: none;
        padding: 24px 28px 20px;
        position: relative;
    }
    #tenantNoticeDashboardModal .notice-icon-container {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        border: 1.5px solid rgba(255, 255, 255, 0.35);
        flex-shrink: 0;
        animation: tenantNoticePulse 2.5s infinite;
    }
    @keyframes tenantNoticePulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    #tenantNoticeDashboardModal .notice-content {
        color: #334155;
        font-size: 14.5px;
        line-height: 1.68;
    }
    #tenantNoticeDashboardModal .notice-content p {
        margin-bottom: 12px;
    }
    #tenantNoticeDashboardModal .notice-content p:last-child {
        margin-bottom: 0;
    }
    #tenantNoticeDashboardModal .notice-content ul,
    #tenantNoticeDashboardModal .notice-content ol {
        padding-left: 20px;
        margin-bottom: 14px;
    }
    #tenantNoticeDashboardModal .notice-content li {
        margin-bottom: 6px;
    }
    #tenantNoticeDashboardModal .notice-content a {
        color: #2563eb;
        text-decoration: underline;
        font-weight: 500;
    }
    #tenantNoticeDashboardModal .notice-content a:hover {
        color: #1d4ed8;
    }
    #tenantNoticeDashboardModal .notice-content strong {
        color: #0f172a;
    }
    #tenantNoticeDashboardModal .modal-footer {
        background-color: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 14px 28px;
    }
</style>

<!-- Modal Popup Pengumuman Tenant -->
<div class="modal fade" id="tenantNoticeDashboardModal" tabindex="-1" role="dialog" aria-labelledby="tenantNoticeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 680px;">
        <div class="modal-content">
            <!-- Header Modal Berwarna Gradient -->
            <div class="modal-header d-flex align-items-center" id="tenantNoticeHeader" style="background: {{ $curTheme['gradient'] }}; color: #ffffff;">
                <div class="d-flex align-items-center gap-3" style="gap: 16px; width: 100%;">
                    <div class="notice-icon-container" id="tenantNoticeIconWrapper">
                        <i class="{{ $iconName }}" id="tenantNoticeIcon"></i>
                    </div>
                    <div style="flex-grow: 1; min-width: 0;">
                        <div class="d-flex align-items-center gap-2 mb-1" style="gap: 8px;">
                            <span class="badge" id="tenantNoticeBadge" style="background: {{ $curTheme['badge_bg'] }}; color: #ffffff; font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; letter-spacing: 0.3px;">
                                <i class="fa fa-info-circle mr-1"></i> <span id="tenantNoticeBadgeText">{{ $curTheme['badge'] }}</span>
                            </span>
                            @if(config('modules.multisite_enabled'))
                            <span class="text-white-50" style="font-size: 11px;"><i class="fa fa-globe mr-1"></i> Multi-site</span>
                            @endif
                        </div>
                        <h5 class="modal-title font-weight-bold" id="tenantNoticeTitle" style="font-size: 1.18rem; line-height: 1.35; margin: 0; color: #ffffff; text-shadow: 0 1px 2px rgba(0,0,0,0.12);">
                            {{ $notice['title'] ?? 'Pemberitahuan Sistem' }}
                        </h5>
                    </div>
                    <button type="button" class="close text-white ml-2" onclick="dismissTenantNotice(true)" aria-label="Close" style="opacity: 0.85; text-shadow: none; font-size: 26px; padding: 0 5px; outline: none; margin-top: -15px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Body Modal -->
            <div class="modal-body p-4" style="background: #ffffff; max-height: 65vh; overflow-y: auto;">
                <div class="notice-content" id="tenantNoticeBody">
                    {!! $notice['content'] ?? '<p>Tidak ada pesan pengumuman.</p>' !!}
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2" style="gap: 12px;">
                <div class="custom-control custom-checkbox text-left">
                    <input type="checkbox" class="custom-control-input" id="tenantNoticeDontShowToday">
                    <label class="custom-control-label text-muted" for="tenantNoticeDontShowToday" style="font-size: 12.5px; cursor: pointer; user-select: none;">
                        Jangan tampilkan lagi hari ini
                    </label>
                </div>
                <div class="d-flex align-items-center gap-2" style="gap: 8px;">
                    <button type="button" class="btn btn-secondary btn-sm" id="tenantNoticeCloseBtn" onclick="dismissTenantNotice(true)" style="border-radius: 8px; font-size: 13px; font-weight: 500; padding: 7px 18px; box-shadow: none;">
                        <span id="tenantNoticeCloseBtnText">{{ $notice['close_btn_text'] ?? 'Saya Mengerti' }}</span>
                    </button>
                    
                    @php
                        $hasAction = !empty($notice['action_btn_text']) && !empty($notice['action_btn_url']);
                    @endphp
                    <a href="{{ $notice['action_btn_url'] ?? '#' }}" 
                       target="{{ $notice['action_btn_target'] ?? '_blank' }}" 
                       id="tenantNoticeActionBtn" 
                       class="btn btn-primary btn-sm {{ $hasAction ? '' : 'd-none' }}" 
                       onclick="dismissTenantNotice(false)" 
                       style="border-radius: 8px; font-size: 13px; font-weight: 600; padding: 7px 18px; {{ $curTheme['btn'] }}">
                        <span id="tenantNoticeActionBtnText">{{ $notice['action_btn_text'] ?? '' }}</span> <i class="fa fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var isPreview = {{ $isPreview ? 'true' : 'false' }};
        var noticeId = '{{ $notice['updated_at'] ?? '1' }}';
        var frequency = '{{ $notice['frequency'] ?? 'session' }}';
        var storageKeyToday = 'tenant_notice_dismiss_date_' + noticeId;
        var storageKeyDaily = 'tenant_notice_daily_' + noticeId;
        var storageKeySession = 'tenant_notice_dismiss_session_' + noticeId;
        var todayStr = new Date().toISOString().slice(0, 10);

        window.dismissTenantNotice = function(closeModal) {
            if (closeModal !== false) {
                $('#tenantNoticeDashboardModal').modal('hide');
            }

            if (!isPreview) {
                if ($('#tenantNoticeDontShowToday').is(':checked') || frequency === 'daily') {
                    localStorage.setItem(storageKeyToday, todayStr);
                    localStorage.setItem(storageKeyDaily, todayStr);
                }

                if (frequency === 'session') {
                    sessionStorage.setItem(storageKeySession, '1');
                }
            }
        };

        if (!isPreview) {
            // Evaluasi apakah modal harus muncul
            if (localStorage.getItem(storageKeyToday) === todayStr) {
                return;
            }

            if (frequency === 'daily' && localStorage.getItem(storageKeyDaily) === todayStr) {
                return;
            }

            if (frequency === 'session' && sessionStorage.getItem(storageKeySession)) {
                return;
            }

            // Tampilkan modal secara otomatis setelah halaman dashboard selesai dimuat
            if (document.readyState === 'complete') {
                setTimeout(showNoticeModal, 500);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(showNoticeModal, 500);
                });
            }

            function showNoticeModal() {
                if (typeof $ !== 'undefined' && $('#tenantNoticeDashboardModal').length) {
                    $('#tenantNoticeDashboardModal').modal({
                        backdrop: 'static',
                        keyboard: true
                    });
                }
            }
        }
    })();
</script>
@endif
