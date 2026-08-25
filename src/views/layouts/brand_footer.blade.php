<!-- Master Brand Footer Watermark (Black Background Inline CSS) -->
<div class="master-footer-brand"
    style="display: block !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 12px 16px !important; background: #000000 !important; border-top: 1px solid rgba(255, 255, 255, 0.1) !important; box-sizing: border-box !important; text-align: center !important; clear: both; color: #ffffff !important;">
    <div
        style="display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; margin: 0 auto !important; padding: 0 !important; text-align: center !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; font-size: 12px !important; font-weight: 500 !important; line-height: 1.6 !important; color: #ffffff !important; opacity: 0.92 !important; letter-spacing: 0.3px !important; box-sizing: border-box !important; vertical-align: middle !important;">
        <span
            style="display: inline !important; margin: 0 !important; padding: 0 !important; color: #bbbbbb !important;">Powered
            by :</span>
        <a href="{{ get_option('brand_url') }}" target="_blank" rel="noopener noreferrer"
            style="display: inline-flex !important; align-items: center !important; gap: 6px !important; margin: 0 !important; padding: 0 !important; color: #ffffff !important; font-weight: 700 !important; text-decoration: none !important; cursor: pointer !important; vertical-align: middle !important;">
            @if(!empty($brandLogo = get_option('brand_logo')))
                <img src="{{ media($brandLogo)->url() }}" alt="{{ get_option('brand_name') }}"
                    style="width: 16px !important; height: 16px !important; object-fit: contain !important; display: inline-block !important; vertical-align: middle !important; border-radius: 3px !important;"
                    onerror="this.style.display='none';">
            @else
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round"
                    style="display: inline-block !important; vertical-align: middle !important; opacity: 0.9 !important;">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                    <path d="M2 12h20" />
                </svg>
            @endif
            <span
                style="display: inline !important; border-bottom: 1px dotted rgba(255, 255, 255, 0.6) !important;">{{ get_option('brand_name') }}</span>
        </a>
    </div>
</div>