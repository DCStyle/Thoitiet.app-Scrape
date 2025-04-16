@if($weatherData)
    <div class="weather-widget card h-100 {{ $compact ? 'compact' : '' }}">
        <div class="card-body">
            @if(!$compact)
                <h5 class="card-title">Thời tiết {{ $weatherData['location'] }}</h5>
            @endif
            
            <div class="d-flex align-items-center justify-content-{{ $compact ? 'between' : 'start' }} mb-2">
                <div class="weather-icon me-{{ $compact ? '0' : '3' }}">
                    <img src="{{ asset($weatherData['current']['weather_image']) }}" 
                         alt="{{ $weatherData['current']['weather_description'] }}" 
                         class="img-fluid" 
                         style="height: {{ $compact ? '40' : '60' }}px;">
                </div>
                <div class="temperature-display {{ $compact ? '' : 'me-3' }}">
                    <span class="fs-{{ $compact ? '3' : '2' }} fw-bold">{{ $weatherData['current']['temperature'] }}°</span>
                    @if(!$compact)
                        <div class="text-muted small">Cảm giác {{ $weatherData['current']['feels_like'] }}°</div>
                    @endif
                </div>
                @if(!$compact)
                    <div>
                        <div>{{ $weatherData['current']['weather_description'] }}</div>
                    </div>
                @endif
            </div>
            
            @if(!$compact)
                <div class="weather-details d-flex justify-content-between mt-3">
                    <div class="text-center">
                        <div class="small text-muted">Độ ẩm</div>
                        <div>{{ $weatherData['current']['humidity'] }}%</div>
                    </div>
                    <div class="text-center">
                        <div class="small text-muted">Gió</div>
                        <div>{{ $weatherData['current']['wind_speed'] }} km/h</div>
                    </div>
                    <div class="text-center">
                        <div class="small text-muted">Cao/Thấp</div>
                        <div>{{ $weatherData['daily'][0]['max_temp'] }}°/{{ $weatherData['daily'][0]['min_temp'] }}°</div>
                    </div>
                </div>
            @endif
        </div>
        
        @if(!$compact)
            <div class="card-footer bg-transparent border-top-0 text-center">
                <a href="{{ route('weather.location', strtolower(str_replace(' ', '-', $weatherData['location']))) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
            </div>
        @endif
    </div>
@else
    <div class="alert alert-warning">
        Không thể tải dữ liệu thời tiết. Vui lòng thử lại sau.
    </div>
@endif

<style>
    .weather-widget.compact {
        border: none;
        box-shadow: none;
    }
    
    .weather-widget.compact .card-body {
        padding: 0.5rem;
    }
</style>
