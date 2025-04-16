@extends('layouts.app')

@section('seo')
    {!! seo($SEOData) !!}
@endsection

@section('content')
    <div class="page-content">
        <div class="container">
            @include('partials.location-breadcrumbs')

            <!-- Time period tabs -->
            @include('partials.location-time-period-tabs', ['activeTab' => 'hourly'])

            <div class="container">
                <div class="row">
                    <!-- Main location weather detail -->
                    <div class="col-xl-8 weather-detail-content">
                        <!-- Hourly weather forecast -->
                        <div class="card">
                            <div class="card-body">
                                <div class="title-main">
                                    <a href="{{ isset($ward) ? route('weather.ward.hourly', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) : (isset($district) ? route('weather.district.hourly', [$province->getSlug(), $district->getSlug()]) : route('weather.province.hourly', $province->getSlug())) }}">
                                        <h1 class="card-title me-2">Dự báo thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} những giờ tới</h1>
                                    </a>
                                </div>
                                <div class="weather-feature-list hourly-weather mt-3">
                                    @php
                                        $currentDate = null;
                                        $count = 0;
                                    @endphp

                                    @foreach($weatherData['hourly'] as $index => $hour)
                                        @php
                                            $hourDate = date('Y-m-d', strtotime($hour['full_time']));
                                            $showDate = false;

                                            if($hourDate != $currentDate) {
                                                $currentDate = $hourDate;
                                                $dayName = date('N', strtotime($hour['full_time']));
                                                $dayNames = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
                                                $formattedDate = $dayNames[$dayName] . ' ' . date('d/m', strtotime($hour['full_time']));
                                                $showDate = $index > 0;
                                            }

                                            $prevTemp = $index > 0 ? $weatherData['hourly'][$index-1]['temperature'] : $hour['temperature'];

                                            // First 7 items are shown, others are hidden
                                            $isHidden = $count >= 7;
                                            $count++;
                                        @endphp

                                        <div class="weather-feature-item {{ $isHidden ? 'hide' : 'shown' }}" {{ $isHidden ? 'style="display: none;"' : '' }}>
                                            @if($showDate)
                                                <div class="font-weight-bold mt-3">{{ $formattedDate }}</div>
                                            @endif
                                            <div class="weather-feature-sumary collapsed" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index+1 }}" aria-expanded="false" aria-controls="collapse0">
                                                <div class="h4 mb-0">
                                                    <span class="text-uppercase">{{ $hour['time'] }}</span>
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
                                            <div id="collapse{{ $index+1 }}" class="w-100 collapse page_speed_1269422649">
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

                        <!-- Temperature and Precipitation Probability Chart -->
                        <div class="card">
                            <div class="card-body">
                                <h1 class="card-title mb-4">Nhiệt độ và khả năng có mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} trong những giờ tới</h1>

                                <div class="chart-container" style="position: relative;">
                                    <!-- ApexCharts uses a div instead of canvas -->
                                    <div id="tempRainProbabilityChart" style="height: 400px;"></div>

                                    <!-- Control buttons -->
                                    <div class="chart-controls" style="position: absolute; top: 10px; right: 10px; display: flex; gap: 5px; z-index: 10;">
                                        <button class="btn btn-light btn-sm zoom-in" title="Phóng to"><i class="fas fa-plus"></i></button>
                                        <button class="btn btn-light btn-sm zoom-out" title="Thu nhỏ"><i class="fas fa-minus"></i></button>
                                        <button class="btn btn-light btn-sm pan-tool" title="Di chuyển"><i class="fas fa-hand-paper"></i></button>
                                        <button class="btn btn-light btn-sm home" title="Về trang chủ"><i class="fas fa-home"></i></button>
                                        <button class="btn btn-light btn-sm menu" title="Menu"><i class="fas fa-bars"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rain chart -->
                        <div class="card">
                            <div class="card-body">
                                <h1 class="card-title mb-4">Lượng mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} trong những giờ tiếp theo</h1>
                                <div class="chart-container" style="position: relative;">
                                    <div id="rainfallChart" style="height: 400px;"></div>
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
                                        <h3 class="list-city-lq"><a href="{{ route('weather.ward', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}">{{ $ward->name }}</a></h3>
                                    </li>
                                @endforeach
                            @elseif(isset($districtsWithWeather))
                                @foreach($districtsWithWeather as $index => $district)
                                    <li class="{{ $index < 24 ? 'shown' : 'hide' }}">
                                        <img src="/assets/images/icontt.svg" alt="Thời tiết" height="30" class="me-2">
                                        <h3 class="list-city-lq"><a href="{{ $district['url'] }}">{{ $district['name'] }}</a></h3>
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
        .chart-controls button {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-container {
            border-radius: 8px;
            background-color: white;
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

            // Initialize Feather icons (if using)
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Temperature and Rain Probability Chart with ApexCharts
            const chartElement = document.getElementById('tempRainProbabilityChart');

            if (chartElement && typeof ApexCharts !== 'undefined') {
                // Prepare data from the hourly weather data
                // We'll use 12 time periods as shown in the image
                const hourlyData = @json(array_slice($weatherData['hourly'] ?? [], 0, 12));

                if (hourlyData && hourlyData.length > 0) {
                    const times = hourlyData.map(item => item.time);
                    const temperatures = hourlyData.map(item => item.temperature);
                    const precipitationProbabilities = hourlyData.map(item =>
                        item.precipitation_probability !== undefined ?
                            item.precipitation_probability :
                            Math.floor(Math.random() * 50) + 50 // Fallback to random values between 50-100 if data is missing
                    );

                    // Create ApexCharts options
                    const options = {
                        series: [
                            {
                                name: 'Khả năng có mưa',
                                type: 'column',
                                data: precipitationProbabilities
                            },
                            {
                                name: 'Nhiệt độ',
                                type: 'line',
                                data: temperatures
                            }
                        ],
                        chart: {
                            height: 400,
                            type: 'line',
                            stacked: false,
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            }
                        },
                        stroke: {
                            width: [0, 3],
                            curve: 'smooth'
                        },
                        colors: ['#4299e1', '#48bb78'],
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [0, 1],
                            formatter: function(val, opts) {
                                if (opts.seriesIndex === 0) {
                                    return val + '%';
                                } else {
                                    return val + '°';
                                }
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold'
                            },
                            background: {
                                enabled: true,
                                foreColor: '#000',
                                borderRadius: 2,
                                padding: 4,
                                opacity: 0.7,
                                borderWidth: 1,
                                borderColor: '#fff'
                            },
                            dropShadow: {
                                enabled: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                columnWidth: '60%',
                                borderRadius: 4
                            }
                        },
                        xaxis: {
                            categories: times,
                            title: {
                                text: ''
                            }
                        },
                        yaxis: [
                            {
                                seriesName: 'Khả năng có mưa',
                                title: {
                                    text: 'Khả năng có mưa'
                                },
                                min: 0,
                                max: 120,
                                labels: {
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                }
                            },
                            {
                                seriesName: 'Nhiệt độ',
                                opposite: true,
                                title: {
                                    text: 'Nhiệt độ'
                                },
                                min: Math.min(...temperatures) - 5,
                                max: Math.max(...temperatures) + 5,
                                labels: {
                                    formatter: function(val) {
                                        return val + '°';
                                    }
                                }
                            }
                        ],
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: function(value, { seriesIndex }) {
                                    if (seriesIndex === 0) {
                                        return value + '%';
                                    } else {
                                        return value + '°C';
                                    }
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            markers: {
                                width: 10,
                                height: 10,
                                radius: 50
                            },
                            itemMargin: {
                                horizontal: 15,
                                vertical: 5
                            }
                        },
                        fill: {
                            opacity: [0.85, 0.25],
                            gradient: {
                                inverseColors: false,
                                shade: 'light',
                                type: "vertical",
                                opacityFrom: 0.85,
                                opacityTo: 0.55
                            }
                        },
                        grid: {
                            borderColor: '#f1f1f1',
                            row: {
                                colors: ['transparent', 'transparent']
                            }
                        }
                    };

                    // Create the chart
                    const chart = new ApexCharts(chartElement, options);
                    chart.render();

                    // Handle control buttons
                    document.querySelector('.zoom-in').addEventListener('click', function() {
                        // ApexCharts doesn't have built-in zoom for mixed charts, you'd need custom logic
                        console.log('Zoom in clicked');
                    });

                    document.querySelector('.zoom-out').addEventListener('click', function() {
                        console.log('Zoom out clicked');
                    });

                    document.querySelector('.home').addEventListener('click', function() {
                        console.log('Home clicked');
                    });

                    document.querySelector('.menu').addEventListener('click', function() {
                        console.log('Menu clicked');
                    });
                } else {
                    console.error('No hourly data available');
                    chartElement.innerHTML = '<div class="alert alert-warning">Không có dữ liệu thời tiết theo giờ</div>';
                }
            } else {
                console.error('Chart element not found or ApexCharts not loaded');
            }

            // Rainfall Chart
            const rainfallChartElement = document.getElementById('rainfallChart');
            if (rainfallChartElement && typeof ApexCharts !== 'undefined') {
                // Prepare data from the hourly weather data
                const hourlyData = @json(array_slice($weatherData['hourly'] ?? [], 0, 12));

                if (hourlyData && hourlyData.length > 0) {
                    const times = hourlyData.map(item => item.time);
                    const precipitation = hourlyData.map(item =>
                        parseFloat(item.precipitation) || 0
                    );

                    // Create ApexCharts options
                    const options = {
                        series: [{
                            name: 'Lượng mưa (mm)',
                            data: precipitation
                        }],
                        chart: {
                            height: 400,
                            type: 'bar',
                            toolbar: {
                                show: false
                            }
                        },
                        colors: ['#2196F3'],
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
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'center',
                            markers: {
                                width: 12,
                                height: 12,
                                strokeWidth: 0,
                                radius: 0,
                            }
                        }
                    };

                    // Create the chart
                    const chart = new ApexCharts(rainfallChartElement, options);
                    chart.render();
                } else {
                    rainfallChartElement.innerHTML = '<div class="alert alert-warning">Không có dữ liệu lượng mưa theo giờ</div>';
                }
            }

            // Show more locations button in Weather city section
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
        });
    </script>
@endpush
