@props(['articles'])

<section class="news-popular">
    <div class="container mb-4">
        <div class="title-main">
            <h3>Tin tức nổi bật</h3>
        </div>

        <div class="row news-popular-inner">
            @foreach($articles as $article)
                <div class="col-xl-3 col-md-3 mb-2 card-post">
                    <a rel="nofollow" href="{{ route('articles.show', $article) }}" class="thumb">
                        <img class="mb-2 w-100 rounded"
                             src="{{ $article->getThumbnail() }}"
                             alt="{{ $article->title }}"
                        >
                    </a>
                    <a rel="nofollow" href="{{ route('articles.show', $article) }}">
                        <h4 class="card-title me-2">
                            {{ $article->title }}
                        </h4>
                    </a>
                </div>
            @endforeach
        </div>

        <a href="{{ route('articles.index') }}">
            <button class="btn btn-primary btn-rounded waves-effect waves-light mb-2 me-2">
                Xem thêm
            </button>
        </a>
    </div>
</section>
