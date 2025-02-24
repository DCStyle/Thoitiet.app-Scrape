<!DOCTYPE html>
<html>
<head>
    @yield('seo')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(isset($metadata))
        <title>{{ $metadata['title'] }}</title>

        @if($metadata['description'])
            <meta name="description" content="{{ $metadata['description'] }}">
        @endif

        @if($metadata['keywords'])
            <meta name="keywords" content="{{ $metadata['keywords'] }}">
        @endif

        @if($metadata['canonical'])
            <link rel="canonical" href="{{ $metadata['canonical'] }}">
        @endif

        @foreach($metadata['og_tags'] ?? [] as $property => $content)
            <meta property="{{ $property }}" content="{{ $content }}">
        @endforeach

        @foreach($metadata['twitter_tags'] ?? [] as $name => $content)
            <meta name="{{ $name }}" content="{{ $content }}">
        @endforeach
    @else
        @include('partials.metadata')
    @endif

    <meta property="og:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">
    <meta name="twitter:creator" content="{{ setting('site_creator', 'Kết Quả Xổ Số') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ setting('site_favicon') ? asset(Storage::url(setting('site_favicon'))) : 'https://placehold.co/16' }}">

    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">

    @vite(['resources/css/app.css'])

    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.30.0/apexcharts.min.css" integrity="sha512-Tv+8HvG00Few62pkPxSs1WVfPf9Hft4U1nMD6WxLxJzlY/SLhfUPFPP6rovEmo4zBgwxMsArU6EkF11fLKT8IQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/thoitiet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://atugatran.github.io/FontAwesome6Pro/css/all.min.css" >

    @stack('styles')
</head>

<body data-topbar="dark" data-layout="horizontal">

<div id="layout-wrapper">
    @include('partials.header')

    <div class="main-content">
        @yield('content')

        @include('partials.footer')
    </div>
</div>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.30.0/apexcharts.min.js" integrity="sha512-8oAJJAAX6u9B9WF3TnDEYXMJAXUj2BDC9ZnlYCxR90WHBW9l870FKUS3f+mgLXDoI3mSz74n8CE3+QiYjAWfrg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    $('.carousel').on('touchstart', function(event){
        const xClick = event.originalEvent.touches[0].pageX;
        $(this).one('touchmove', function(event){
            const xMove = event.originalEvent.touches[0].pageX;
            const sensitivityInPx = 5;

            if( Math.floor(xClick - xMove) > sensitivityInPx ){
                $(this).carousel('next');
            }
            else if( Math.floor(xClick - xMove) < -sensitivityInPx ){
                $(this).carousel('prev');
            }
        });
        $(this).on('touchend', function(){
            $(this).off('touchmove');
        });
    });
</script>

<script src="{{ asset('js/thoitiet.js') }}"></script>
<script src="{{ asset('js/footer.js') }}"></script>

<script src="{{ asset('js/temp_rain_hourly.js') }}"></script>
<script src="{{ asset('js/temp_rain_daily.js') }}"></script>
<script src="{{ asset('js/rains_hourly.js') }}"></script>

@vite(['resources/js/app.js'])

@stack('scripts')
</body>
</html>
