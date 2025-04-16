@extends('layouts.app')

@section('seo')
    {!! seo($SEOData) !!}
@endsection

@section('content')
    <section class="weather-general">
        <div class="container">
            <div class="weather-general-inner">
                <!-- Main Weather Display -->
                <div class="weather-main">
                    <a href="{{ route('weather.province', $province->getSlug()) }}" class="feature-location d-flex mb-3">
                        <h3 class="weather-main-title">Dự báo thời tiết {{ $weatherData['location'] }}</h3>
                    </a>

                    <div class="weather-main-hero">
                        <img src="{{ asset($weatherData['current']['weather_image']) }}" alt="{{ $weatherData['current']['weather_description'] }}">
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
                            <img src="/assets/images/icon-1/temperature.svg" alt="Nhiệt độ thời tiết">
                            <div class="item-title">Thấp/Cao</div>
                            <div class="temp">
                                <p>{{ $weatherData['daily'][0]['min_temp'] }}°/</p>
                                <p>{{ $weatherData['daily'][0]['max_temp'] }}°</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="/assets/images/icon-1/humidity-xl.svg" alt="Độ ẩm không khí">
                            <div class="item-title">Độ ẩm</div>
                            <div class="temp">
                                <p>{{ $weatherData['current']['humidity'] }} %</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="/assets/images/icon-1/clarity-eye-line.svg" alt="Tầm nhìn xa">
                            <div class="item-title">Tầm nhìn</div>
                            <div class="temp">
                                <p>{{ $weatherData['current']['visibility'] ?? '6' }} km</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="/assets/images/icon-1/ph-wind.svg" alt="Tốc độ gió">
                            <div class="item-title">Gió</div>
                            <div class="temp">
                                <p>{{ $weatherData['current']['wind_speed'] }} km/h</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="/assets/images/icon-1/dawn.svg" alt="Bình minh - Hoàng hôn">
                            <div class="item-title">Bình minh/Hoàng hôn</div>
                            <div class="temp">
                                <p>{{ $weatherData['sunrise'] ?? '05:37' }}/{{ $weatherData['sunset'] ?? '18:15' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Forecast Cards -->
                @foreach(array_slice($weatherData['daily'], 1, 3) as $index => $daily)
                    <div class="weather-sub">
                        <div>
                            <div class="title">{{ $daily['formatted_date'] ?? 'T'.$daily['day_name'].' '.$daily['date'] }}</div>
                            <img src="{{ asset($daily['weather_image']) }}" alt="{{ $daily['weather_description'] }}">
                            <div class="desc">
                                <div class="humidity">
                                    <img src="/assets/images/icon-1/dewpoint.svg" alt="Dự báo lượng mưa">
                                    <span>{{ $daily['precipitation_probability'] > 0 ? $daily['precipitation_probability'] : '0' }}mm</span>
                                </div>
                                <p>{{ $daily['weather_description'] }}</p>
                                <div class="temp">
                                    <span>{{ $daily['min_temp'] }}°</span> / <span>{{ $daily['max_temp'] }}°</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Weather Highlight Section -->
    <section class="weather-highlight">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="weather-highlight-list">
                        <div class="title-main w-100 mb-2"><h1 class="weather-main-title">Dự báo thời tiết 63 tỉnh thành</h1></div>

                        @foreach($featuredLocations as $location)
                            <a href="/{{ Str::slug($location['location']) }}" class="weather-sub">
                                <div>
                                    <div class="title">{{ $location['location'] }}</div>
                                    <img src="{{ asset($location['current']['weather_image']) }}" alt="Thời tiết {{ $location['location'] }} {{ $location['current']['weather_description'] }}">
                                    <div class="desc">
                                        <div class="humidity">
                                            <img src="/assets/images/icon-1/dewpoint.svg" alt="Độ ẩm không khí">
                                            <span>{{ isset($location['current']['precipitation']) && $location['current']['precipitation'] > 0 ? number_format($location['current']['precipitation'], 2) : '0' }}mm</span>
                                        </div>
                                        <p>{{ $location['current']['weather_description'] }}</p>
                                        <div class="temp">
                                            <span>{{ $location['current']['temperature'] }}°</span> / <span>{{ isset($location['daily']) ? $location['daily'][0]['max_temp'] : $location['current']['temperature'] }}°</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach

                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="weather-highlight-live rounded">
                        <div class="title-main"><h2 class="card-title me-2">Bản đồ thời tiết Windy</h2></div>
                        <div class="windy page_speed_746210818">
                            <iframe loading="lazy" title="Bản đồ thời tiết Windy" class="rounded" width="100%" height="100%" src="https://embed.windy.com/embed2.html?lat=16.0&lon=108.0&detailLat=16.0&detailLon=108.0&width=100%25&height=550&zoom=5&level=surface&overlay=wind&product=ecmwf&menu=&message=true&marker=true&calendar=now&pressure=true&type=map&location=coordinates&detail=&metricWind=default&metricTemp=%C2%B0C&radarRange=-1" frameborder="0"></iframe>
                        </div>
                        <div class="card page_speed_1861520856">
                            <div class="calendar calendar-first" id="calendar_first">
                                <div class="calendar_header">
                                    <button class="switch-month switch-left"> <i class="fa fa-chevron-left"></i></button>
                                    <p class="calendar_m">{{ date('F Y') }}</p>
                                    <button class="switch-month switch-right"> <i class="fa fa-chevron-right"></i></button>
                                </div>
                                <div class="calendar_weekdays">
                                    <div style="color: rgb(68, 68, 68);">CN</div>
                                    <div style="color: rgb(68, 68, 68);">Th2</div>
                                    <div style="color: rgb(68, 68, 68);">Th3</div>
                                    <div style="color: rgb(68, 68, 68);">Th4</div>
                                    <div style="color: rgb(68, 68, 68);">Th5</div>
                                    <div style="color: rgb(68, 68, 68);">Th6</div>
                                    <div style="color: rgb(68, 68, 68);">Th7</div>
                                </div>
                                <div class="calendar_content">
                                    @php
                                        $today = date('j');
                                        $month = date('n');
                                        $year = date('Y');
                                        $daysInMonth = date('t');
                                        $firstDay = date('w', strtotime("$year-$month-01"));
                                    @endphp

                                    @for ($i = 0; $i < $firstDay; $i++)
                                        <div class="blank"></div>
                                    @endfor

                                    @for ($i = 1; $i <= $daysInMonth; $i++)
                                        @if ($i < $today)
                                            <div class="past-date">{{ $i }}</div>
                                        @elseif ($i == $today)
                                            <div class="today" style="color: rgb(0, 189, 170);">{{ $i }}</div>
                                        @else
                                            <div>{{ $i }}</div>
                                        @endif
                                    @endfor

                                    @php
                                        $remaining = 42 - $daysInMonth - $firstDay;
                                    @endphp

                                    @for ($i = 0; $i < $remaining; $i++)
                                        <div class="blank"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Provincial Forecast Section -->
    <section class="weather-city">
        <div class="container mb-4">
            <div class="title-main">
                <h2>Thời tiết 63 tỉnh thành</h2>
            </div>
            <ul class="weather-city-inner">
                @php
                    $allProvinces = \App\Models\Province::all();
                    $count = 0;
                @endphp

                @foreach($allProvinces as $province)
                    @php $count++; @endphp
                    <li class="{{ $count > 24 ? 'hide' : 'shown' }}" {{ $count > 24 ? 'style=display:none;' : '' }}>
                        <img src="/assets/images/icontt.svg" alt="Thời tiết">
                        <h3 class="list-city-lq"><a href="/{{ $province->code_name }}">{{ $province->name }}</a></h3>
                    </li>
                @endforeach
            </ul>
            <div class="page_speed_1869062205">
                <button type="button" class="btn btn-primary btn-rounded waves-effect waves-light mb-2 me-2 showMore">Xem thêm</button>
            </div>
        </div>
    </section>

    <section class="news-popular">
        <div class="container mb-4">
            <div class="title-main">
                <h3>Tin tức nổi bật</h3>
            </div>
            <div class="row news-popular-inner">
                @foreach($weatherNews as $news)
                    <div class="col-xl-3 col-md-3 mb-2 card-post">
                        <a rel="nofollow" href="{{ route('articles.show', $news['slug']) }}" class="thumb">
                            <img class="mb-2 w-100 rounded" src="{{ $news['image'] }}" alt="{{ $news['title'] }}">
                        </a>
                        <a rel="nofollow" href="{{ route('articles.show', $news['slug']) }}">
                            <h4 class="card-title me-2">
                                {{ $news['title'] }}
                            </h4>
                        </a>
                    </div>
                @endforeach
            </div>
            <a rel="nofollow" href="{{ route('articles.index') }}"><button class="btn btn-primary btn-rounded waves-effect waves-light mb-2 me-2 showMore">Xem thêm</button></a>
        </div>
    </section>
@endsection
