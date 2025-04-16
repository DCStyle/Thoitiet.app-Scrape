@extends('layouts.app')

@section('seo')
    {!! seo($SEOData) !!}
@endsection

@section('content')
    <div class="page-content">
        <div class="container">
            @include('partials.location-breadcrumbs')

            <!-- Time period tabs -->
            @include('partials.location-time-period-tabs', ['activeTab' => 'tomorrow'])

            <div class="container">
                <div class="row">
                    <!-- Main location weather detail -->
                    <div class="col-xl-8 weather-detail-content">
                        <!-- Main tomorrow weather forecast card -->
                        <div class="card">
                            <div class="card-body">
                                <div class="title-main">
                                    @php
                                        // Get tomorrow's forecast data (first item in daily after today)
                                        $tomorrowData = $weatherData['daily'][1] ?? null;

                                        // Get tomorrow's date
                                        $tomorrowDate = date('d/m', strtotime('+1 day'));
                                        $dayName = date('N', strtotime('+1 day'));
                                        $dayNames = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
                                        $formattedDayName = $dayNames[$dayName];
                                    @endphp

                                    <a href="{{ isset($ward) ? route('weather.ward.tomorrow', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) : (isset($district) ? route('weather.district.tomorrow', [$province->getSlug(), $district->getSlug()]) : route('weather.province.tomorrow', $province->getSlug())) }}">
                                        <h1 class="card-title me-2">Dự báo thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} ngày mai</h1>
                                    </a>
                                    <p class="card-title me-2">{{ $formattedDayName }} - {{ $tomorrowDate }}</p>
                                </div>
                                <div class="weather-main-hero">
                                    <img src="{{ asset($tomorrowData['weather_image']) }}" alt="{{ $tomorrowData['weather_description'] }}">
                                    <p class="temp">
                                        {{ $tomorrowData['max_temp'] }}°
                                    </p>
                                    <div class="desc">
                                        <p>{{ $tomorrowData['weather_description'] }}</p>
                                        <span>Cảm giác như <span>{{ $tomorrowData['max_temp'] + 1 }}°</span></span>
                                    </div>
                                    <div class="extra">
                                        <div class="item">
                                            <div class="icon"><img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết"></div>
                                            <div>Ngày/Đêm:</div>
                                            <div class="temp">
                                                <p>
                                                    {{ $tomorrowData['max_temp'] }}°
                                                </p>/
                                                <p>
                                                    {{ $tomorrowData['min_temp'] }}°
                                                </p>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="icon"><img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ"></div>
                                            <div>Sáng/Tối:</div>
                                            <div class="temp">
                                                <p>
                                                    {{ $timeOfDayTemps['morning']['temperature'] ?? $tomorrowData['min_temp'] }}°
                                                </p>/
                                                <p>
                                                    {{ $timeOfDayTemps['evening']['temperature'] ?? $tomorrowData['max_temp'] }}°
                                                </p>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="icon"><img src="/assets/images/icon-1/line-md-paint.svg" alt="Lượng mưa"></div>
                                            <div>Lượng mưa:</div>
                                            <div class="temp">
                                                <p>
                                                    {{ $tomorrowData['precipitation_sum'] }} mm
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="weather-main-desc">
                                    <div class="item">
                                        <img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} vào ngày mai">
                                        <div class="item-title">Thấp/Cao</div>
                                        <div class="temp">
                                            <p>{{ $tomorrowData['min_temp'] }}°/</p>
                                            <p>{{ $tomorrowData['max_temp'] }}°</p>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <img src="/assets/images/icon-1/humidity-xl.svg" alt="Dự báo độ ẩm thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}">
                                        <div class="item-title">Độ ẩm</div>
                                        <div class="temp">
                                            <p>{{ isset($weatherData['hourly'][24]['humidity']) ? $weatherData['hourly'][24]['humidity'] : '78' }} %</p>
                                        </div>
                                    </div>

                                    <div class="item">
                                        <img src="/assets/images/icon-1/ph-wind.svg" alt="Tốc độ gió {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} vào ngày mai">
                                        <div class="item-title">Gió</div>
                                        <div class="temp">
                                            <p>{{ isset($weatherData['hourly'][24]['wind_speed']) ? number_format($weatherData['hourly'][24]['wind_speed'], 2) : '6.08' }} km/h</p>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <img src="/assets/images/icon-1/pressure.svg" alt="Áp suất không khí">
                                        <div class="item-title">Áp suất</div>
                                        <div class="temp">
                                            <p>1008 hPa</p>
                                        </div>
                                    </div>

                                    <div class="item">
                                        <img src="/assets/images/icon-1/dawn.svg" alt="Bình minh và hoàng hôn">
                                        <div class="item-title">Bình minh/Hoàng hôn</div>
                                        <div class="temp">
                                            <p>
                                                05:35/18:15
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hourly weather forecast for tomorrow -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <h2 class="card-title mb-4">Thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} ngày mai theo giờ</h2>

                                <div class="weather-feature-list hourly-weather mt-3">
                                    @php
                                        // Get tomorrow's hourly data (hours 24-47 from current hourly data)
                                        $tomorrowHourly = array_slice($weatherData['hourly'], 24, 24);
                                        $count = 0;
                                        $displayHours = [1, 4, 7, 10, 13, 16, 19, 22]; // Hours to display (1:00, 4:00, etc.)
                                    @endphp

                                    @foreach($tomorrowHourly as $index => $hour)
                                        @php
                                            $hourTime = date('H:i', strtotime($hour['full_time']));
                                            $hourNum = intval(date('H', strtotime($hour['full_time'])));

                                            // Only show specific hours
                                            if (!in_array($hourNum, $displayHours)) continue;

                                            // First 7 items are shown, others are hidden
                                            $isHidden = $count >= 7;
                                            $count++;

                                            // Get previous temperature for comparison
                                            $prevIndex = $index > 0 ? $index - 1 : $index;
                                            $prevTemp = $tomorrowHourly[$prevIndex]['temperature'];
                                        @endphp

                                        <div class="weather-feature-item {{ $isHidden ? 'hide' : 'shown' }}" {{ $isHidden ? 'style="display: none;"' : '' }}>
                                            <div class="weather-feature-sumary collapsed" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index+24 }}" aria-expanded="false" aria-controls="collapse0">
                                                <div class="h4 mb-0">
                                                    <span class="text-uppercase">{{ $hourTime }}</span>
                                                </div>
                                                <p>
                                                    <span>{{ $hour['temperature'] }}°</span> / <span>{{ $prevTemp }}°</span>
                                                </p>
                                                <img class="image" src="{{ asset($hour['weather_image']) }}" alt="Thời tiết {{ $hour['weather_description'] }}">
                                                <p class="desc">
                                                    {{ $hour['weather_description'] }}
                                                </p>
                                                <div class="humidity">
                                                    <img src="/assets/images/icon-1/humidity-xl.svg" alt="Độ ẩm">
                                                    <span>{{ $hour['humidity'] }} %</span>
                                                </div>
                                                <div class="windy">
                                                    <img src="/assets/images/icon-1/ph-wind.svg" alt="Tốc độ gió">
                                                    <span>{{ number_format($hour['wind_speed'], 2) }} km/h</span>
                                                </div>
                                                <i data-feather="chevron-down"></i>
                                            </div>
                                            <div id="collapse{{ $index+24 }}" class="w-100 collapse page_speed_1269422649">
                                                <div class="weather-feature-content">
                                                    <div class="item">
                                                        <div class="icon">
                                                            <img src="/assets/images/icon-1/pressure.svg" alt="Áp suất không khí">
                                                        </div>
                                                        <div class="weather-content">
                                                            <p class="h5">Áp suất</p>
                                                            <p>{{ $hour['pressure'] ?? 1005 }} hPa</p>
                                                        </div>
                                                    </div>
                                                    <div class="item">
                                                        <div class="icon">
                                                            <img src="/assets/images/icon-1/clarity-eye-line.svg" alt="Tầm nhìn xa">
                                                        </div>
                                                        <div class="weather-content">
                                                            <p class="h5">Tầm nhìn</p>
                                                            <p> {{ $hour['visibility'] ?? 10 }} km </p>
                                                        </div>
                                                    </div>
                                                    <div class="item">
                                                        <div class="icon">
                                                            <img src="/assets/images/icon-1/ph-wind.svg" alt="Tốc độ gió">
                                                        </div>
                                                        <div class="weather-content">
                                                            <p class="h5">Gió</p>
                                                            <span>{{ number_format($hour['wind_speed'], 2) }} km/h</span>
                                                        </div>
                                                    </div>
                                                    <div class="item">
                                                        <div class="icon">
                                                            <img src="/assets/images/icon-1/dewpoint.svg" alt="Độ ẩm không khí">
                                                        </div>
                                                        <div class="weather-content">
                                                            <p class="h5">Độ ẩm</p>
                                                            <p>{{ $hour['humidity'] }}%</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary btn-rounded waves-effect waves-light mb-2 me-2 showmore-weather">Xem thêm</button>
                                </div>
                            </div>
                        </div>

                        <!-- Precipitation Chart -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <h2 class="card-title mb-4">Lượng mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} vào ngày mai</h2>
                                <div class="chart-container" style="position: relative;">
                                    <div id="rainfallTomorrowChart" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Temperature Chart -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <h2 class="card-title mb-4">Nhiệt độ {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} vào ngày mai</h2>
                                <div class="chart-container" style="position: relative;">
                                    <div id="temperatureTomorrowChart" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-4 weather-highlight-live">
                        @include('partials.location-sidebar')
                    </div>
                </div>
            </div>

            <!-- Weather city -->
            @if(isset($province) && !isset($ward))
                <section class="weather-city">
                    <div class="container mb-4">
                        <div class="title-main">
                            <h2>{{ isset($district) ? 'Thời tiết phường xã ' . $district->name : 'Thời tiết quận huyện ' . $province->name }}</h2>
                        </div>
                        <ul class="weather-city-inner">
                            @if(isset($district) && isset($wards) && $wards->count() > 0)
                                @foreach($wards as $index => $ward)
                                    <li class="{{ $index < 24 ? 'shown' : 'hide' }}">
                                        <img src="/assets/images/icontt.svg" alt="Thời tiết" height="30" class="me-2">
                                        <h3 class="list-city-lq">
                                            <a href="{{ route('weather.ward.tomorrow', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}">{{ $ward->name }}</a>
                                        </h3>
                                    </li>
                                @endforeach
                            @elseif(isset($districtsWithWeather))
                                @foreach($districtsWithWeather as $index => $district)
                                    <li class="{{ $index < 24 ? 'shown' : 'hide' }}">
                                        <img src="{{ $district['weather_icon'] }}" alt="Thời tiết" height="30" class="me-2">
                                        <h3 class="list-city-lq">
                                            <a href="{{ route('weather.district.tomorrow', [$province->getSlug(), $district['slug'] ?? $district['name']]) }}">{{ $district['name'] }}</a>
                                        </h3>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                        <div>
                            <button type="button" class="btn btn-primary btn-rounded waves-effect waves-light mb-2 me-2 showMoreLocations">Xem thêm</button>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Hourly forecast styling */
        .weather-feature-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .weather-feature-sumary {
            display: grid;
            grid-template-columns: 70px 80px 80px 1fr 80px 80px 30px;
            align-items: center;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .weather-feature-sumary {
                grid-template-columns: 70px 70px 70px 1fr 70px 70px 30px;
            }
        }

        @media (max-width: 576px) {
            .weather-feature-sumary {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }

            .weather-feature-sumary > * {
                margin-bottom: 10px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show more weather items
            const showMoreButton = document.querySelector('.showmore-weather');
            if (showMoreButton) {
                showMoreButton.addEventListener('click', function() {
                    const hiddenItems = document.querySelectorAll('.weather-feature-item.hide');
                    hiddenItems.forEach(item => {
                        item.classList.remove('hide');
                        item.style.display = '';
                    });
                    this.style.display = 'none';
                });
            }

            // Show more locations
            const showMoreLocationsBtn = document.querySelector('.weather-city .showMoreLocations');
            if (showMoreLocationsBtn) {
                showMoreLocationsBtn.addEventListener('click', function() {
                    const hiddenItems = document.querySelectorAll('.weather-city-inner .hide');
                    hiddenItems.forEach(item => {
                        item.classList.remove('hide');
                        item.classList.add('shown');
                    });
                    this.style.display = 'none';
                });
            }

            // Initialize Feather icons (if using)
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Precipitation Chart
            const rainfallChartElement = document.getElementById('rainfallTomorrowChart');
            if (rainfallChartElement && typeof ApexCharts !== 'undefined') {
                // Get tomorrow's hourly data (hours 24-47)
                const tomorrowHourly = @json(array_slice($weatherData['hourly'] ?? [], 24, 24));

                if (tomorrowHourly && tomorrowHourly.length > 0) {
                    // Extract data for specific hours (to match the UI in the images)
                    const displayHours = [1, 4, 7, 10, 13, 16, 19, 22]; // Hours to display
                    const times = [];
                    const precipitationValues = [];

                    tomorrowHourly.forEach(hour => {
                        const hourNum = parseInt(hour.time.split(':')[0]);
                        if (displayHours.includes(hourNum)) {
                            times.push(hour.time);
                            precipitationValues.push(parseFloat(hour.precipitation || 0));
                        }
                    });

                    // Create chart options
                    const options = {
                        series: [{
                            name: 'Lượng mưa (mm)',
                            data: precipitationValues
                        }],
                        chart: {
                            height: 400,
                            type: 'bar',
                            toolbar: {
                                show: false
                            }
                        },
                        colors: ['#0d6efd'],
                        plotOptions: {
                            bar: {
                                borderRadius: 0,
                                columnWidth: '50%',
                                dataLabels: {
                                    position: 'top',
                                },
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val;
                            },
                            offsetY: -20,
                            style: {
                                fontSize: '12px',
                                colors: ["#304758"]
                            }
                        },
                        xaxis: {
                            categories: times,
                            position: 'bottom',
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            },
                            crosshairs: {
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        colorFrom: '#D8E3F0',
                                        colorTo: '#BED1E6',
                                        stops: [0, 100],
                                        opacityFrom: 0.4,
                                        opacityTo: 0.5,
                                    }
                                }
                            }
                        },
                        yaxis: {
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false,
                            },
                            labels: {
                                show: true,
                                formatter: function (val) {
                                    return val.toFixed(1);
                                }
                            },
                            min: 0,
                            max: 2.0,
                            tickAmount: 5
                        },
                        grid: {
                            borderColor: '#e0e0e0',
                            strokeDashArray: 4,
                            xaxis: {
                                lines: {
                                    show: false
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        }
                    };

                    // Create the chart
                    const chart = new ApexCharts(rainfallChartElement, options);
                    chart.render();
                } else {
                    rainfallChartElement.innerHTML = '<div class="alert alert-warning">Không có dữ liệu lượng mưa cho ngày mai</div>';
                }
            }

            // Temperature Chart
            const temperatureChartElement = document.getElementById('temperatureTomorrowChart');
            if (temperatureChartElement && typeof ApexCharts !== 'undefined') {
                // Get tomorrow's hourly data (hours 24-47)
                const tomorrowHourly = @json(array_slice($weatherData['hourly'] ?? [], 24, 24));

                if (tomorrowHourly && tomorrowHourly.length > 0) {
                    // Extract data for specific hours (to match the UI in the images)
                    const displayHours = [1, 4, 7, 10, 13, 16, 19, 22]; // Hours to display
                    const times = [];
                    const temperatureValues = [];

                    tomorrowHourly.forEach(hour => {
                        const hourNum = parseInt(hour.time.split(':')[0]);
                        if (displayHours.includes(hourNum)) {
                            times.push(hour.time);
                            temperatureValues.push(hour.temperature);
                        }
                    });

                    // Create chart options
                    const options = {
                        series: [{
                            name: 'Nhiệt độ (°C)',
                            data: temperatureValues
                        }],
                        chart: {
                            height: 400,
                            type: 'line',
                            toolbar: {
                                show: false
                            }
                        },
                        colors: ['#0d6efd'],
                        stroke: {
                            width: 3,
                            curve: 'smooth'
                        },
                        markers: {
                            size: 6,
                            colors: ['#0d6efd'],
                            strokeColors: '#fff',
                            strokeWidth: 2
                        },
                        dataLabels: {
                            enabled: true,
                            offsetY: -10,
                            style: {
                                fontSize: '12px',
                                colors: ["#304758"]
                            },
                            background: {
                                enabled: true,
                                foreColor: '#000',
                                borderRadius: 2,
                                padding: 4,
                                opacity: 0.7,
                                borderWidth: 1,
                                borderColor: '#fff'
                            }
                        },
                        xaxis: {
                            categories: times,
                            position: 'bottom',
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        },
                        yaxis: {
                            min: function(min) { return min - 5; },
                            max: function(max) { return max + 5; },
                            labels: {
                                formatter: function(val) {
                                    return Math.round(val) + '°';
                                }
                            },
                            title: {
                                text: 'Nhiệt độ'
                            }
                        },
                        grid: {
                            borderColor: '#e0e0e0',
                            strokeDashArray: 4,
                            xaxis: {
                                lines: {
                                    show: false
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return val + '°C';
                                }
                            }
                        }
                    };

                    // Create the chart
                    const chart = new ApexCharts(temperatureChartElement, options);
                    chart.render();
                } else {
                    temperatureChartElement.innerHTML = '<div class="alert alert-warning">Không có dữ liệu nhiệt độ cho ngày mai</div>';
                }
            }
        });
    </script>
@endpush
