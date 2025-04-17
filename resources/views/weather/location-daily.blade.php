@extends('layouts.app')

@section('seo')
    {!! seo($SEOData) !!}
@endsection

@section('content')
    <div class="page-content">
        <div class="container">
            @include('partials.location-breadcrumbs')

            <!-- Time period tabs -->
            @include('partials.location-time-period-tabs', ['activeTab' => 'daily-' . $daysCount])

            <div class="container">
                <div class="row">
                    <!-- Main content area -->
                    <div class="col-xl-8 weather-detail-content">
                        <h1 class="mb-4">Dự báo thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} {{ $daysCount }} ngày tới</h1>

                        <!-- Weather forecast cards for each day -->
                        @foreach($weatherData['daily'] as $index => $day)
                            @if($index < $daysCount)
                                @php
                                    $dayName = $day['day_name'] ?? '';
                                    $date = $day['date'] ?? '';
                                    $weatherImage = isset($day['weather_image']) ? asset($day['weather_image']) : '/assets/images/weather-1/04d.png';
                                    $maxTemp = $day['max_temp'] ?? 29;
                                    $minTemp = $day['min_temp'] ?? 22;
                                    $weatherDesc = $day['weather_description'] ?? 'mây đen u ám';
                                    $feelsLike = $maxTemp - 1; // Approximate feels like temp
                                    $morningTemp = $timeOfDayTemps['morning']['temperature'] ?? 19;
                                    $eveningTemp = $timeOfDayTemps['evening']['temperature'] ?? 31;
                                    $precipitationSum = $day['precipitation_sum'] ?? 0;
                                    $humidity = $weatherData['hourly'][$index * 24]['humidity'] ?? 36;
                                    $windSpeed = isset($weatherData['hourly'][$index*24]['wind_speed']) ? number_format($weatherData['hourly'][$index*24]['wind_speed'], 2) : '6.51';
                                    $pressure = $weatherData['hourly'][$index * 24]['pressure'] ?? 1010;
                                    $sunrise = $day['sunrise'] ?? '05:36';
                                    $sunset = $day['sunset'] ?? '18:15';

                                    // Data for charts - placeholder arrays with 8 values
                                    $rainData = '[0,0,0,0,0,0,0,0]'; // Replace with actual data
                                    $tempData = '[' . ($maxTemp + 2) . ',' . $maxTemp . ',' . ($maxTemp - 4) . ',' . $minTemp . ',' . ($minTemp + 2) . ',' . ($minTemp + 4) . ',' . ($maxTemp - 1) . ',' . $maxTemp . ']';
                                @endphp

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="title-main">
                                            <h2 class="font-weight-bold mt-3">{{ $dayName }} - {{ $date }}</h2>
                                        </div>
                                        <div class="weather-main-hero">
                                            <img src="{{ $weatherImage }}" alt="Thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} {{ $dayName }} - {{ $date }}">
                                            <p class="temp">
                                                {{ $maxTemp }}°
                                            </p>
                                            <div class="desc">
                                                <p>{{ $weatherDesc }}</p>
                                                <span>Cảm giác như <span>{{ $feelsLike }}°</span></span>
                                            </div>
                                            <div class="extra">
                                                <div class="item">
                                                    <div class="icon"><img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết"></div>
                                                    <div>Ngày/Đêm:</div>
                                                    <div class="temp">
                                                        <p>
                                                            {{ $maxTemp }}°
                                                        </p>/
                                                        <p>
                                                            {{ $minTemp }}°
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="item">
                                                    <div class="icon"><img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết"></div>
                                                    <div>Sáng/Tối:</div>
                                                    <div class="temp">
                                                        <p>
                                                            {{ $morningTemp }}°
                                                        </p>/
                                                        <p>
                                                            {{ $eveningTemp }}°
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="item">
                                                    <div class="icon"><img src="/assets/images/icon-1/line-md-paint.svg" alt="Lượng mưa"></div>
                                                    <div>Lượng mưa:</div>
                                                    <div class="temp">
                                                        <p>
                                                            {{ $precipitationSum }} mm
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="weather-main-desc">
                                            <div class="item">
                                                <img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết">
                                                <div class="item-title">Thấp/Cao</div>
                                                <div class="temp">
                                                    <p>{{ $minTemp }}°/</p>
                                                    <p>{{ $maxTemp }}°</p>
                                                </div>
                                            </div>
                                            <div class="item">
                                                <img src="/assets/images/icon-1/humidity-xl.svg" alt="Dự báo độ ẩm thời tiết {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }}">
                                                <div class="item-title">Độ ẩm</div>
                                                <div class="temp">
                                                    <p>{{ $humidity }} %</p>
                                                </div>
                                            </div>

                                            <div class="item">
                                                <img src="/assets/images/icon-1/ph-wind.svg" alt="Dự báo tốc độ gió">
                                                <div class="item-title">Gió</div>
                                                <div class="temp">
                                                    <p>{{ $windSpeed }} km/h</p>
                                                </div>
                                            </div>
                                            <div class="item">
                                                <img src="/assets/images/icon-1/pressure.svg" alt="Áp suất không khí">
                                                <div class="item-title">Áp suất</div>
                                                <div class="temp">
                                                    <p>{{ $pressure }} hPa</p>
                                                </div>
                                            </div>
                                            <div class="item">
                                                <img src="/assets/images/icon-1/dawn.svg" alt="Thời gian Bình minh và Hoàng hôn">
                                                <div class="item-title">Bình minh/Hoàng hôn</div>
                                                <div class="temp">
                                                    <p>
                                                        {{ $sunrise }}/{{ $sunset }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="weather-nextday-chart">
                                            <div class="charts_day_{{ $index + 1 }}">
                                                <div class="title-main">
                                                    <h3 class="card-title me-2">Lượng mưa {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} {{ $dayName }} - {{ $date }}</h3>
                                                </div>
                                                <div id="rain_hourly_day_{{ $index + 1 }}" data-colors="[&quot;#1c84ee&quot;]" data-rains="{{ $rainData }}" class="apex-charts" dir="ltr"></div>
                                                <div class="title-main">
                                                    <h3 class="card-title me-2">Nhiệt độ {{ isset($ward) ? $ward->name : (isset($district) ? $district->name : $province->name) }} {{ $dayName }} - {{ $date }}</h3>
                                                </div>
                                                <div id="temp_hourly_day_{{ $index + 1 }}" data-colors="[&quot;#1c84ee&quot;]" data-temps="{{ $tempData }}" class="apex-charts" dir="ltr"></div>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-primary btn-rounded waves-effect waves-light mb-2 me-2 showdetail_day_{{ $index + 1 }}">Xem chi tiết</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-4 weather-highlight-live">
                        @include('partials.location-sidebar')
                    </div>
                </div>

                <!-- Districts section (if not viewing a district or ward) -->
                @if(isset($province) && !isset($district))
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="mb-4">Thời tiết quận huyện {{ $province->name }}</h2>
                            <div class="row">
                                @if(isset($districtsWithWeather))
                                    @foreach($districtsWithWeather as $index => $district)
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $district['weather_icon'] }}" alt="Thời tiết" height="30" class="me-2">
                                                <a href="{{ $district['url'] }}" class="text-decoration-none text-dark">
                                                    {{ $district['name'] }}
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @if(isset($districtsWithWeather) && count($districtsWithWeather) > 12)
                                <div class="text-center mt-3">
                                    <button id="showMoreDistricts" class="btn btn-primary btn-sm">Xem thêm</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .weather-main-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
        }

        .weather-main-hero img {
            height: 80px;
            margin-right: 20px;
        }

        .weather-main-hero .temp {
            font-size: 3rem;
            font-weight: bold;
            margin: 0 20px 0 0;
        }

        .weather-main-hero .desc {
            margin-right: 30px;
        }

        .weather-main-hero .extra {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .weather-main-hero .extra .item {
            display: flex;
            align-items: center;
            margin-right: 30px;
            margin-bottom: 10px;
        }

        .weather-main-desc {
            display: flex;
            flex-wrap: wrap;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .weather-main-desc .item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 40px;
            margin-bottom: 15px;
        }

        .weather-main-desc .item img {
            height: 30px;
            margin-bottom: 10px;
        }

        .weather-nextday-chart {
            margin-top: 30px;
        }

        .apex-charts {
            min-height: 250px;
        }

        @media (max-width: 768px) {
            .weather-main-hero .temp {
                font-size: 2.5rem;
            }

            .weather-main-desc .item {
                margin-right: 20px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts for each day
            @foreach($weatherData['daily'] as $index => $day)
            @if($index < $daysCount)
            // Rain chart
            var rainOptions_{{ $index + 1 }} = {
                series: [{
                    name: "Lượng mưa",
                    data: JSON.parse(document.getElementById('rain_hourly_day_{{ $index + 1 }}').getAttribute('data-rains'))
                }],
                chart: {
                    height: 250,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#1c84ee'],
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: ['01:00', '04:00', '07:00', '10:00', '13:00', '16:00', '19:00', '22:00'],
                },
                yaxis: {
                    title: {
                        text: 'mm'
                    }
                }
            };

            var rainChart_{{ $index + 1 }} = new ApexCharts(document.getElementById('rain_hourly_day_{{ $index + 1 }}'), rainOptions_{{ $index + 1 }});
            rainChart_{{ $index + 1 }}.render();

            // Temperature chart
            var tempOptions_{{ $index + 1 }} = {
                series: [{
                    name: "Nhiệt độ",
                    data: JSON.parse(document.getElementById('temp_hourly_day_{{ $index + 1 }}').getAttribute('data-temps'))
                }],
                chart: {
                    height: 250,
                    type: 'line',
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#1c84ee'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                markers: {
                    size: 4
                },
                xaxis: {
                    categories: ['01:00', '04:00', '07:00', '10:00', '13:00', '16:00', '19:00', '22:00'],
                },
                yaxis: {
                    title: {
                        text: '°C'
                    }
                }
            };

            var tempChart_{{ $index + 1 }} = new ApexCharts(document.getElementById('temp_hourly_day_{{ $index + 1 }}'), tempOptions_{{ $index + 1 }});
            tempChart_{{ $index + 1 }}.render();

            // Show/hide chart detail buttons
            var detailBtn_{{ $index + 1 }} = document.querySelector('.showdetail_day_{{ $index + 1 }}');
            var charts_{{ $index + 1 }} = document.querySelector('.charts_day_{{ $index + 1 }}');

            if(detailBtn_{{ $index + 1 }} && charts_{{ $index + 1 }}) {
                charts_{{ $index + 1 }}.style.display = 'none';

                detailBtn_{{ $index + 1 }}.addEventListener('click', function() {
                    if(charts_{{ $index + 1 }}.style.display === 'none') {
                        charts_{{ $index + 1 }}.style.display = 'block';
                        this.textContent = 'Ẩn chi tiết';
                    } else {
                        charts_{{ $index + 1 }}.style.display = 'none';
                        this.textContent = 'Xem chi tiết';
                    }
                });
            }
            @endif
            @endforeach

            // Show more districts button
            var showMoreBtn = document.getElementById('showMoreDistricts');
            if(showMoreBtn) {
                var districtItems = document.querySelectorAll('.col-lg-4.col-md-6.mb-3');

                for(var i = 12; i < districtItems.length; i++) {
                    districtItems[i].style.display = 'none';
                }

                showMoreBtn.addEventListener('click', function() {
                    for(var i = 0; i < districtItems.length; i++) {
                        districtItems[i].style.display = 'block';
                    }
                    this.style.display = 'none';
                });
            }
        });
    </script>
@endpush
