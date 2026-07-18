@php
    $currentStep = trim($__env->yieldContent('step', '0'));
@endphp
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#17277c">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '用户共创系统') · luckin coffee</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/luckin-cocreate.css') }}?v=20260718-2233">
    <script src="{{ asset('js/lucide.min.js') }}" defer></script>
    <script src="{{ asset('js/luckin-cocreate.js') }}" defer></script>
</head>
<body class="cocreate-app @yield('body-class')">
    <header class="cocreate-header">
        <a class="cocreate-brand" href="{{ route('co-create.entry') }}" aria-label="返回用户共创系统首页">
            <img src="{{ asset('images/luckin-coffee-logo.png') }}" alt="luckin coffee">
            <span>用户共创系统</span>
        </a>
        <span class="demo-label">DEMO</span>
    </header>

    @if((int) $currentStep > 0)
        <div class="cocreate-progress" aria-label="共创进度：第 {{ $currentStep }} 步，共 4 步">
            @for($step = 1; $step <= 4; $step++)
                <span class="{{ $step <= (int) $currentStep ? 'is-active' : '' }}"></span>
            @endfor
        </div>
    @endif

    @yield('content')

    <footer class="cocreate-legal">
        概念验证 Demo · 页面内容与奖励均为场景演示
    </footer>
</body>
</html>
