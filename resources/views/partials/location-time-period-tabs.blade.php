<!-- Time period tabs -->
<div class="weather-menu">
    <div class="container">
        <div class="weather-menu-overlay">
            <ul class="weather-menu-inner">
                <li class="weather-menu-item {{ $activeTab === 'current' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">Hiện tại</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">Hiện tại</a>
                    @else
                        <a href="{{ route('weather.province', $province->getSlug()) }}" class="weather-menu-link">Hiện tại</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'hourly' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.hourly', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">Theo giờ</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.hourly', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">Theo giờ</a>
                    @else
                        <a href="{{ route('weather.province.hourly', $province->getSlug()) }}" class="weather-menu-link">Theo giờ</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'tomorrow' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.tomorrow', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">Ngày mai</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.tomorrow', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">Ngày mai</a>
                    @else
                        <a href="{{ route('weather.province.tomorrow', $province->getSlug()) }}" class="weather-menu-link">Ngày mai</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-3' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.3', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">3 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.3', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">3 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.3', $province->getSlug()) }}" class="weather-menu-link">3 ngày tới</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-5' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.5', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">5 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.5', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">5 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.5', $province->getSlug()) }}" class="weather-menu-link">5 ngày tới</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-7' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.7', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">7 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.7', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">7 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.7', $province->getSlug()) }}" class="weather-menu-link">7 ngày tới</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-10' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.10', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">10 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.10', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">10 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.10', $province->getSlug()) }}" class="weather-menu-link">10 ngày tới</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-15' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.15', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">15 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.15', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">15 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.15', $province->getSlug()) }}" class="weather-menu-link">15 ngày tới</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-20' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.20', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">20 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.20', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">20 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.20', $province->getSlug()) }}" class="weather-menu-link">20 ngày tới</a>
                    @endif
                </li>
                <li class="weather-menu-item {{ $activeTab === 'daily-30' ? 'active' : '' }}">
                    @if(isset($ward))
                        <a href="{{ route('weather.ward.daily.30', [$province->getSlug(), $district->getSlug(), $ward->getSlug()]) }}" class="weather-menu-link">30 ngày tới</a>
                    @elseif(isset($district))
                        <a href="{{ route('weather.district.daily.30', [$province->getSlug(), $district->getSlug()]) }}" class="weather-menu-link">30 ngày tới</a>
                    @else
                        <a href="{{ route('weather.province.daily.30', $province->getSlug()) }}" class="weather-menu-link">30 ngày tới</a>
                    @endif
                </li>
            </ul>
        </div>
    </div>
</div>
