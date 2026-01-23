<style>
    #leazycms-logo {
        all: initial;
        /* reset semua style luar */
        display: inline-block;
        font-family: "Segoe UI", Arial, sans-serif;
    }

    #leazycms-logo * {
        box-sizing: border-box;
    }

    #leazycms-logo .leazycms-logo__wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    #leazycms-logo .leazycms-logo__left {
        font-size: 64px;
        line-height: 1;
    }

    #leazycms-logo .leazycms-logo__icon {
        display: block;
    }
 #leazycms-logo .leazycms-logo__icon img {
        display: block;
        height: 50px;
        width: auto;
    }
    #leazycms-logo .leazycms-logo__right {
        display: flex;
        flex-direction: column;
    }

    #leazycms-logo .leazycms-logo__org {
        font-size: 18px;
        font-weight: 700;
        text-transform: uppercase;
        color: #af691e;
    }

    #leazycms-logo .leazycms-logo__kab {
        font-size: 14px;
        font-weight: 500;
        color: #047857;
    }
</style>
@if(!isset($url))
    <a href="{{ $url ?? '/' }}" class="{{ $class ?? '' }}">
@endif
    <div id="leazycms-logo">
        <div class="leazycms-logo__wrap">
            <div class="leazycms-logo__left">
                <span class="leazycms-logo__icon"><img src="{{ $image }}" alt="Logo"></span>
            </div>
            <div class="leazycms-logo__right">
                <div class="leazycms-logo__org">{{ $brand_name }}</div>
                <div class="leazycms-logo__kab">{{ $brand_tagline }}</div>
            </div>
        </div>
    </div>
@if(!isset($url))
    </a>
@endif