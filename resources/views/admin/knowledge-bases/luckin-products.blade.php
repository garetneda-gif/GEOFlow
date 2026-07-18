@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/luckin-product-catalog.css') }}">
@endpush

@section('content')
    <div class="luckin-product-library" data-product-library>
        <header class="luckin-product-library__header">
            <div class="luckin-product-library__title-row">
                <a href="{{ route('admin.knowledge-bases.index') }}" class="luckin-product-library__back" aria-label="返回知识库">
                    <i data-lucide="arrow-left" aria-hidden="true"></i>
                </a>
                <div>
                    <p class="luckin-product-library__eyebrow">OFFICIAL PRODUCT ARCHIVE</p>
                    <h1>瑞幸官网产品视觉库</h1>
                    <p class="luckin-product-library__lead">把官网产品图、分类与产品说明集中到 GEOFlow 知识库中，供选题、内容生产和知识核验时直接浏览。</p>
                </div>
            </div>
        </header>

        <section class="luckin-product-library__toolbar" aria-label="筛选产品">
            <div class="luckin-product-library__search">
                <i data-lucide="search" aria-hidden="true"></i>
                <input type="search" placeholder="搜索产品名称、标签或产品说明" autocomplete="off" data-product-search>
                <button type="button" data-clear-search hidden>清除</button>
            </div>
            <div class="luckin-product-library__filters" role="group" aria-label="按分类筛选">
                <button type="button" class="is-active" data-category="all" aria-pressed="true">
                    全部 <span>{{ $products->count() }}</span>
                </button>
                @foreach ($categories as $category)
                    <button type="button" data-category="{{ $category['id'] }}" aria-pressed="false">
                        {{ $category['name'] }} <span>{{ $category['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <div class="luckin-product-library__result-line" aria-live="polite">
            <span data-result-label>正在展示全部 {{ $products->count() }} 款产品</span>
            <span>图片与文字来自瑞幸咖啡官网，商品与营养信息以官网实时页面为准</span>
        </div>

        <section class="luckin-product-library__grid" data-product-grid aria-label="瑞幸官网产品">
            @foreach ($products as $product)
                @php
                    $searchText = implode(' ', array_merge(
                        [$product['name'], $product['category'], $product['description']],
                        $product['tags'],
                        $product['specs'],
                    ));
                @endphp
                <article
                    class="luckin-product-item"
                    data-product-item
                    data-category="{{ $product['category_id'] }}"
                    data-search="{{ $searchText }}"
                >
                    <a class="luckin-product-item__visual" href="{{ $product['official_url'] }}" target="_blank" rel="noopener noreferrer" tabindex="-1" aria-hidden="true">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <img
                            src="{{ asset(ltrim($product['image'], '/')) }}"
                            alt="{{ $product['name'] }}官网产品图"
                            width="480"
                            height="480"
                            loading="{{ $loop->iteration <= 8 ? 'eager' : 'lazy' }}"
                            decoding="async"
                        >
                    </a>
                    <div class="luckin-product-item__body">
                        <div class="luckin-product-item__heading">
                            <div>
                                <p>{{ $product['category'] }}</p>
                                <h2>{{ $product['name'] }}</h2>
                            </div>
                            <a href="{{ $product['official_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="在瑞幸官网查看{{ $product['name'] }}">
                                <i data-lucide="arrow-up-right" aria-hidden="true"></i>
                            </a>
                        </div>

                        @if (! empty($product['tags']) || ! empty($product['specs']))
                            <div class="luckin-product-item__facts">
                                @foreach ($product['tags'] as $tag)
                                    <span class="is-tag">{{ $tag }}</span>
                                @endforeach
                                @foreach ($product['specs'] as $spec)
                                    <span>{{ $spec }}</span>
                                @endforeach
                            </div>
                        @endif

                        <p class="luckin-product-item__description">{{ $product['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="luckin-product-library__empty" data-empty-state hidden>
            <p>没有找到匹配的产品</p>
            <button type="button" data-reset-filters>清除筛选</button>
        </div>

        <footer class="luckin-product-library__source">
            <div>
                <span>数据来源</span>
                <strong>瑞幸咖啡官网产品页</strong>
            </div>
            <p>本页为 {{ $snapshotDate }} 建立的只读视觉快照；不会替代官网实时商品、价格、库存或营养信息。</p>
            <a href="https://lkcoffee.com/products" target="_blank" rel="noopener noreferrer">打开官网原页 <i data-lucide="external-link" aria-hidden="true"></i></a>
        </footer>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/luckin-product-catalog.js') }}" defer></script>
@endpush
