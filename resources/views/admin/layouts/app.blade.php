@php
    $adminBrandName = \App\Support\AdminWeb::siteName();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@isset($pageTitle){{ $pageTitle }} — @endisset瑞幸 GEO 智能内容运营中台</title>
    <script src="{{ asset('js/tailwindcss.play-cdn.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/luckin-theme.css') }}">
    @stack('styles')
</head>
<body class="luckin-admin bg-gray-50">
@include('admin.partials.header', [
    'adminBrandName' => $adminBrandName,
    'adminSiteName' => $adminSiteName ?? $adminBrandName,
    'pageTitle' => $pageTitle ?? '',
    'activeMenu' => $activeMenu ?? '',
])
    <main class="luckin-main">
        <div class="luckin-page-shell">
        @if (session('message'))
            <div class="admin-flash-alert luckin-toast luckin-toast-success mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="status">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="admin-flash-alert luckin-toast luckin-toast-error mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <div class="luckin-toast-content space-y-1">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            </div>
        @endif
        @yield('content')
        </div>
    </main>
@include('admin.partials.footer')
@include('admin.partials.welcome-modal')
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>
