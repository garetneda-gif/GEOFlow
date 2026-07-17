<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('site.partials.seo-head')
    @stack('head')
    <script src="{{ asset('js/tailwindcss.play-cdn.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/toutiao-news-20260426/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/luckin-theme.css') }}">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    @if(!empty($headAnalyticsCode))
        {!! $headAnalyticsCode !!}
    @endif
    @php
        $schemaAtContext = chr(64).'context';
        $schemaAtType = chr(64).'type';
        $websiteSchema = [
            $schemaAtContext => 'https://schema.org',
            $schemaAtType => 'WebSite',
            'name' => '瑞幸 AI 饮品指南',
            'url' => route('site.home'),
            'potentialAction' => [
                $schemaAtType => 'SearchAction',
                'target' => route('site.home').'?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <x-json-ld :data="$websiteSchema" />
</head>
<body class="luckin-site tt-body">
    <div class="luckin-site-demo">概念验证 Demo · 内容为场景演示，不代表瑞幸正式公告或实时经营信息</div>
    @include('theme.toutiao-news-20260426.partials.header')
    <main class="tt-main">
        @yield('content')
    </main>
    @include('theme.toutiao-news-20260426.partials.footer')
    @stack('scripts')
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('themes/toutiao-news-20260426/theme.js') }}" defer></script>
</body>
</html>
