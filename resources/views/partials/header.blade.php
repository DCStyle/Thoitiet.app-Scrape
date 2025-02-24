<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <button aria-label="btnMenu" type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-right">
                        <line x1="21" y1="10" x2="7" y2="10"></line>
                        <line x1="21" y1="6" x2="3" y2="6"></line>
                        <line x1="21" y1="14" x2="3" y2="14"></line>
                        <line x1="21" y1="18" x2="7" y2="18"></line>
                    </svg>
                </i>
            </button>

            <div class="navbar-brand-box">
                <a href="/" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ setting('site_logo') ? Storage::url(setting('site_logo')) : 'https://placehold.co/200x36' }}" alt="{{ setting('site_name') }}" height="36">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ setting('site_logo') ? Storage::url(setting('site_logo')) : 'https://placehold.co/200x36' }}" alt="{{ setting('site_name') }}" height="40">
                        <span class="logo-txt"></span>
                    </span>
                </a>

                <a href="/" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ setting('site_logo') ? Storage::url(setting('site_logo')) : 'https://placehold.co/200x36' }}" alt="{{ setting('site_name') }}" height="36">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ setting('site_logo') ? Storage::url(setting('site_logo')) : 'https://placehold.co/200x36' }}" alt="{{ setting('site_name') }}" height="40">
                        <span class="logo-txt"></span>
                    </span>
                </a>
            </div>

            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" id="thoi-tiet-search-header" class="form-control" placeholder="Tìm kiếm...">
                    <button aria-label="btnSearch" class="btn btn-primary" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
                <div class="thoi-tiet-search-header-result"></div>
            </form>
        </div>

        <div class="d-flex">
            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </i>
                </button>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" id="m-thoi-tiet-search-header" class="form-control" placeholder="Tìm kiếm ..." aria-label="Kết quả tìm kiếm">
                                <button id="m-thoi-tiet-search-header" class="btn btn-primary" type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </button>
                            </div>
                            <div class="m-thoi-tiet-search-header-result"></div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="time-local-btn">
                    <span>
                        <i>
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                          </svg>
                        </i>

                        Giờ địa phương:
                    </span>

                    <span id="s-date-time">
                        <span id="gio"></span>
                        <span>:</span>
                        <span id="phut"></span>
                        <span>:</span>
                        <span id="giay"></span>
                        <span></span>
                        <span id="ngay_thang"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</header>

@include('partials.top-nav')
