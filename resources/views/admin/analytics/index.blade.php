@extends('admin.layouts.app')

@php
    $growthStats = $growthOverview['stats'] ?? [];
    $recentSubmissions = $growthOverview['recent_submissions'] ?? collect();
    $sourceSummary = $growthOverview['source_summary'] ?? [];
    $growthStages = [
        [
            'icon' => 'eye',
            'title' => __('admin.growth_center.stage.visit_title'),
            'desc' => __('admin.growth_center.stage.visit_desc'),
            'count' => (int) ($growthStats['today_visits'] ?? 0),
            'href' => '#growth-observation-details',
            'iconClass' => 'bg-blue-50 text-blue-600 ring-blue-100',
            'countClass' => 'text-blue-700',
            'linkClass' => 'text-blue-700 group-hover:text-blue-800',
        ],
        [
            'icon' => 'mouse-pointer-click',
            'title' => __('admin.growth_center.stage.touch_title'),
            'desc' => __('admin.growth_center.stage.touch_desc'),
            'count' => (int) ($growthStats['active_forms'] ?? 0),
            'href' => route('admin.lead-forms.index'),
            'iconClass' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            'countClass' => 'text-emerald-700',
            'linkClass' => 'text-emerald-700 group-hover:text-emerald-800',
        ],
        [
            'icon' => 'inbox',
            'title' => __('admin.growth_center.stage.lead_title'),
            'desc' => __('admin.growth_center.stage.lead_desc'),
            'count' => (int) ($growthStats['submissions_total'] ?? 0),
            'href' => route('admin.leads.index'),
            'iconClass' => 'bg-amber-50 text-amber-600 ring-amber-100',
            'countClass' => 'text-amber-700',
            'linkClass' => 'text-amber-700 group-hover:text-amber-800',
        ],
        [
            'icon' => 'user-check',
            'title' => __('admin.growth_center.stage.follow_title'),
            'desc' => __('admin.growth_center.stage.follow_desc'),
            'count' => (int) ($growthStats['handled_leads'] ?? 0),
            'href' => route('admin.leads.index', ['status' => 'contacted']),
            'iconClass' => 'bg-purple-50 text-purple-600 ring-purple-100',
            'countClass' => 'text-purple-700',
            'linkClass' => 'text-purple-700 group-hover:text-purple-800',
        ],
    ];
    $growthMetrics = [
        ['icon' => 'calendar-days', 'label' => __('admin.growth_center.metric.today_visits'), 'value' => (int) ($growthStats['today_visits'] ?? 0), 'class' => 'text-blue-600'],
        ['icon' => 'bot', 'label' => __('admin.growth_center.metric.ai_visits'), 'value' => (int) ($growthStats['today_ai_visits'] ?? 0), 'class' => 'text-indigo-600'],
        ['icon' => 'clipboard-list', 'label' => __('admin.growth_center.metric.submissions'), 'value' => (int) ($growthStats['submissions_total'] ?? 0), 'class' => 'text-emerald-600'],
        ['icon' => 'sparkles', 'label' => __('admin.growth_center.metric.new_leads'), 'value' => (int) ($growthStats['new_leads'] ?? 0), 'class' => 'text-amber-600'],
        ['icon' => 'phone-call', 'label' => __('admin.growth_center.metric.pending_followups'), 'value' => (int) ($growthStats['pending_followups'] ?? 0), 'class' => 'text-rose-600'],
    ];

    if ((int) ($growthStats['new_leads'] ?? 0) > 0) {
        $priority = [
            'icon' => 'inbox',
            'title' => __('admin.growth_center.priority.new_leads_title', ['count' => (int) $growthStats['new_leads']]),
            'desc' => __('admin.growth_center.priority.new_leads_desc'),
            'href' => route('admin.leads.index', ['status' => 'new']),
            'button' => __('admin.growth_center.priority.new_leads_button'),
            'iconClass' => 'bg-amber-50 text-amber-600 ring-amber-100',
        ];
    } elseif ((int) ($growthStats['active_forms'] ?? 0) === 0) {
        $priority = [
            'icon' => 'clipboard-plus',
            'title' => __('admin.growth_center.priority.no_form_title'),
            'desc' => __('admin.growth_center.priority.no_form_desc'),
            'href' => route('admin.lead-forms.create'),
            'button' => __('admin.growth_center.priority.no_form_button'),
            'iconClass' => 'bg-blue-50 text-blue-600 ring-blue-100',
        ];
    } else {
        $priority = [
            'icon' => 'line-chart',
            'title' => __('admin.growth_center.priority.observation_title'),
            'desc' => __('admin.growth_center.priority.observation_desc'),
            'href' => '#growth-observation-details',
            'button' => __('admin.growth_center.priority.observation_button'),
            'iconClass' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        ];
    }
@endphp

