@extends('layouts.app')

@section('seo')
    {!! seo($category->getDynamicSEOData()) !!}
@endsection

@section('content')
    <div class="page-content">
        <!-- Page title section -->
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h1 class="mb-sm-0 font-size-16">{{ $category->name }}</h1>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                                <li class="breadcrumb-item active">
                                    <a href="{{ route('article-categories.show', $category) }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page content section -->
        <div class="container">
            <div class="row">
                <!-- Article list section -->
                <div class="col-xl-8">
                    <div class="card">
                        @foreach($articles as $article)
                            <article id="post-{{ $article->id }}" class="row mb-4 post-tt">
                                <div class="col-xl-5 img-tt">
                                    <a href="{{ route('articles.show', $article) }}" title="{{ $article->title }}">
                                        <img width="100%" src="{{ $article->getThumbnail() }}">
                                    </a>
                                </div>
                                <div class="col-xl-7 card-body info-tt">
                                    <h2 class="entry-title card-title">
                                        <a href="{{ route('articles.show', $article) }}"
                                           rel="bookmark"
                                           itemprop="url"
                                        >
                                            {{ $article->title }}
                                        </a>
                                    </h2>
                                    <div class="entry-meta"></div>
                                    <div class="entry-text">
                                        <p>{{ $article->exceprt() }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        <x-pagination :paginator="$articles" />
                    </div>
                </div>

                <!-- Sidebar section -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body space-y-4">
                            <div class="new-cate">
                                <h3 class="new-cate-title">Danh mục bài viết</h3>
                                <ul class="list-unstyled new-cate-list">
                                    @foreach($categories as $category)
                                        <li>
                                            <h4 class="new-cate-item">
                                                <a href="{{ route('article-categories.show', $category) }}">
                                                    {{ $category->name }}
                                                </a>
                                            </h4>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            @if($latestArticles->count() > 0)
                                <div class="new-cate">
                                    <h3 class="new-cate-title">Bài viết mới nhất</h3>
                                    <ul class="list-unstyled new-cate-list">
                                        @foreach($latestArticles as $article)
                                            <li>
                                                @if($loop->first)
                                                    <div class="new-cate-main">
                                                        <a href="{{ route('articles.show', $article) }}"
                                                           class="block thumb max-w-full"
                                                        >
                                                            <img src="{{ $article->getThumbnail() }}"
                                                                 alt="{{ $article->title }}"
                                                                 width="100%"
                                                                 class="object-fit-cover"
                                                            >
                                                        </a>
                                                    </div>
                                                @endif

                                                <h4 class="new-cate-item">
                                                    <a href="{{ route('articles.show', $article) }}">
                                                        {{ $article->title }}
                                                    </a>
                                                </h4>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
