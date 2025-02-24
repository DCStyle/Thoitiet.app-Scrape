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
                            <img width="36" height="36" class="img-fluid" src="/assets/images/facebook.svg" alt="facebook fanpage">
                        </a>
                    @endif
                    @if($settings['social_telegram'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_telegram'] }}" title="Telegram">
                            <img width="36" height="36" class="img-fluid" src="/assets/images/telegram.svg" alt="telegram">
                        </a>
                    @endif
                    @if($settings['social_zalo'])
                        <a rel="noopener nofollow" target="_blank" href="{{ $settings['social_zalo'] }}" title="Zalo">
                            <img width="36" height="36" class="img-fluid" src="/assets/images/zalo.svg" alt="zalo">
                        </a>
                    @endif
                </div>
                <span>{{ $settings['copyright'] }}</span>
            </div>
        </div>
    </div>
</footer>
