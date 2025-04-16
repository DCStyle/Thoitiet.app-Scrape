@extends('layouts.app')

@section('seo')
    {!! seo($SEOData) !!}
@endsection

@section('content')
<div class="page-content">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h2 class="card-title mb-0">Dự báo thời tiết 63 tỉnh thành</h2>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Dự báo thời tiết cập nhật mới nhất ngày {{ date('d/m/Y') }}</p>
                        </div>
                        
                        <!-- Region Tabs -->
                        <ul class="nav nav-tabs mb-4" id="regionTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="north-tab" data-bs-toggle="tab" data-bs-target="#north" type="button" role="tab" aria-controls="north" aria-selected="true">Miền Bắc</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="central-tab" data-bs-toggle="tab" data-bs-target="#central" type="button" role="tab" aria-controls="central" aria-selected="false">Miền Trung</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="south-tab" data-bs-toggle="tab" data-bs-target="#south" type="button" role="tab" aria-controls="south" aria-selected="false">Miền Nam</button>
                            </li>
                        </ul>
                        
                        <!-- Region Content -->
                        <div class="tab-content" id="regionTabsContent">
                            <!-- Northern Vietnam -->
                            <div class="tab-pane fade show active" id="north" role="tabpanel" aria-labelledby="north-tab">
                                <div class="row">
                                    @foreach($citiesWeather as $city)
                                        @if(in_array($city['location'], ['Hà Nội', 'Bắc Giang', 'Bắc Kạn', 'Cao Bằng', 'Điện Biên', 'Hà Giang', 'Hải Dương', 'Hải Phòng', 'Hòa Bình', 'Hưng Yên', 'Lạng Sơn', 'Lào Cai', 'Nam Định', 'Ninh Bình', 'Phú Thọ', 'Quảng Ninh', 'Sơn La', 'Thái Bình', 'Thái Nguyên', 'Tuyên Quang', 'Vĩnh Phúc', 'Yên Bái']))
                                            <div class="col-md-3 col-sm-6 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">{{ $city['location'] }}</h5>
                                                        <div class="d-flex align-items-center justify-content-center my-3">
                                                            <img src="{{ asset($city['current']['weather_image']) }}" alt="{{ $city['current']['weather_description'] }}" class="img-fluid me-2" style="height: 50px;">
                                                            <span class="fs-2 fw-bold">{{ $city['current']['temperature'] }}°</span>
                                                        </div>
                                                        <p class="mb-2">{{ $city['current']['weather_description'] }}</p>
                                                        <div class="small d-flex justify-content-center mt-1">
                                                            <div class="me-3">
                                                                <i class="fas fa-temperature-high text-danger me-1"></i> {{ $city['daily'][0]['max_temp'] }}°
                                                            </div>
                                                            <div>
                                                                <i class="fas fa-temperature-low text-primary me-1"></i> {{ $city['daily'][0]['min_temp'] }}°
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-white border-top-0 text-center">
                                                        <a href="{{ route('weather.location', strtolower(str_replace(' ', '-', $city['location']))) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Central Vietnam -->
                            <div class="tab-pane fade" id="central" role="tabpanel" aria-labelledby="central-tab">
                                <div class="row">
                                    @foreach($citiesWeather as $city)
                                        @if(in_array($city['location'], ['Đà Nẵng', 'Bình Định', 'Đắk Lắk', 'Đắk Nông', 'Gia Lai', 'Hà Tĩnh', 'Khánh Hòa', 'Kon Tum', 'Lâm Đồng', 'Nghệ An', 'Ninh Thuận', 'Phú Yên', 'Quảng Bình', 'Quảng Nam', 'Quảng Ngãi', 'Quảng Trị', 'Thanh Hóa', 'Thừa Thiên Huế']))
                                            <div class="col-md-3 col-sm-6 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">{{ $city['location'] }}</h5>
                                                        <div class="d-flex align-items-center justify-content-center my-3">
                                                            <img src="{{ asset($city['current']['weather_image']) }}" alt="{{ $city['current']['weather_description'] }}" class="img-fluid me-2" style="height: 50px;">
                                                            <span class="fs-2 fw-bold">{{ $city['current']['temperature'] }}°</span>
                                                        </div>
                                                        <p class="mb-2">{{ $city['current']['weather_description'] }}</p>
                                                        <div class="small d-flex justify-content-center mt-1">
                                                            <div class="me-3">
                                                                <i class="fas fa-temperature-high text-danger me-1"></i> {{ $city['daily'][0]['max_temp'] }}°
                                                            </div>
                                                            <div>
                                                                <i class="fas fa-temperature-low text-primary me-1"></i> {{ $city['daily'][0]['min_temp'] }}°
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-white border-top-0 text-center">
                                                        <a href="{{ route('weather.location', strtolower(str_replace(' ', '-', $city['location']))) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Southern Vietnam -->
                            <div class="tab-pane fade" id="south" role="tabpanel" aria-labelledby="south-tab">
                                <div class="row">
                                    @foreach($citiesWeather as $city)
                                        @if(in_array($city['location'], ['Hồ Chí Minh', 'An Giang', 'Bà Rịa - Vũng Tàu', 'Bạc Liêu', 'Bến Tre', 'Bình Dương', 'Bình Phước', 'Bình Thuận', 'Cà Mau', 'Cần Thơ', 'Đồng Nai', 'Đồng Tháp', 'Hậu Giang', 'Kiên Giang', 'Long An', 'Sóc Trăng', 'Tây Ninh', 'Tiền Giang', 'Trà Vinh', 'Vĩnh Long']))
                                            <div class="col-md-3 col-sm-6 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">{{ $city['location'] }}</h5>
                                                        <div class="d-flex align-items-center justify-content-center my-3">
                                                            <img src="{{ asset($city['current']['weather_image']) }}" alt="{{ $city['current']['weather_description'] }}" class="img-fluid me-2" style="height: 50px;">
                                                            <span class="fs-2 fw-bold">{{ $city['current']['temperature'] }}°</span>
                                                        </div>
                                                        <p class="mb-2">{{ $city['current']['weather_description'] }}</p>
                                                        <div class="small d-flex justify-content-center mt-1">
                                                            <div class="me-3">
                                                                <i class="fas fa-temperature-high text-danger me-1"></i> {{ $city['daily'][0]['max_temp'] }}°
                                                            </div>
                                                            <div>
                                                                <i class="fas fa-temperature-low text-primary me-1"></i> {{ $city['daily'][0]['min_temp'] }}°
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-white border-top-0 text-center">
                                                        <a href="{{ route('weather.location', strtolower(str_replace(' ', '-', $city['location']))) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Weather Map -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3 class="card-title mb-0">Bản đồ thời tiết Việt Nam</h3>
                    </div>
                    <div class="card-body">
                        <div id="weather-map" style="height: 600px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('weather-map').setView([16.0, 108.0], 5);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Vietnam bounds
        const southWest = L.latLng(8.18, 102.14);
        const northEast = L.latLng(23.39, 109.46);
        const bounds = L.latLngBounds(southWest, northEast);
        
        map.fitBounds(bounds);
        
        // Add markers for cities
        const cities = @json($citiesWeather);
        
        cities.forEach(city => {
            // Create custom icon based on weather
            const weatherIcon = L.divIcon({
                html: `<div class="weather-marker">
                          <img src="${city.current.weather_image}" alt="${city.current.weather_description}" width="24">
                          <span class="temp">${city.current.temperature}°</span>
                       </div>`,
                className: 'weather-marker-container',
                iconSize: [40, 40]
            });
            
            // Add marker with popup
            const marker = L.marker([getLatForCity(city.location), getLngForCity(city.location)], {icon: weatherIcon}).addTo(map);
            
            marker.bindPopup(`
                <div class="text-center">
                    <h6>${city.location}</h6>
                    <img src="${city.current.weather_image}" alt="${city.current.weather_description}" width="40">
                    <p class="mb-1 fw-bold">${city.current.temperature}°C</p>
                    <p class="mb-1">${city.current.weather_description}</p>
                    <p class="mb-1 small">
                        <i class="fas fa-tint text-primary"></i> ${city.current.humidity}% &nbsp;
                        <i class="fas fa-wind"></i> ${city.current.wind_speed} km/h
                    </p>
                    <a href="/weather/${city.location.toLowerCase().replace(/ /g, '-')}" class="btn btn-sm btn-primary mt-2">Chi tiết</a>
                </div>
            `);
        });
        
        // Helper functions to get coordinates for cities
        function getLatForCity(cityName) {
            // This is a simplified example - in reality, you would have a proper mapping
            const cityCoords = {
                'Hà Nội': 21.0285,
                'Hồ Chí Minh': 10.8231,
                'Đà Nẵng': 16.0544,
                'Huế': 16.4637,
                'Cần Thơ': 10.0452,
                'Hải Phòng': 20.8449,
                'Nha Trang': 12.2388,
                'Đà Lạt': 11.9404,
                // Add more cities as needed
            };
            
            return cityCoords[cityName] || 16.0; // Default to central Vietnam if not found
        }
        
        function getLngForCity(cityName) {
            const cityCoords = {
                'Hà Nội': 105.8542,
                'Hồ Chí Minh': 106.6297,
                'Đà Nẵng': 108.2022,
                'Huế': 107.5846,
                'Cần Thơ': 105.7662,
                'Hải Phòng': 106.6881,
                'Nha Trang': 109.1967,
                'Đà Lạt': 108.4583,
                // Add more cities as needed
            };
            
            return cityCoords[cityName] || 108.0; // Default to central Vietnam if not found
        }
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
    .weather-marker-container {
        background: none;
        border: none;
    }
    
    .weather-marker {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        padding: 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
    }
    
    .weather-marker .temp {
        font-weight: bold;
        font-size: 12px;
    }
    
    .nav-tabs .nav-link {
        color: #495057;
    }
    
    .nav-tabs .nav-link.active {
        color: #3498db;
        font-weight: bold;
    }
</style>
@endpush
