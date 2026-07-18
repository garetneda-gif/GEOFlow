@php
    $path = request()->path();
    $isHome = $path === '' || $path === '/';
@endphp
<header class="tt-header">
    <div class="tt-shell">
        <div class="tt-header-row">
            <a href="{{ route('site.home') }}" class="tt-brand" aria-label="瑞幸 AI 饮品指南">
                @if(!empty($siteLogo))
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-9 w-auto max-w-48 object-contain">
                @else
                    <img src="{{ asset('themes/toutiao-news-20260426/luckin-coffee-logo.png') }}" alt="luckin coffee 瑞幸咖啡" class="luckin-official-logo">
                @endif
            </a>

            <nav class="tt-topnav" aria-label="主导航">
                <a href="{{ route('site.home') }}" data-nav-item="home" class="{{ $isHome ? 'is-active' : '' }}">{{ __('front.nav.home') }}</a>
                @foreach($navCategories->take(5) as $categoryItem)
                    <a href="{{ route('site.category', $categoryItem->slug) }}">{{ $categoryItem->name }}</a>
                @endforeach
            </nav>

            <button type="button" class="tt-mobile-menu" onclick="document.getElementById('ttMobileNav')?.classList.toggle('hidden')" aria-label="{{ __('front.nav.categories') }}">
                <i data-lucide="menu" class="w-7 h-7"></i>
            </button>
        </div>
        <div id="ttMobileNav" class="hidden pb-4">
            <div class="tt-channel-rail !sticky !top-auto">
                <a href="{{ route('site.home') }}" data-nav-item="home" class="tt-channel {{ $isHome ? 'is-active' : '' }}">{{ __('front.nav.home') }}</a>
                @foreach($navCategories as $categoryItem)
                    <a href="{{ route('site.category', $categoryItem->slug) }}" class="tt-channel">{{ $categoryItem->name }}</a>
                @endforeach
            </div>
        </div>
    </div>
</header>
