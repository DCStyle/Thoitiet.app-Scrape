<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>

        @if(isset($province))
            <li class="breadcrumb-item {{ !isset($district) ? 'active' : '' }}">
                @if(!isset($district))
                    {{ $province->name }}
                @else
                    <a href="{{ route('weather.province', $province->getSlug()) }}">{{ $province->name }}</a>
                @endif
            </li>
        @endif

        @if(isset($district))
            <li class="breadcrumb-item {{ !isset($ward) ? 'active' : '' }}">
                @if(!isset($ward))
                    {{ $district->name }}
                @else
                    <a href="{{ route('weather.district', [$province->getSlug(), $district->getSlug()]) }}">{{ $district->name }}</a>
                @endif
            </li>
        @endif

        @if(isset($ward))
            <li class="breadcrumb-item active">{{ $ward->name }}</li>
        @endif
    </ol>
</nav>
