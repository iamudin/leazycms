@php
    $inputId = _us($r[0]);
@endphp
<small for="{{$inputId}}">{{$r[0]}}</small>
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text"><i id="preview-{{$inputId}}" class="{{ !empty(old($inputId)) ? old($inputId) : (isset($field[$inputId]) && !empty($field[$inputId]) ? $field[$inputId] : 'fa fa-font') }}"></i></span>
    </div>
    <input {{ isset($r[1]->required) ? 'required':'' }} type="text" id="inputFa-{{$inputId}}" value="{{ !empty(old($inputId)) ? old($inputId) :  (isset($field[$inputId]) && !empty($field[$inputId])  ? $field[$inputId] : null)}}" class="form-control" name="{{$inputId}}" placeholder="Contoh class icon: fas fa-home" onkeyup="document.getElementById('preview-{{$inputId}}').className = this.value || 'fa fa-font'">
    <div class="input-group-append dropdown">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="loadFaIcons('{{$inputId}}')">
            <i class="fa fa-search"></i> Cari
        </button>
        <div class="dropdown-menu dropdown-menu-right p-2" style="width: 300px; max-height: 350px; overflow-y: auto;" id="dropdown-{{$inputId}}">
            <input type="text" class="form-control form-control-sm mb-2" id="searchFa-{{$inputId}}" placeholder="Ketik nama icon..." onkeyup="searchIconAPI('{{$inputId}}')" onclick="event.stopPropagation()">
            <div id="iconList-{{$inputId}}">
                <div class="text-center text-muted py-3">
                    <i class="fa fa-spinner fa-spin mb-2"></i>
                    <p class="mb-0" style="font-size:12px;">Memuat Icon...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif

@push('scripts')
<script>
    if (typeof window.faIconsList === 'undefined') {
        window.faIconsList = [];
        window.isFetchingFa = false;

        function loadFaIcons(inputId) {
            const container = document.getElementById('iconList-' + inputId);
            
            if (container.children.length > 1) return; // Sudah dimuat

            if (window.faIconsList.length > 0) {
                renderFaIcons(inputId, window.faIconsList);
                return;
            }

            if (window.isFetchingFa) {
                setTimeout(() => loadFaIcons(inputId), 500);
                return;
            }

            window.isFetchingFa = true;
            
            fetch('https://raw.githubusercontent.com/FortAwesome/Font-Awesome/5.15.4/metadata/icons.json')
                .then(response => response.json())
                .then(data => {
                    window.faIconsList = Object.keys(data).map(key => {
                        let style = 'fa';
                        if (data[key].styles.includes('brands')) style = 'fab';
                        else if (data[key].styles.includes('solid')) style = 'fas';
                        else if (data[key].styles.includes('regular')) style = 'far';
                        
                        return {
                            class: style + ' fa-' + key,
                            name: key
                        };
                    });
                    
                    window.isFetchingFa = false;
                    renderFaIcons(inputId, window.faIconsList);
                })
                .catch(err => {
                    container.innerHTML = '<div class="text-center text-danger py-3"><p style="font-size:12px;">Gagal memuat icon.</p></div>';
                    window.isFetchingFa = false;
                });
        }

        function renderFaIcons(inputId, icons) {
            const container = document.getElementById('iconList-' + inputId);
            let html = '<div class="d-flex flex-wrap justify-content-center">';
            
            icons.forEach(icon => {
                html += `
                <button type="button" class="btn btn-light btn-sm m-1 icon-pick-btn" data-icon="${icon.class}" title="${icon.name}" style="width:35px;height:35px;" onclick="selectFaIcon('${inputId}', '${icon.class}', this)">
                    <i class="${icon.class}"></i>
                </button>`;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        function searchIconAPI(inputId) {
            var filter = document.getElementById('searchFa-' + inputId).value.toLowerCase();
            var nodes = document.querySelectorAll('#iconList-' + inputId + ' .icon-pick-btn');
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].getAttribute('title').toLowerCase().includes(filter) || nodes[i].getAttribute('data-icon').toLowerCase().includes(filter)) {
                    nodes[i].style.display = "inline-block";
                } else {
                    nodes[i].style.display = "none";
                }
            }
        }

        function selectFaIcon(inputId, iconClass, btnElement) {
            document.getElementById('inputFa-' + inputId).value = iconClass;
            document.getElementById('preview-' + inputId).className = iconClass;
            $(btnElement).closest('.dropdown-menu').removeClass('show');
        }
    }
</script>
@endpush
