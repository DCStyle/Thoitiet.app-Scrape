@php
    // Get current path
    $currentPath = request()->path();
    $currentPath = $currentPath === '/' ? '/' : '/' . $currentPath;

    // Add prefix slash to path if it doesn't have one
    if (!str_starts_with($currentPath, '/')) {
        $currentPath = '/' . $currentPath;
    }

    // Add suffix slash to path if it doesn't have one
    if (!str_ends_with($currentPath, '/')) {
        $currentPath .= '/';
    }

    // Get custom path settings
    $pathTitles = setting('site_path_title') ? json_decode(setting('site_path_title'), true) : [];
    $pathDescriptions = setting('site_path_description') ? json_decode(setting('site_path_description'), true) : [];

    // Try to find matching path in settings
    $customTitle = null;
    $customDescription = null;

    foreach ($pathTitles as $index => $titleData) {
        $settingPath = array_key_first($titleData);

        // Check if current path matches the setting path
        if ($currentPath === $settingPath || rtrim($currentPath, '/') === rtrim($settingPath, '/')) {
            $customTitle = $titleData[$settingPath];

            // Get matching description if it exists
            if (isset($pathDescriptions[$index][$settingPath])) {
                $customDescription = $pathDescriptions[$index][$settingPath];
            }
            break;
        }
    }
@endphp

<title>@yield('title', $customTitle ?? setting('site_name'))</title>

<meta charset="utf-8">
<meta name="description" content="@yield('description', $customDescription ?? setting('site_description'))">
<meta name="keywords" content="@yield('keywords', setting('site_keywords'))">
<meta name="theme-color" content="#ffffff">

<meta property="og:title" content="@yield('title', $customTitle ?? setting('site_name'))">
<meta property="og:description" content="@yield('description', $customDescription ?? setting('site_description'))">

<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ setting('site_name') }}">
<meta property="og:locale" content="vi_VN">
<meta property="og:locale:alternate" content="en_US">