@section('content')
    <div class="px-4 sm:px-0">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.analytics.heading') }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('admin.analytics.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.lead-forms.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
                    {{ __('admin.growth_center.action.create_form') }}
                </a>
                <a href="{{ route('admin.lead-forms.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i data-lucide="clipboard-list" class="mr-2 h-4 w-4"></i>
                    {{ __('admin.growth_center.action.manage_forms') }}
                </a>
                <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i data-lucide="inbox" class="mr-2 h-4 w-4"></i>
                    {{ __('admin.growth_center.action.lead_inbox') }}
                </a>
                <button type="button" onclick="location.reload()" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50">
                    <i data-lucide="refresh-cw" class="mr-1 h-4 w-4"></i>
                    {{ __('admin.analytics.refresh') }}
                </button>
            </div>
        </div>

        @php
            $luckinDemoMetrics = [
                ['label' => 'GEO内容总量', 'value' => '386', 'icon' => 'files'],
                ['label' => 'AI爬虫访问量', 'value' => '12,480', 'icon' => 'bot'],
                ['label' => '内容引用命中率', 'value' => '78.4%', 'icon' => 'scan-search'],
                ['label' => '高频需求覆盖率', 'value' => '82.1%', 'icon' => 'badge-check'],
                ['label' => '待补知识数量', 'value' => '19', 'icon' => 'book-dashed'],
                ['label' => '内容到菜单访问率', 'value' => '14.6%', 'icon' => 'mouse-pointer-click'],
            ];
        @endphp
        <section class="luckin-card mb-8 p-5 sm:p-6" aria-labelledby="luckin-agent-demo-title">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 id="luckin-agent-demo-title" class="text-xl font-semibold text-gray-900">Agent运营洞察演示</h2>
                        <span class="luckin-demo-badge">模拟数据</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">以下指标用于展示未来接入真实 AI 搜索、Agent 请求和菜单访问数据后的分析形态。</p>
                </div>
                <a href="{{ route('admin.materials.index') }}" class="luckin-button luckin-button-secondary">查看知识缺口</a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                @foreach($luckinDemoMetrics as $metric)
                    <div class="luckin-kpi">
                        <i data-lucide="{{ $metric['icon'] }}" class="h-5 w-5 text-blue-700"></i>
                        <span class="mt-3 text-xs text-gray-500">{{ $metric['label'] }}</span>
                        <strong class="mt-1 text-2xl text-gray-900">{{ $metric['value'] }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="mt-5 grid gap-4 xl:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">高频用户问题排行</h3>
                    <ol class="mt-3 space-y-3 text-sm text-gray-600">
                        <li class="flex justify-between gap-3"><span>1. 晚间低甜与非咖选择</span><strong>865</strong></li>
                        <li class="flex justify-between gap-3"><span>2. 多人预算组合点单</span><strong>742</strong></li>
                        <li class="flex justify-between gap-3"><span>3. 优惠券适用范围核验</span><strong>618</strong></li>
                    </ol>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">各场景知识覆盖情况</h3>
                    <div class="mt-3 space-y-3 text-sm">
                        @foreach([['晚间非咖', 86], ['多人点单', 72], ['门店与履约', 64]] as [$label, $value])
                            <div><div class="mb-1 flex justify-between"><span>{{ $label }}</span><strong>{{ $value }}%</strong></div><div class="h-2 rounded-full bg-blue-50"><div class="h-2 rounded-full bg-blue-700" style="width: {{ $value }}%"></div></div></div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">内容审核风险分布</h3>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-red-50 px-3 py-1.5 text-red-700">商品事实待核验 7</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-700">实时价格 4</span>
                        <span class="rounded-full bg-blue-50 px-3 py-1.5 text-blue-700">缺少来源 5</span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">可发布 23</span>
                    </div>
                    <p class="mt-4 text-xs leading-5 text-gray-500">演示标签不修改现有审核规则；实际发布状态仍由原有审核流程决定。</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 xl:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">AI爬虫访问趋势</h3>
                        <span class="text-xs text-gray-500">近7日</span>
                    </div>
                    <div class="mt-5 flex h-28 items-end gap-2" aria-label="AI爬虫访问趋势模拟图">
                        @foreach([38, 52, 47, 68, 74, 64, 86] as $value)
                            <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                                <div class="w-full rounded-t bg-blue-700/80" style="height: {{ $value }}%" title="{{ $value }}"></div>
                                <span class="text-[10px] text-gray-400">{{ $loop->iteration }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">内容发布渠道表现</h3>
                    <div class="mt-4 space-y-4 text-sm">
                        @foreach([['自有内容站', 86], ['通用 HTTP 渠道', 71], ['WordPress 渠道', 58]] as [$label, $value])
                            <div>
                                <div class="mb-1.5 flex justify-between gap-3"><span class="text-gray-600">{{ $label }}</span><strong class="text-gray-900">{{ $value }}%</strong></div>
                                <div class="h-2 rounded-full bg-blue-50"><div class="h-2 rounded-full bg-blue-700" style="width: {{ $value }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">未解决需求排行</h3>
                    <ol class="mt-3 space-y-3 text-sm text-gray-600">
                        <li class="flex items-start justify-between gap-3"><span>1. 晚间场景的甜度标签不完整</span><strong class="text-amber-700">126</strong></li>
                        <li class="flex items-start justify-between gap-3"><span>2. 多人点单缺少预算追问规则</span><strong class="text-amber-700">93</strong></li>
                        <li class="flex items-start justify-between gap-3"><span>3. 门店可售状态需接入实时查询</span><strong class="text-red-700">71</strong></li>
                    </ol>
                </div>
            </div>
        </section>

        <section class="mb-8">
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">{{ __('admin.growth_center.workbench.eyebrow') }}</p>
                    <h2 class="mt-1 text-xl font-semibold text-gray-900">{{ __('admin.growth_center.workbench.title') }}</h2>
                    <p class="mt-1 max-w-4xl text-sm leading-6 text-gray-600">{{ __('admin.growth_center.workbench.desc') }}</p>
                </div>
                <a href="{{ route('admin.leads.export') }}" class="inline-flex w-fit items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                    <i data-lucide="download" class="mr-2 h-4 w-4"></i>
                    {{ __('admin.growth_center.action.export_leads') }}
                </a>
            </div>

            <div class="mb-4 rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md ring-1 {{ $priority['iconClass'] }}">
                            <i data-lucide="{{ $priority['icon'] }}" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">{{ __('admin.growth_center.priority.label') }}</p>
                            <h3 class="mt-1 text-base font-semibold text-gray-900">{{ $priority['title'] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500">{{ $priority['desc'] }}</p>
                        </div>
                    </div>
                    <a href="{{ $priority['href'] }}" class="inline-flex h-9 w-fit shrink-0 items-center rounded-md bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ $priority['button'] }}
                        <i data-lucide="arrow-right" class="ml-1.5 h-4 w-4"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($growthStages as $stage)
                    <a href="{{ $stage['href'] }}" class="group flex min-h-44 flex-col justify-between rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-md ring-1 {{ $stage['iconClass'] }}">
                                    <i data-lucide="{{ $stage['icon'] }}" class="h-5 w-5"></i>
                                </div>
                                <div class="text-right text-2xl font-semibold {{ $stage['countClass'] }}">{{ $stage['count'] }}</div>
                            </div>
                            <h3 class="mt-5 text-base font-semibold text-gray-900">{{ $stage['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-500">{{ $stage['desc'] }}</p>
                        </div>
                        <div class="mt-5 inline-flex items-center text-sm font-semibold {{ $stage['linkClass'] }}">
                            {{ __('admin.growth_center.workbench.open') }}
                            <i data-lucide="arrow-right" class="ml-1.5 h-4 w-4 transition group-hover:translate-x-0.5"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-5">
            @foreach ($growthMetrics as $metric)
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center">
                        <i data-lucide="{{ $metric['icon'] }}" class="h-6 w-6 {{ $metric['class'] }}"></i>
                        <div class="ml-5">
                            <div class="text-sm text-gray-500">{{ $metric['label'] }}</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ $metric['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('admin.growth_center.inbox.title') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ __('admin.growth_center.inbox.desc') }}</p>
                    </div>
                    <a href="{{ route('admin.leads.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">{{ __('admin.growth_center.inbox.view_all') }}</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recentSubmissions as $submission)
                        <a href="{{ route('admin.leads.show', ['submissionId' => $submission->id]) }}" class="block px-5 py-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">{{ $submission->form?->name ?? __('admin.leads.deleted_form') }}</div>
                                    <div class="mt-1 truncate text-xs text-gray-500">{{ $submission->source_url ?: __('admin.growth_center.direct_source') }}</div>
                                </div>
                                <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ __('admin.leads.status.'.$submission->status) }}</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-400">{{ $submission->created_at?->format('Y-m-d H:i') }}</div>
                        </a>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">{{ __('admin.growth_center.inbox.empty') }}</div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('admin.growth_center.source.title') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('admin.growth_center.source.desc') }}</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($sourceSummary as $source)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0 truncate text-sm font-medium text-gray-900">{{ $source['source'] }}</div>
                                <div class="shrink-0 text-sm font-semibold text-gray-900">{{ $source['count'] }}</div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span>{{ __('admin.growth_center.source.converted', ['count' => (int) ($source['converted'] ?? 0)]) }}</span>
                                <span>{{ __('admin.growth_center.source.submissions') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">{{ __('admin.growth_center.source.empty') }}</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section id="growth-observation-details" class="mb-6 border-t border-gray-200 pt-8">
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('admin.growth_center.observation.eyebrow') }}</p>
                <h2 class="mt-1 text-xl font-semibold text-gray-900">{{ __('admin.growth_center.observation.title') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('admin.growth_center.observation.desc') }}</p>
            </div>
        </section>

        @include('admin.analytics._filters', ['filters' => $filters, 'filterOptions' => $filterOptions])
        @include('admin.analytics._global-overview', ['globalOverview' => $globalOverview])
        @include('admin.analytics._single-site-section')
        @if ($canManageProtectedWorkflows)
            @include('admin.analytics._distribution-section')
        @endif
        @include('admin.analytics._log-section', ['logSummary' => $logSummary])
    </div>
@endsection
