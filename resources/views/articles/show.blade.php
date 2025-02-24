@extends('layouts.app')

@section('seo')
    {!! seo($article->getDynamicSEOData()) !!}
@endsection

@section('content')
    <div class="page-content">
        <div class="container">
            <div class="row">
                <!-- Main content section -->
                <div class="col-xl-8">
                    <div class="card">
                        <article id="post-{{ $article->id }}" class="post">
                            <a href="{{ route('articles.show', $article) }}" title="{{ $article->title }}">
                                <img width="100%"
                                     src="{{ $article->getThumbnail() }}"
                                     class="post-img" alt="{{ $article->title }}"
                                >
                            </a>

                            <div class="post-content card-body">
                                <h1 class="entry-title">
                                    <a href="{{ route('articles.show', $article) }}" rel="bookmark" itemprop="url">
                                        {{ $article->title }}
                                    </a>
                                </h1>

                                <div class="entry-meta">
                                    <span class="entry-date published">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ $article->created_at->format('M d, Y') }}
                                    </span>

                                    <span class="entry-time published">
                                        <i class="far fa-clock"></i>
                                        {{ $article->readTime() }}
                                    </span>

                                    <span class="entry-author">
                                        <i class="far fa-user"></i>
                                        {{ $article->user->name }}
                                    </span>
                                </div>

                                <div class="entry-content">
                                    {!! $article->content !!}
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Sidebar section -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body space-y-4">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
