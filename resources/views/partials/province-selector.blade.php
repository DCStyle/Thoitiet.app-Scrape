<div class="province-selector-component mb-4">
    <div class="card">
        <div class="card-header bg-light">
            <h3 class="card-title mb-0">Chọn tỉnh/thành phố</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($provinces as $p)
                    <div class="col-md-2 col-sm-4 col-6 mb-3">
                        <a href="{{ url($p->code_name) }}"
                           class="btn {{ isset($province) && $p->code == $province->code ? 'btn-primary' : 'btn-outline-primary' }} w-100 text-truncate">
                            {{ $p->name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
