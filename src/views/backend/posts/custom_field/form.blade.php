<div class="row">
@foreach ($module->form->custom_field as $r)
    @php
        $type = is_array($r[1]->type ?? null) ? 'option' : ($r[1]->type ?? 'text');
        $isFullWidth = in_array($type, ['textarea', 'rich-text', 'break']);
        $colClass = $isFullWidth ? 'col-12' : 'col-md-6';
    @endphp

    @if ($type === 'break')
        <div class="col-12">
            <div class="d-flex align-items-center mt-3 mb-2 pt-2">
                <div class="badge badge-primary px-3 py-1 font-weight-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.5px; border-radius: 6px; background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe;">
                    <i class="fa fa-layer-group mr-1"></i> {{ $r[0] }}
                </div>
                <div class="flex-grow-1 ml-2" style="height: 1px; background: #e2e8f0;"></div>
            </div>
            @if(!empty($r[1]->helper))
                <small class="text-muted d-block mb-2 pl-1">{{ $r[1]->helper }}</small>
            @endif
        </div>
    @else
        <div class="{{ $colClass }} mb-2.5">
            @if ($type == 'text')
                @include('cms::backend.posts.custom_field.text')
            @elseif ($type == 'textarea')
                @include('cms::backend.posts.custom_field.textarea')
            @elseif ($type == 'file')
                @include('cms::backend.posts.custom_field.file')
            @elseif ($type == 'image')
                @include('cms::backend.posts.custom_field.image')
            @elseif ($type == 'number')
                @include('cms::backend.posts.custom_field.number')
            @elseif ($type == 'phone')
                @include('cms::backend.posts.custom_field.phone')
            @elseif ($type == 'url')
                @include('cms::backend.posts.custom_field.url')
            @elseif ($type == 'email')
                @include('cms::backend.posts.custom_field.email')
            @elseif ($type == 'date')
                @include('cms::backend.posts.custom_field.date')
            @elseif ($type == 'datetime')
                @include('cms::backend.posts.custom_field.datetime')
            @elseif ($type == 'currency')
                @include('cms::backend.posts.custom_field.currency')
            @elseif ($type == 'color')
                @include('cms::backend.posts.custom_field.color')
            @elseif ($type == 'rich-text')
                @include('cms::backend.posts.custom_field.rich-text')
            @elseif ($type == 'font-awesome')
                @include('cms::backend.posts.custom_field.font-awesome')
            @elseif ($type == 'option' || is_array($r[1]->type ?? null))
                @include('cms::backend.posts.custom_field.option')
            @endif
        </div>
    @endif
@endforeach
</div>
