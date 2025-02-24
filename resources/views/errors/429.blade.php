@extends('errors.layout')

@section('title', 'Too Many Requests')

@section('content')
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card border-danger shadow-sm" style="max-width: 500px;">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Rate Limit Exceeded
                </h4>
            </div>
            <div class="card-body">
                <h5 class="card-title text-danger">Too Many Requests</h5>
                <p class="card-text">{{ $exception->getMessage() ?: 'Too many requests. Please try again later.' }}</p>
                <p class="card-text">
                    <small class="text-muted">
                        You can try again in {{ $exception->getHeaders()['Retry-After'] ?? 60 }} seconds.
                    </small>
                </p>
                <div class="progress" style="height: 2px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="/" class="btn btn-outline-danger">
                        <i class="bi bi-house-door-fill me-1"></i>Return Home
                    </a>
                    <button onclick="window.location.reload()" class="btn btn-danger" id="retryButton" disabled>
                        <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                        (<span id="countdown">60</span>s)
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const retryButton = document.getElementById('retryButton');
                const countdownElement = document.getElementById('countdown');
                let timeLeft = {{ $exception->getHeaders()['Retry-After'] ?? 60 }};

                const countdown = setInterval(() => {
                    timeLeft--;
                    countdownElement.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(countdown);
                        retryButton.disabled = false;
                        retryButton.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Try Again';
                    }
                }, 1000);
            });
        </script>
    @endpush
@endsection
