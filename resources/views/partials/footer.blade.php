<footer class="footer">
    <div class="container">
        <div class="row pt-3 pb-0 pt-md-4 pb-md-4">
            @foreach($columns as $column)
                <div class="col-6 col-md-3 mb-4 mb-md-0">
                    @if(str_starts_with($column->url ?? '', 'http'))
                        <a class="fw-medium d-block text-decoration-none color-content mb-2 mb-md-3"
                           href="{{ $column->url }}"
                           title="{{ $column->title }}">{{ $column->title }}</a>
                    @else
                        <div class="fw-medium d-block text-decoration-none color-content mb-2 mb-md-3">{{ $column->title }}</div>
                    @endif
                    <ul class="list-unstyled mb-0 txt-sub-content">
                        @foreach($column->items as $item)
                            <li>
                                <a href="{{ $item->url }}"
                                   @if(str_starts_with($item->url, 'http'))
                                       target="_blank" rel="noopener"
                                   @endif
                                   title="{{ $item->label }}">{{ $item->label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="row border-top txt-sub-content pt-4 pt-md-5 pb-5">
            <div class="col-md-6 mb-4 mb-md-0">
                <span class="fw-medium">{{ $settings['site_name'] }}</span> - {{ $settings['site_description'] }}<br>
                <span class="fw-medium">Email:</span> <a href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a><br>
                <span class="fw-medium">Địa chỉ:</span> {{ $settings['address'] }}<br>
                <span class="fw-medium">Chịu trách nhiệm nội dung:</span> {{ $settings['responsible_person'] }}<br>
            </div>
            <div class="col-md-6 d-flex flex-column justify-content-end align-items-start align-items-md-end">
                <div class="d-flex gap-3 mb-4 mb-md-2 align-items-center">
                    @if($settings['social_facebook'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_facebook'] }}" title="Facebook">
                            <i class="fa-brands fa-facebook display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_telegram'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_telegram'] }}" title="Telegram">
                            <i class="fa-brands fa-telegram display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_youtube'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_youtube'] }}" title="Youtube">
                            <i class="fa-brands fa-youtube display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_twitter'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_twitter'] }}" title="Twitter">
                            <i class="fa-brands fa-twitter display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_instagram'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_instagram'] }}" title="Instagram">
                            <i class="fa-brands fa-instagram display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_linkedin'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_linkedin'] }}" title="Linkedin">
                            <i class="fa-brands fa-linkedin display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_pinterest'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_pinterest'] }}" title="Pinterest">
                            <i class="fa-brands fa-pinterest display-6"></i>
                        </a>
                    @endif
                    @if($settings['social_tiktok'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_tiktok'] }}" title="Tiktok">
                            <i class="fa-brands fa-tiktok display-6"></i>
                        </a>
                    @endif
                </div>

                <span class="d-block mb-4 mb-md-2 ">
                    {{ $settings['copyright'] }}
                </span>

                <div class="d-flex gap-3 mb-4 mb-md-2 align-items-center">
                    @if($settings['usage_policy'])
                        <a href="{{ $settings['usage_policy'] }}" title="Chính sách sử dụng">Chính sách sử dụng</a>
                    @endif

                    @if($settings['privacy_policy'])
                        //
                        <a href="{{ $settings['privacy_policy'] }}" title="Chính sách bảo mật">Chính sách bảo mật</a>
                    @endif

                    @if($settings['contact'])
                        //
                        <a href="{{ $settings['contact'] }}" title="Liên hệ">Liên hệ</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
