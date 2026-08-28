@php
    $isWebControllerIndex = false;
    if (isset($index) && count($index) > 0 && !request()->is('/')) {
        $actionMethod = request()->route()?->getActionMethod();
        $controllerAction = (string) (request()->route()?->getAction('controller') ?? '');
        
        // Deteksi presisi: hanya aktif jika $index diinisialisasi dari WebController@index
        if (
            $actionMethod === 'index' || 
            str_contains($controllerAction, 'WebController@index') || 
            (isset($module) && isset($module->web) && $module->web->index && get_post_type('view_type') === 'index')
        ) {
            $isWebControllerIndex = true;
        }
    }
@endphp

@if($isWebControllerIndex && config('modules.multisite_enabled') && function_exists('get_master_ad') && get_master_ad('in_feed'))
    <div id="tenant-auto-infeed-ad" style="display: none; grid-column: 1 / -1; width: 100%; margin: 16px 0; clear: both;">
        {!! render_master_ad('in_feed') !!}
    </div>
    <script>
        (function() {
            function injectFeedAd() {
                var ad = document.getElementById('tenant-auto-infeed-ad');
                if (!ad) return;

                // 1. Selector komprehensif untuk mendeteksi list item / kartu di semua tema & CSS framework
                var selectors = [
                    // Grid & Grid-cols (Tailwind / CSS Grid)
                    '[class*="grid-cols"] > article',
                    '[class*="grid-cols"] > div',
                    '[class*="grid-cols"] > *',
                    '.grid > article',
                    '.grid > div',
                    '.grid > *',
                    
                    // Divide-y & Space-y (Halaman Download Dokumen, Timeline, List Berita Vertikal)
                    '[class*="divide-y"] > div',
                    '[class*="divide-y"] > *',
                    '[class*="space-y"] > article',
                    '[class*="space-y"] > div',
                    '[class*="space-y"] > *',

                    // Bootstrap & Flex Rows
                    '.row > [class*="col-"]',
                    '.row > *',

                    // Tag Semantik HTML5
                    'main article',
                    'section article',
                    'article',

                    // Baris Tabel
                    'table tbody tr',

                    // List Items
                    '.list-group > *',
                    '[class*="flex-col"] > *'
                ];

                var matchedItems = null;

                for (var i = 0; i < selectors.length; i++) {
                    var items = document.querySelectorAll(selectors[i]);
                    var valid = [];
                    for (var k = 0; k < items.length; k++) {
                        var el = items[k];
                        // Hindari elemen header, footer, sidebar, pagination, atau ad itu sendiri
                        if (el.id === 'tenant-auto-infeed-ad' || el.closest('#tenant-auto-infeed-ad') || el.closest('header') || el.closest('footer') || el.closest('aside') || el.closest('#sidebar') || el.closest('nav') || el.closest('[aria-label="Pagination"]')) {
                            continue;
                        }
                        valid.push(el);
                    }
                    if (valid.length >= 3) {
                        matchedItems = valid;
                        break;
                    }
                }

                // 2. Fallback Universal: Cari container apa pun di area konten yang memiliki >= 3 direct children sejenis
                if (!matchedItems) {
                    var searchContainers = document.querySelectorAll('main, section, .container, #content, body');
                    for (var c = 0; c < searchContainers.length; c++) {
                        var container = searchContainers[c];
                        var candidateDivs = container.querySelectorAll('div, ul, ol');
                        for (var d = 0; d < candidateDivs.length; d++) {
                            var parentEl = candidateDivs[d];
                            if (parentEl.id === 'tenant-auto-infeed-ad' || parentEl.closest('#tenant-auto-infeed-ad') || parentEl.closest('header') || parentEl.closest('footer') || parentEl.closest('aside') || parentEl.closest('nav')) continue;
                            
                            var directChildren = [];
                            for (var ch = 0; ch < parentEl.children.length; ch++) {
                                var child = parentEl.children[ch];
                                if (child.id !== 'tenant-auto-infeed-ad' && child.tagName !== 'SCRIPT' && child.tagName !== 'STYLE' && child.tagName !== 'TEMPLATE') {
                                    directChildren.push(child);
                                }
                            }
                            if (directChildren.length >= 3 && directChildren.length <= 100) {
                                matchedItems = directChildren;
                                break;
                            }
                        }
                        if (matchedItems) break;
                    }
                }

                if (matchedItems && matchedItems.length >= 1) {
                    var targetIndex = Math.min(3, matchedItems.length - 1); // Tepat setelah item ke-3 (index 2), atau item terakhir jika < 3
                    var targetItem = matchedItems[targetIndex];

                    // Jika di dalam tabel <tr>, bungkus dengan <tr><td colspan="100%">
                    if (targetItem.tagName === 'TR') {
                        var trWrapper = document.createElement('tr');
                        var tdWrapper = document.createElement('td');
                        tdWrapper.colSpan = 100;
                        tdWrapper.style.padding = '12px 0';
                        tdWrapper.appendChild(ad);
                        trWrapper.appendChild(tdWrapper);
                        targetItem.insertAdjacentElement('afterend', trWrapper);
                    } else {
                        targetItem.insertAdjacentElement('afterend', ad);
                    }
                    ad.style.display = 'block';
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', injectFeedAd);
            } else {
                injectFeedAd();
            }
        })();
    </script>
@endif