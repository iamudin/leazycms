<style>
.polling-result-form {
    font-family: inherit;
}
.polling-result-header {
    text-align: center;
    margin-bottom: 20px;
}
.polling-result-title {
    font-size: 20px;
    line-height: normal;
    font-weight: normal;
    color: #333;
}
.polling-result-body {
    margin-bottom: 20px;
}
.polling-result-item {
    margin-bottom: 16px;
}
.polling-result-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    font-size: 14px;
    color: #333;
}
.polling-result-name {
    font-weight: 500;
}
.polling-result-name i {
    margin-left: 5px;
}
.polling-result-stats {
    color: #666;
    font-size: 13px;
}
.polling-result-bar-bg {
    background: #e9ecef;
    border-radius: 8px;
    height: 12px;
    width: 100%;
    overflow: hidden;
}
.polling-result-bar-fill {
    height: 100%;
    background: #0d6efd;
    border-radius: 8px;
    transition: width 1s ease-in-out;
}
.polling-result-bar-fill.selected {
    background: #198754; /* Green for user's choice */
}
</style>

<div class="polling-result-form polling-result-{{$data->id}}">
    <div class="polling-result-header">
        <h6 class="polling-result-title">{{ $data->title }}</h6>
        <p class="text-muted" style="font-size:12px; margin-top:5px;">Terima kasih atas partisipasi Anda!</p>
    </div>

    <div class="polling-result-body">
        @foreach ($options as $item)
            <div class="polling-result-item">
                <div class="polling-result-info">
                    <span class="polling-result-name">
                        {{ $item->name }}
                        @if((string)$cookieValue === (string)$item->id)
                            <i class="fa fa-check-circle text-success" title="Pilihan Anda"></i>
                        @endif
                    </span>
                    <span class="polling-result-stats">{{ $item->percentage }}% ({{ $item->votes }})</span>
                </div>
                <div class="polling-result-bar-bg">
                    <div class="polling-result-bar-fill {{ (string)$cookieValue === (string)$item->id ? 'selected' : '' }}" style="width: {{ $item->percentage }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="text-center">
        <small class="text-muted">Total Suara: {{ $totalVotes }}</small>
    </div>
</div>
