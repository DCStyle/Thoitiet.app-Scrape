<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand me-4" href="/">
            <img src="{{ setting('site_logo') ? Storage::url(setting('site_logo')) : 'https://placehold.co/200x36' }}" alt="{{ setting('site_name') }}" height="36">
        </a>

        <!-- Search Form - Main visible on desktop -->
        <div class="d-none d-lg-block flex-grow-1 me-4">
            <div class="input-group">
                <input type="text" id="thoi-tiet-search-header" class="form-control" placeholder="Nhập tên địa điểm">
                <div class="thoi-tiet-search-header-result position-absolute w-100 bg-white" style="top: 100%; left: 0; z-index: 1000;"></div>
            </div>
        </div>

        <!-- Province/City Dropdown -->
        <div class="dropdown me-3">
            <button class="btn dropdown-toggle" type="button" id="dropdownProvinceButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-city"></i> Tỉnh/Thành phố
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down ms-1"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="dropdown-menu dropdown-menu-end dropdown-mega-menu-xl p-3" aria-labelledby="dropdownProvinceButton">
                <div class="row">
                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Đông Bắc Bộ</h6>
                        <div class="d-flex flex-column">
                            <a href="/bac-giang" class="dropdown-item" data-key="t-lightbox">Bắc Giang</a>
                            <a href="/bac-kan" class="dropdown-item" data-key="t-lightbox">Bắc Kạn</a>
                            <a href="/cao-bang" class="dropdown-item" data-key="t-lightbox">Cao Bằng</a>
                            <a href="/ha-giang" class="dropdown-item" data-key="t-lightbox">Hà Giang</a>
                            <a href="/lang-son" class="dropdown-item" data-key="t-lightbox">Lạng Sơn</a>
                            <a href="/phu-tho" class="dropdown-item" data-key="t-lightbox">Phú Thọ</a>
                            <a href="/quang-ninh" class="dropdown-item" data-key="t-lightbox">Quảng Ninh</a>
                            <a href="/thai-nguyen" class="dropdown-item" data-key="t-lightbox">Thái Nguyên</a>
                            <a href="/tuyen-quang" class="dropdown-item" data-key="t-lightbox">Tuyên Quang</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Tây Bắc Bộ</h6>
                        <div class="d-flex flex-column">
                            <a href="/hoa-binh" class="dropdown-item" data-key="t-lightbox">Hoà Bình</a>
                            <a href="/lai-chau" class="dropdown-item" data-key="t-lightbox">Lai Châu</a>
                            <a href="/lao-cai" class="dropdown-item" data-key="t-lightbox">Lào Cai</a>
                            <a href="/son-la" class="dropdown-item" data-key="t-lightbox">Sơn La</a>
                            <a href="/yen-bai" class="dropdown-item" data-key="t-lightbox">Yên Bái</a>
                            <a href="/dien-bien" class="dropdown-item" data-key="t-lightbox">Điện Biên</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Đồng bằng sông Hồng</h6>
                        <div class="d-flex flex-column">
                            <a href="/bac-ninh" class="dropdown-item" data-key="t-lightbox">Bắc Ninh</a>
                            <a href="/ha-nam" class="dropdown-item" data-key="t-lightbox">Hà Nam</a>
                            <a href="/ha-noi" class="dropdown-item" data-key="t-lightbox">Hà Nội</a>
                            <a href="/hai-duong" class="dropdown-item" data-key="t-lightbox">Hải Dương</a>
                            <a href="/hai-phong" class="dropdown-item" data-key="t-lightbox">Hải Phòng</a>
                            <a href="/hung-yen" class="dropdown-item" data-key="t-lightbox">Hưng Yên</a>
                            <a href="/nam-dinh" class="dropdown-item" data-key="t-lightbox">Nam Định</a>
                            <a href="/ninh-binh" class="dropdown-item" data-key="t-lightbox">Ninh Bình</a>
                            <a href="/thai-binh" class="dropdown-item" data-key="t-lightbox">Thái Bình</a>
                            <a href="/vinh-phuc" class="dropdown-item" data-key="t-lightbox">Vĩnh Phúc</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Bắc Trung Bộ</h6>
                        <div class="d-flex flex-column">
                            <a href="/ha-tinh" class="dropdown-item" data-key="t-lightbox">Hà Tĩnh</a>
                            <a href="/nghe-an" class="dropdown-item" data-key="t-lightbox">Nghệ An</a>
                            <a href="/quang-binh" class="dropdown-item" data-key="t-lightbox">Quảng Bình</a>
                            <a href="/quang-tri" class="dropdown-item" data-key="t-lightbox">Quảng Trị</a>
                            <a href="/thanh-hoa" class="dropdown-item" data-key="t-lightbox">Thanh Hóa</a>
                            <a href="/thua-thien-hue" class="dropdown-item" data-key="t-lightbox">Thừa Thiên Huế</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Nam Trung Bộ</h6>
                        <div class="d-flex flex-column">
                            <a href="/binh-thuan" class="dropdown-item" data-key="t-lightbox">Bình Thuận</a>
                            <a href="/binh-dinh" class="dropdown-item" data-key="t-lightbox">Bình Định</a>
                            <a href="/khanh-hoa" class="dropdown-item" data-key="t-lightbox">Khánh Hòa</a>
                            <a href="/ninh-thuan" class="dropdown-item" data-key="t-lightbox">Ninh Thuận</a>
                            <a href="/phu-yen" class="dropdown-item" data-key="t-lightbox">Phú Yên</a>
                            <a href="/quang-nam" class="dropdown-item" data-key="t-lightbox">Quảng Nam</a>
                            <a href="/quang-ngai" class="dropdown-item" data-key="t-lightbox">Quảng Ngãi</a>
                            <a href="/da-nang" class="dropdown-item" data-key="t-lightbox">Đà Nẵng</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Tây Nguyên</h6>
                        <div class="d-flex flex-column">
                            <a href="/gia-lai" class="dropdown-item" data-key="t-lightbox">Gia Lai</a>
                            <a href="/kon-tum" class="dropdown-item" data-key="t-lightbox">Kon Tum</a>
                            <a href="/lam-dong" class="dropdown-item" data-key="t-lightbox">Lâm Đồng</a>
                            <a href="/dak-lak" class="dropdown-item" data-key="t-lightbox">Đắk Lắk</a>
                            <a href="/dak-nong" class="dropdown-item" data-key="t-lightbox">Đắk Nông</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Đông Nam Bộ</h6>
                        <div class="d-flex flex-column">
                            <a href="/ba-ria-vung-tau" class="dropdown-item" data-key="t-lightbox">Bà Rịa - Vũng Tàu</a>
                            <a href="/binh-duong" class="dropdown-item" data-key="t-lightbox">Bình Dương</a>
                            <a href="/binh-phuoc" class="dropdown-item" data-key="t-lightbox">Bình Phước</a>
                            <a href="/ho-chi-minh" class="dropdown-item" data-key="t-lightbox">Hồ Chí Minh</a>
                            <a href="/tay-ninh" class="dropdown-item" data-key="t-lightbox">Tây Ninh</a>
                            <a href="/dong-nai" class="dropdown-item" data-key="t-lightbox">Đồng Nai</a>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <h6 class="fw-bold">Đồng bằng sông Cửu Long</h6>
                        <div class="d-flex flex-column">
                            <a href="/an-giang" class="dropdown-item" data-key="t-lightbox">An Giang</a>
                            <a href="/bac-lieu" class="dropdown-item" data-key="t-lightbox">Bạc Liêu</a>
                            <a href="/ben-tre" class="dropdown-item" data-key="t-lightbox">Bến Tre</a>
                            <a href="/ca-mau" class="dropdown-item" data-key="t-lightbox">Cà Mau</a>
                            <a href="/can-tho" class="dropdown-item" data-key="t-lightbox">Cần Thơ</a>
                            <a href="/hau-giang" class="dropdown-item" data-key="t-lightbox">Hậu Giang</a>
                            <a href="/kien-giang" class="dropdown-item" data-key="t-lightbox">Kiên Giang</a>
                            <a href="/long-an" class="dropdown-item" data-key="t-lightbox">Long An</a>
                            <a href="/soc-trang" class="dropdown-item" data-key="t-lightbox">Sóc Trăng</a>
                            <a href="/tien-giang" class="dropdown-item" data-key="t-lightbox">Tiền Giang</a>
                            <a href="/tra-vinh" class="dropdown-item" data-key="t-lightbox">Trà Vinh</a>
                            <a href="/vinh-long" class="dropdown-item" data-key="t-lightbox">Vĩnh Long</a>
                            <a href="/dong-thap" class="dropdown-item" data-key="t-lightbox">Đồng Tháp</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items Dropdown -->
        @foreach($menuItems as $menu)
            <div class="dropdown">
                <a class="btn {{ $menu->children->count() > 0 ? 'dropdown-toggle' : '' }}"
                   id="dropdownNewsButton"
                   data-bs-toggle="{{ $menu->children->count() > 0 ? 'dropdown' : '' }}"
                   aria-expanded="false"
                >
                    @if($menu->icon)
                        {!! $menu->icon !!}
                    @endif

                    {{ $menu->title }}

                    @if($menu->children->count() > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down ms-1"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    @endif
                </a>

                @if($menu->children->count() > 0)
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownNewsButton">
                        @foreach($menu->children as $child)
                            <a href="{{ $child->url }}" class="dropdown-item">{{ $child->title }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</nav>

<!-- Mobile Search - Shown only on small screens -->
<div class="d-lg-none bg-light py-2">
    <div class="container-fluid">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" id="m-thoi-tiet-search-header" class="form-control border-start-0" placeholder="Nhập tên địa điểm">
            <div class="m-thoi-tiet-search-header-result position-absolute w-100 bg-white" style="top: 100%; left: 0; z-index: 1000;"></div>
        </div>
    </div>
</div>
