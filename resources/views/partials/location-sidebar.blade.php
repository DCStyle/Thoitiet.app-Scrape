<!-- Weather News -->
<div class="card rounded-4 border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h3 class="fw-bold text-primary mb-3">Tin tức thời tiết</h3>

        <div>
            @foreach($weatherNews as $news)
                <div class="mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ $news['image'] }}" class="card-img-top" alt="{{ $news['title'] }}">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('articles.show', $news['slug']) }}" class="text-decoration-none text-dark">
                                    {{ $news['title'] }}
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Air Quality -->
<div class="card">
    <div class="card-body">
        <div class="title-main">
            <h2 class="card-title me-2">Chất lượng không khí</h2>
        </div>
        <div class="air-quality-content air-5">
            <p class="title">{{ $airQualityData['aqi_category'] ?? 'Rất kém' }}</p>
            <p class="desc">{{ $airQualityData['description'] }}</p>
        </div>
        <div class="air-quality-list">
            <div class="air-quality-item">
                <div class="title">CO</div>
                <p>{{ $airQualityData['co'] }}</p>
            </div>
            <div class="air-quality-item">
                <div class="title">NH<sub>3</sub></div>
                <p>{{ $airQualityData['nh3'] }}</p>
            </div>
            <div class="air-quality-item">
                <div class="title">NO</div>
                <p>{{ $airQualityData['no'] }}</p>
            </div>
            <div class="air-quality-item">
                <div class="title">NO<sub>2</sub></div>
                <p>{{ $airQualityData['no2'] }}</p>
            </div>
        </div>
        <div class="air-quality-list">
            <div class="air-quality-item">
                <div class="title">O<sub>3</sub></div>
                <p>{{ $airQualityData['o3'] }}</p>
            </div>
            <div class="air-quality-item">
                <div class="title">PM<sub>10</sub></div>
                <p>{{ $airQualityData['pm10'] }}</p>
            </div>
            <div class="air-quality-item">
                <div class="title">PM<sub>2.5</sub></div>
                <p>{{ $airQualityData['pm2_5'] }}</p>
            </div>
            <div class="air-quality-item">
                <div class="title">SO<sub>2</sub></div>
                <p>{{ $airQualityData['so2'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="sunrise-sunset">
    <div class="row">
        <div class="col-6">
            <p class="title">Bình minh</p>
            <p class="sunrise-sunset-time text-uppercase">{{ $sunriseSunsetData['sunrise'] }}</p>
        </div>
        <div class="col-6">
            <p class="title">Hoàng hôn</p>
            <p class="sunrise-sunset-time text-uppercase">{{ $sunriseSunsetData['sunset'] }}</p>
        </div>
    </div>
</div>
