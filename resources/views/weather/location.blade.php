@extends('layouts.app')

@section('seo')
    {!! seo($SEOData) !!}
@endsection

@section('content')
    <div class="page-content">
        <div class="container">
            @include('partials.location-breadcrumbs')

            <!-- Time period tabs -->
            @include('partials.location-time-period-tabs', ['activeTab' => 'current'])

            <div class="container">
                <!-- Current Weather Card -->
                <div class="row">
                    <div class="col-xl-6 col-md-12">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="weather-main">
                                    <a href="/{{ isset($ward) ? $province->getSlug() . '/' . $district->getSlug() . '/' . $ward->getSlug() : (isset($district) ? $province->getSlug() . '/' . $district->getSlug() : $province->getSlug()) }}" class="feature-location d-flex">
                                        <h1 class="weather-main-title">Dự báo thời tiết {{ isset($ward) ? $ward->name . ', ' . $district->name . ', ' . $province->name : (isset($district) ? $district->name . ', ' . $province->name : $province->name) }}</h1>
                                    </a>
                                    <p class="">Hôm nay, {{ $today }}</p>
                                    <div class="weather-main-hero">
                                        <img src="{{ asset($weatherData['current']['weather_image']) }}" alt="Dự báo thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}">
                                        <p class="temp">
                                            {{ $weatherData['current']['temperature'] }}°
                                        </p>
                                        <div class="desc">
                                            <p>{{ $weatherData['current']['weather_description'] }}</p>
                                            <span>Cảm giác như <span>{{ $weatherData['current']['feels_like'] }}°</span></span>
                                        </div>
                                    </div>
                                    <div class="weather-main-desc">
                                        <div class="item">
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}">
                                            <div class="item-title">Thấp/Cao</div>
                                            <div class="temp">
                                                <p>{{ $weatherData['daily'][0]['min_temp'] }}°/</p>
                                                <p>{{ $weatherData['daily'][0]['max_temp'] }}°</p>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/humidity-xl.svg" alt="Độ ẩm">
                                            <div class="item-title">Độ ẩm</div>
                                            <div class="temp">
                                                <p>{{ $weatherData['current']['humidity'] }} %</p>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/clarity-eye-line.svg" alt="Tầm nhìn">
                                            <div class="item-title">Tầm nhìn</div>
                                            <div class="temp">
                                                <p>{{ $weatherData['current']['visibility'] ?? 10 }} km</p>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/ph-wind.svg" alt="Dự báo tốc độ gió">
                                            <div class="item-title">Gió</div>
                                            <div class="temp">
                                                <p>{{ $weatherData['current']['wind_speed'] }} km/h</p>
                                            </div>
                                        </div>

                                        <div class="item">
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/dawn.svg" alt="Bình minh - Hoàng hôn">
                                            <div class="item-title">Bình minh/Hoàng hôn</div>
                                            <div class="temp">
                                                <p>
                                                    {{ $sunriseSunsetData['sunrise'] }}/{{ $sunriseSunsetData['sunset'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="weather-main">
                                    <div class="feature-location d-flex mb-3">
                                        <h2 class="weather-main-title">Nhiệt độ {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}</h2>
                                    </div>
                                    <div class="weather-day-temp">
                                        <div class="temp-item">
                                            <div class="h4">Ngày</div>
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/temp-1.png" alt="Nhiệt độ thời tiết ban ngày ở {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}">
                                            <div>
                                        <span>
                                            {{ $timeOfDayTemps['day']['temperature'] }}°
                                        </span>
                                                /
                                                <span>
                                            {{ $timeOfDayTemps['day']['temperature_night'] }}°
                                        </span>
                                            </div>
                                        </div>
                                        <div class="temp-item">
                                            <div class="h4">Đêm</div>
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/temp-2.png" alt="Nhiệt độ thời tiết ban đêm ở {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}">
                                            <div>
                                        <span>
                                            {{ $timeOfDayTemps['night']['temperature'] }}°
                                        </span>
                                                /
                                                <span>
                                            {{ $timeOfDayTemps['night']['temperature_night'] }}°
                                        </span>
                                            </div>
                                        </div>
                                        <div class="temp-item">
                                            <div class="h4">Sáng</div>
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/temp-3.png" alt="Nhiệt độ buổi sáng">
                                            <div>
                                        <span>
                                            {{ $timeOfDayTemps['morning']['temperature'] }}°
                                        </span>
                                                /
                                                <span>
                                            {{ $timeOfDayTemps['morning']['temperature_night'] }}°
                                        </span>
                                            </div>
                                        </div>
                                        <div class="temp-item">
                                            <div class="h4">Tối</div>
                                            <img src="https://thoitiet247.vn/assets/images/icon-1/temp-4.png" alt="Nhiệt độ buổi tối">
                                            <div>
                                        <span>
                                            {{ $timeOfDayTemps['evening']['temperature'] }}°
                                        </span>
                                                /
                                                <span>
                                            {{ $timeOfDayTemps['evening']['temperature_night'] }}°
                                        </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-column page layout -->
                <div class="row">
                    <!-- Current location weather detail -->
                    <div class="col-xl-8 weather-detail-content">
                        <!-- Hourly weather forecast -->
                        <div class="card">
                            <div class="card-body">
                                <div class="title-main">
                                    <h2 class="card-title me-2">Thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} theo giờ</h2>
                                </div>
                                <div id="carouselExampleControls" class="carousel" data-bs-touch="true" data-bs-wrap="false" data-bs-ride="false">
                                    <div class="carousel-inner" role="listbox">
                                        <div class="carousel-item active">
                                            @foreach(array_slice($weatherData['hourly'], 0, 4) as $index => $hour)
                                                <div class="col-md-3 hour-item">
                                                    <div class="h4">{{ $hour['time'] }}</div>
                                                    <div>
                                    <span>
                                        {{ $hour['temperature'] }}°
                                    </span>
                                                        /
                                                        <span>
                                        {{ $index > 0 ? $weatherData['hourly'][$index-1]['temperature'] : $hour['temperature'] }}°
                                    </span>
                                                    </div>
                                                    <img src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                    <p class="humidity">
                                                        <img src="https://thoitiet247.vn/assets/images/icon-1/dewpoint.svg" alt="Lượng mưa">
                                                        <span>{{ $hour['precipitation'] > 0 ? $hour['precipitation'] : '0' }} mm</span>
                                                    </p>
                                                    <p class="desc">{{ $hour['weather_description'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if(count($weatherData['hourly']) > 4)
                                            <div class="carousel-item">
                                                @foreach(array_slice($weatherData['hourly'], 4, 4) as $index => $hour)
                                                    <div class="col-md-3 hour-item">
                                                        <div class="h4">{{ $hour['time'] }}</div>
                                                        <div>
                                    <span>
                                        {{ $hour['temperature'] }}°
                                    </span>
                                                            /
                                                            <span>
                                        {{ $index > 0 ? $weatherData['hourly'][$index+3]['temperature'] : $hour['temperature'] }}°
                                    </span>
                                                        </div>
                                                        <img src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                        <p class="humidity">
                                                            <img src="https://thoitiet247.vn/assets/images/icon-1/dewpoint.svg" alt="Lượng mưa">
                                                            <span>{{ $hour['precipitation'] > 0 ? $hour['precipitation'] : '0' }} mm</span>
                                                        </p>
                                                        <p class="desc">{{ $hour['weather_description'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(count($weatherData['hourly']) > 8)
                                            <div class="carousel-item">
                                                @foreach(array_slice($weatherData['hourly'], 8, 4) as $index => $hour)
                                                    <div class="col-md-3 hour-item">
                                                        <div class="h4">{{ $hour['time'] }}</div>
                                                        <div>
                                    <span>
                                        {{ $hour['temperature'] }}°
                                    </span>
                                                            /
                                                            <span>
                                        {{ $index > 0 ? $weatherData['hourly'][$index+7]['temperature'] : $hour['temperature'] }}°
                                    </span>
                                                        </div>
                                                        <img src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                        <p class="humidity">
                                                            <img src="https://thoitiet247.vn/assets/images/icon-1/dewpoint.svg" alt="Lượng mưa">
                                                            <span>{{ $hour['precipitation'] > 0 ? $hour['precipitation'] : '0' }} mm</span>
                                                        </p>
                                                        <p class="desc">{{ $hour['weather_description'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(count($weatherData['hourly']) > 12)
                                            <div class="carousel-item">
                                                @foreach(array_slice($weatherData['hourly'], 12, 4) as $index => $hour)
                                                    <div class="col-md-3 hour-item">
                                                        <div class="h4">{{ $hour['time'] }}</div>
                                                        <div>
                                    <span>
                                        {{ $hour['temperature'] }}°
                                    </span>
                                                            /
                                                            <span>
                                        {{ $index > 0 ? $weatherData['hourly'][$index+11]['temperature'] : $hour['temperature'] }}°
                                    </span>
                                                        </div>
                                                        <img src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                        <p class="humidity">
                                                            <img src="https://thoitiet247.vn/assets/images/icon-1/dewpoint.svg" alt="Lượng mưa">
                                                            <span>{{ $hour['precipitation'] > 0 ? $hour['precipitation'] : '0' }} mm</span>
                                                        </p>
                                                        <p class="desc">{{ $hour['weather_description'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(count($weatherData['hourly']) > 16)
                                            <div class="carousel-item">
                                                @foreach(array_slice($weatherData['hourly'], 16, 4) as $index => $hour)
                                                    <div class="col-md-3 hour-item">
                                                        <div class="h4">{{ $hour['time'] }}</div>
                                                        <div>
                                    <span>
                                        {{ $hour['temperature'] }}°
                                    </span>
                                                            /
                                                            <span>
                                        {{ $index > 0 ? $weatherData['hourly'][$index+15]['temperature'] : $hour['temperature'] }}°
                                    </span>
                                                        </div>
                                                        <img src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                        <p class="humidity">
                                                            <img src="https://thoitiet247.vn/assets/images/icon-1/dewpoint.svg" alt="Lượng mưa">
                                                            <span>{{ $hour['precipitation'] > 0 ? $hour['precipitation'] : '0' }} mm</span>
                                                        </p>
                                                        <p class="desc">{{ $hour['weather_description'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(count($weatherData['hourly']) > 20)
                                            <div class="carousel-item">
                                                @foreach(array_slice($weatherData['hourly'], 20, 4) as $index => $hour)
                                                    <div class="col-md-3 hour-item">
                                                        <div class="h4">{{ $hour['time'] }}</div>
                                                        <div>
                                    <span>
                                        {{ $hour['temperature'] }}°
                                    </span>
                                                            /
                                                            <span>
                                        {{ $index > 0 ? $weatherData['hourly'][$index+19]['temperature'] : $hour['temperature'] }}°
                                    </span>
                                                        </div>
                                                        <img src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                        <p class="humidity">
                                                            <img src="https://thoitiet247.vn/assets/images/icon-1/dewpoint.svg" alt="Lượng mưa">
                                                            <span>{{ $hour['precipitation'] > 0 ? $hour['precipitation'] : '0' }} mm</span>
                                                        </p>
                                                        <p class="desc">{{ $hour['weather_description'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <a class="carousel-control-prev action-arrow" href="#carouselExampleControls" role="button" data-bs-slide="prev">
                                        <i><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg></i>
                                    </a>
                                    <a class="carousel-control-next action-arrow" href="#carouselExampleControls" role="button" data-bs-slide="next">
                                        <i><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg></i>
                                    </a>
                                </div>
                                <div class="page_speed_832169772">
                                    @if(isset($ward))
                                        <a href="{{ route('weather.ward.hourly', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-btn-24h">Chi tiết 24h tới</a>
                                    @elseif(isset($district))
                                        <a href="{{ route('weather.district.hourly', [$province->getSlug(), $district->getSlug()]) }}" class="weather-btn-24h">Chi tiết 24h tới</a>
                                    @else
                                        <a href="{{ route('weather.province.hourly', $province->getSlug()) }}" class="weather-btn-24h">Chi tiết 24h tới</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Daily forecast for next days -->
                        <div class="card rounded-4 border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h3 class="fw-bold text-primary mb-3">Dự báo thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} những ngày tới</h3>

                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                        @foreach($weatherData['daily'] as $index => $day)
                                            <tr class="align-middle">
                                                <td width="15%">
                                                    <strong>{{ $day['day_name'] }} <small class="text-muted">{{ $day['date'] }}</small></strong>
                                                </td>
                                                <td width="10%">
                                                    <div class="d-flex">
                                                        <img src="{{ asset($day['weather_image']) }}" alt="{{ $day['weather_description'] }}" height="40">
                                                        <img src="{{ asset($day['weather_image']) }}" alt="{{ $day['weather_description'] }}" height="40">
                                                    </div>
                                                </td>
                                                <td width="30%">{{ $day['weather_description'] }}</td>
                                                <td width="15%" class="text-center">
                                                    <i class="fas fa-wind me-1"></i> {{ number_format(5.0 + $index * 0.5, 2) }} km/h
                                                </td>
                                                <td width="15%" class="text-end">
                                                    <span class="text-primary">{{ $day['min_temp'] }}°</span> /
                                                    <span class="text-danger">{{ $day['max_temp'] }}°</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Temperature and Precipitation Probability Chart -->
                        <div class="card rounded-4 border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h3 class="fw-bold text-primary mb-3">Nhiệt độ và khả năng có mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} trong 12h tới</h3>
                                <canvas id="tempRainProbabilityChart" height="250"></canvas>
                            </div>
                        </div>

                        <!-- Precipitation Amount Chart -->
                        <div class="card rounded-4 border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h3 class="fw-bold text-primary mb-3">Lượng mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} những giờ tới</h3>
                                <canvas id="precipitationChart" height="250"></canvas>
                            </div>
                        </div>

                        <!-- Temperature and Precipitation Probability for Next Days Chart -->
                        <div class="card rounded-4 border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h3 class="fw-bold text-primary mb-3">Nhiệt độ và khả năng có mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} những ngày tới</h3>
                                <canvas id="tempRainProbabilityDailyChart" height="250"></canvas>
                            </div>
                        </div>

                        @if(isset($province) && !isset($district))
                            <!-- Districts Weather -->
                            <div class="card rounded-4 border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h3 class="fw-bold text-primary mb-3">Thời tiết quận huyện {{ $province->name }}</h3>

                                    <div class="row">
                                        @foreach($districtsWithWeather as $index => $district)
                                            <div class="col-md-4 mb-3 {{ $index >= 12 ? 'district-hidden d-none' : '' }}">
                                                <div class="border-bottom pb-2 mb-2">
                                                    <a href="{{ $district['url'] }}" class="text-decoration-none">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $district['weather_icon'] }}" alt="Weather" height="30" class="me-2">
                                                            <span class="text-dark">{{ $district['name'] }}</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-center mt-3">
                                        <button class="btn btn-primary btn-sm" id="showMore">Xem thêm</button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(isset($district) && !isset($ward) && isset($wards) && $wards->count() > 0)
                            <!-- Wards Weather -->
                            <div class="card rounded-4 border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h3 class="fw-bold text-primary mb-3">Thời tiết phường xã {{ $district->name }}</h3>

                                    <div class="row">
                                        @foreach($wards as $index => $ward)
                                            <div class="col-md-4 mb-3 {{ $index >= 12 ? 'ward-hidden d-none' : '' }}">
                                                <div class="border-bottom pb-2 mb-2">
                                                    <a href="{{ route('weather.ward', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="text-decoration-none">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ asset('/assets/images/weather-1/01d.png') }}" alt="Weather" height="30" class="me-2">
                                                            <span class="text-dark">{{ $ward->name }}</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-center mt-3">
                                        <button class="btn btn-primary btn-sm" id="showMoreWards">Xem thêm</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-4 weather-highlight-live">
                        @include('partials.location-sidebar')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Temperature and Rain Probability Chart (12 hours)
            const hourlyData = @json($hourlyChartData);

            const ctx1 = document.getElementById('tempRainProbabilityChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: hourlyData.map(d => d.time),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Nhiệt độ (°C)',
                            data: hourlyData.map(d => d.temperature),
                            borderColor: '#ff7e00',
                            backgroundColor: 'rgba(255, 126, 0, 0.1)',
                            yAxisID: 'temperature',
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#ff7e00',
                        },
                        {
                            type: 'bar',
                            label: 'Khả năng có mưa (%)',
                            data: hourlyData.map(d => d.precipitation_probability),
                            backgroundColor: 'rgba(33, 150, 243, 0.7)',
                            yAxisID: 'precipitation',
                            barPercentage: 0.8
                        }
                    ]
                },
                options: {
                    scales: {
                        temperature: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Nhiệt độ (°C)'
                            },
                            grid: {
                                display: false
                            }
                        },
                        precipitation: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Khả năng có mưa (%)'
                            },
                            min: 0,
                            max: 100
                        }
                    }
                }
            });

            // Precipitation Amount Chart
            const precipData = @json($precipitationHourlyData);

            const ctx2 = document.getElementById('precipitationChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: precipData.map(d => d.time),
                    datasets: [
                        {
                            label: 'Lượng mưa (mm)',
                            data: precipData.map(d => d.precipitation_amount),
                            backgroundColor: '#2196F3'
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Lượng mưa (mm)'
                            }
                        }
                    }
                }
            });

            // Daily Temperature and Precipitation Probability Chart
            const dailyData = @json($dailyChartData);

            const ctx3 = document.getElementById('tempRainProbabilityDailyChart').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: dailyData.map(d => `${d.day_name} ${d.date}`),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Nhiệt độ tối đa (°C)',
                            data: dailyData.map(d => d.temperature_max),
                            borderColor: '#ff7e00',
                            backgroundColor: 'rgba(255, 126, 0, 0.1)',
                            yAxisID: 'temperature',
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointBackgroundColor: '#ff7e00',
                        },
                        {
                            type: 'line',
                            label: 'Nhiệt độ tối thiểu (°C)',
                            data: dailyData.map(d => d.temperature_min),
                            borderColor: '#3f51b5',
                            backgroundColor: 'rgba(63, 81, 181, 0.1)',
                            yAxisID: 'temperature',
                            tension: 0.4,
                            borderWidth: 3,
                            borderDash: [5, 5],
                            pointRadius: 5,
                            pointBackgroundColor: '#3f51b5',
                        },
                        {
                            type: 'bar',
                            label: 'Khả năng có mưa (%)',
                            data: dailyData.map(d => d.precipitation_probability),
                            backgroundColor: 'rgba(33, 150, 243, 0.7)',
                            yAxisID: 'precipitation',
                            barPercentage: 0.6
                        }
                    ]
                },
                options: {
                    scales: {
                        temperature: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Nhiệt độ (°C)'
                            },
                            grid: {
                                display: false
                            }
                        },
                        precipitation: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Khả năng có mưa (%)'
                            },
                            min: 0,
                            max: 100
                        }
                    }
                }
            });

            // Show more districts button
            if (document.getElementById('showMore')) {
                document.getElementById('showMore').addEventListener('click', function() {
                    const hiddenDistricts = document.querySelectorAll('.district-hidden');
                    hiddenDistricts.forEach(district => {
                        district.classList.remove('d-none');
                    });
                    this.style.display = 'none';
                });
            }

            // Show more wards button
            if (document.getElementById('showMoreWards')) {
                document.getElementById('showMoreWards').addEventListener('click', function() {
                    const hiddenWards = document.querySelectorAll('.ward-hidden');
                    hiddenWards.forEach(ward => {
                        ward.classList.remove('d-none');
                    });
                    this.style.display = 'none';
                });
            }
        });
    </script>
@endpush
