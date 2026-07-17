@extends('admin.layouts.app')

@section('content')
    @php
        $canManageProtectedWorkflows = (bool) ($canManageProtectedWorkflows ?? false);
        $stats = is_array($dashboardStats ?? null) ? $dashboardStats : [];
        $todayStats = is_array($dashboardTodayStats ?? null) ? $dashboardTodayStats : [];
        $taskHealthData = is_array($taskHealth ?? null) ? $taskHealth : [];
        $recentTasks = is_iterable($recentTasks ?? null) ? $recentTasks : [];

        $activeTasks = (int) ($taskHealthData['active_tasks'] ?? $stats['active_tasks'] ?? 0);
        $pausedTasks = (int) ($taskHealthData['paused_tasks'] ?? 0);
        $runningJobs = (int) ($taskHealthData['running_jobs'] ?? $stats['running_jobs'] ?? 0);
        $pendingJobs = (int) ($taskHealthData['pending_jobs'] ?? $stats['pending_jobs'] ?? 0);
        $failedJobs = (int) ($taskHealthData['failed_jobs'] ?? $stats['failed_jobs'] ?? 0);

        $demoMetrics = [
            ['label' => '已核验知识', 'value' => '126', 'detail' => '可被 Agent 检索的事实单元', 'icon' => 'badge-check'],
            ['label' => '场景知识卡', 'value' => '48', 'detail' => '覆盖具体用户需求的知识卡', 'icon' => 'messages-square'],
            ['label' => '待审核内容', 'value' => '12', 'detail' => '等待人工确认事实与风险', 'icon' => 'scan-search'],
            ['label' => '已发布内容', 'value' => '386', 'detail' => '进入可观测触点的内容资产', 'icon' => 'file-text'],
            ['label' => 'AI引用命中率', 'value' => '78.4%', 'detail' => '概念验证口径', 'icon' => 'chart-no-axes-combined'],
            ['label' => '本月Agent需求', 'value' => '2,846', 'detail' => '跨场景模拟请求累计', 'icon' => 'bot'],
        ];

        $agentDemands = [
            [
                'question' => '晚上不想喝咖啡，有哪些低甜选择？',
                'volume' => '865',
                'coverage' => '部分覆盖',
                'updated_at' => '2026-07-17',
                'action' => '补充场景知识',
                'href' => route('admin.knowledge-bases.index'),
            ],
            [
                'question' => '办公室四个人预算80元，怎么组合？',
                'volume' => '742',
                'coverage' => '待补充',
                'updated_at' => '2026-07-16',
                'action' => '创建GEO任务',
                'href' => route('admin.tasks.create'),
            ],
            [
                'question' => '我有哪些快过期优惠券？',
                'volume' => '618',
                'coverage' => '需实时查询',
                'updated_at' => '2026-07-17',
                'action' => '维护查询原则',
                'href' => route('admin.knowledge-bases.index'),
            ],
            [
                'question' => '一个喝咖啡、一个不喝，怎样一起点？',
                'volume' => '621',
                'coverage' => '已覆盖',
                'updated_at' => '2026-07-15',
                'action' => '查看内容资产',
                'href' => route('admin.articles.index'),
            ],
        ];

        $knowledgeGaps = [
            [
                'title' => '7个商品缺少甜度标签',
                'desc' => '补充可核验的偏好标签，不填写未经确认的配方或含量。',
                'status' => '待补齐',
                'href' => route('admin.knowledge-bases.index'),
                'action' => '打开知识库',
                'icon' => 'book-open-check',
            ],
            [
                'title' => '4个场景缺少晚间适配说明',
                'desc' => '补上时段与需求识别，让 Agent 在回答前知道该追问什么。',
                'status' => '试点中',
                'href' => route('admin.tasks.create'),
                'action' => '编排任务',
                'icon' => 'message-circle-question',
            ],
            [
                'title' => '3条优惠规则即将过期',
                'desc' => '只维护核验原则与有效期，实际优惠以交易系统实时结果为准。',
                'status' => '待复核',
                'href' => route('admin.articles.index'),
                'action' => '查看内容',
                'icon' => 'file-search-2',
            ],
        ];

        $knowledgeGaps[] = [
            'title' => '5篇内容等待人工审核',
            'desc' => '核验商品事实、实时信息、来源与风险表达后再进入发布链路。',
            'status' => '待审核',
            'href' => route('admin.articles.index', ['review_status' => 'pending']),
            'action' => '进入审核中心',
            'icon' => 'shield-check',
        ];

        $geoFlowSteps = [
            ['number' => '01', 'title' => '可信知识', 'desc' => '沉淀可核验品牌事实', 'icon' => 'database', 'href' => route('admin.knowledge-bases.index')],
            ['number' => '02', 'title' => '内容任务', 'desc' => '围绕具体需求编排', 'icon' => 'workflow', 'href' => route('admin.tasks.create')],
            ['number' => '03', 'title' => 'AI生成', 'desc' => '生成可追溯表达', 'icon' => 'sparkles', 'href' => route('admin.tasks.index')],
            ['number' => '04', 'title' => '人工审核', 'desc' => '核验事实、来源与风险', 'icon' => 'shield-check', 'href' => route('admin.articles.index', ['review_status' => 'pending'])],
            ['number' => '05', 'title' => '多端发布', 'desc' => '同步到已授权触点', 'icon' => 'radio-tower', 'href' => $canManageProtectedWorkflows ? route('admin.distribution.index') : null],
            ['number' => '06', 'title' => 'AI可见度', 'desc' => '观测爬虫和引用线索', 'icon' => 'bot', 'href' => route('admin.analytics')],
            ['number' => '07', 'title' => '转化反馈', 'desc' => '用结果反哺下一轮', 'icon' => 'chart-no-axes-combined', 'href' => route('admin.analytics')],
        ];

        $liveHealth = [
            ['label' => '活跃任务', 'value' => $activeTasks, 'icon' => 'play-circle'],
            ['label' => '运行中', 'value' => $runningJobs, 'icon' => 'loader-circle'],
            ['label' => '排队中', 'value' => $pendingJobs, 'icon' => 'clock-3'],
            ['label' => '失败运行', 'value' => $failedJobs, 'icon' => 'triangle-alert'],
        ];

        $materials = is_array($materialHealth ?? null) ? $materialHealth : [];
        $ai = is_array($aiHealth ?? null) ? $aiHealth : [];
        $distribution = is_array($distributionHealth ?? null) ? $distributionHealth : [];
        $materialLibraryCount = (int) ($materials['keyword_libraries'] ?? 0)
            + (int) ($materials['title_libraries'] ?? 0)
            + (int) ($materials['knowledge_bases'] ?? 0)
            + (int) ($materials['image_libraries'] ?? 0)
            + (int) ($materials['authors'] ?? 0);
        $knowledgeChunks = (int) ($materials['knowledge_chunks'] ?? 0);
        $vectorizedChunks = (int) ($materials['vectorized_chunks'] ?? 0);
        $unvectorizedChunks = (int) ($materials['unvectorized_chunks'] ?? 0);
        $aiUsedToday = (int) ($ai['used_today'] ?? 0);
        $publishedArticles = (int) ($stats['published_articles'] ?? 0);
        $draftArticles = (int) ($stats['draft_articles'] ?? 0);
        $pendingReview = (int) ($stats['pending_review'] ?? 0);
        $distributionFailed = (int) ($distribution['failed'] ?? 0);
        $runningBadgeCount = (int) (($runningJobs + $pendingJobs) > 0)
            + (int) ((int) ($distribution['sending'] ?? 0) > 0)
            + (int) ((int) ($todayStats['today_articles'] ?? 0) > 0);
        $attentionBadgeCount = (int) ($failedJobs > 0)
            + (int) ($unvectorizedChunks > 0)
            + (int) ($pendingReview > 0)
            + (int) ($distributionFailed > 0);

        $automationRecommendations = [];
        if ($failedJobs > 0) {
            $automationRecommendations[] = [
                'title' => __('admin.dashboard.todo_failed_jobs'),
                'desc' => __('admin.dashboard.automation.health_task_meta', ['running' => $runningJobs, 'queued' => $pendingJobs, 'failed' => $failedJobs]),
                'count' => $failedJobs,
                'href' => route('admin.tasks.health'),
                'tone' => 'border-red-200 bg-red-50 text-red-800',
            ];
        }
        if ($unvectorizedChunks > 0) {
            $automationRecommendations[] = [
                'title' => __('admin.dashboard.automation.rec_chunks_title'),
                'desc' => __('admin.dashboard.automation.rec_chunks_desc'),
                'count' => $unvectorizedChunks,
                'href' => route('admin.knowledge-bases.index'),
                'tone' => 'border-amber-200 bg-amber-50 text-amber-800',
            ];
        }
        if ($pendingReview > 0) {
            $automationRecommendations[] = [
                'title' => __('admin.dashboard.automation.rec_review_title'),
                'desc' => __('admin.dashboard.automation.rec_review_desc'),
                'count' => $pendingReview,
                'href' => route('admin.articles.index', ['review_status' => 'pending']),
                'tone' => 'border-blue-200 bg-blue-50 text-blue-800',
            ];
        }
        if ($canManageProtectedWorkflows && $distributionFailed > 0) {
            $automationRecommendations[] = [
                'title' => __('admin.dashboard.automation.rec_distribution_title'),
                'desc' => __('admin.dashboard.automation.rec_distribution_desc'),
                'count' => $distributionFailed,
                'href' => route('admin.distribution.jobs'),
                'tone' => 'border-red-200 bg-red-50 text-red-800',
            ];
        }

        $foundationFlow = [
            ['title' => __('admin.dashboard.automation.node_prompt_graph_title'), 'desc' => __('admin.dashboard.automation.node_prompt_graph_desc'), 'href' => route('admin.ai-prompts')],
            ['title' => __('admin.dashboard.automation.node_knowledge_assets_title'), 'desc' => __('admin.dashboard.automation.node_knowledge_assets_desc'), 'href' => route('admin.knowledge-bases.index')],
            ['title' => __('admin.dashboard.automation.node_evidence_structure_title'), 'desc' => __('admin.dashboard.automation.node_evidence_structure_desc'), 'href' => route('admin.materials.index')],
            ['title' => __('admin.dashboard.automation.node_engineering_task_title'), 'desc' => __('admin.dashboard.automation.node_engineering_task_desc'), 'href' => route('admin.tasks.create')],
            ['title' => __('admin.dashboard.automation.node_content_title'), 'desc' => __('admin.dashboard.automation.node_content_desc'), 'href' => route('admin.articles.index')],
            ['title' => __('admin.dashboard.automation.node_quality_gate_title'), 'desc' => __('admin.dashboard.automation.node_quality_gate_desc'), 'href' => route('admin.articles.index', ['review_status' => 'pending'])],
            ['title' => __('admin.dashboard.automation.node_authority_distribution_title'), 'desc' => __('admin.dashboard.automation.node_authority_distribution_desc'), 'href' => $canManageProtectedWorkflows ? route('admin.distribution.index') : null],
            ['title' => __('admin.dashboard.automation.node_measurement_title'), 'desc' => __('admin.dashboard.automation.node_measurement_desc'), 'href' => route('admin.analytics')],
        ];
    @endphp

    <div class="luckin-dashboard space-y-8">
        <section class="luckin-hero relative overflow-hidden rounded-3xl bg-slate-950 px-6 py-8 text-white shadow-xl sm:px-10 sm:py-12">
            <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-amber-400/10 blur-3xl"></div>
            <div class="relative grid gap-10 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-end">
                <div class="max-w-4xl">
                    <p class="luckin-kicker text-xs font-semibold uppercase tracking-[0.24em] text-blue-300">LUCKIN COFFEE · GEO 运营工作台</p>
                    <h1 class="mt-4 text-3xl font-bold leading-tight tracking-tight sm:text-5xl">让瑞幸在用户不打开 App 时，仍能被 AI 准确理解与调用</h1>
                    <p class="mt-5 max-w-3xl text-base leading-7 text-slate-300 sm:text-lg">通过可信知识、内容生产、人工审核和多端发布，持续经营瑞幸在AI搜索与Agent决策中的可见度。</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('admin.tasks.create') }}" class="luckin-primary-btn inline-flex h-11 items-center rounded-xl bg-blue-500 px-5 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-400">
                            <i data-lucide="plus" class="mr-2 h-4 w-4"></i>
                            创建 GEO 任务
                        </a>
                        <a href="{{ route('admin.knowledge-bases.index') }}" class="luckin-secondary-btn inline-flex h-11 items-center rounded-xl border border-white/20 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/15">
                            <i data-lucide="database" class="mr-2 h-4 w-4"></i>
                            导入品牌知识
                        </a>
                    </div>
                </div>
                <div class="luckin-hero-note rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-300">今日工作焦点</span>
                        <span class="luckin-status-dot inline-flex h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_0_5px_rgba(110,231,183,0.14)]"></span>
                    </div>
                    <p class="mt-4 text-xl font-semibold leading-8">让“用户怎么问”与“品牌怎么答”在同一张图上对齐。</p>
                    <p class="mt-3 text-sm leading-6 text-slate-300">演示数据与实时健康数据分开展示，便于团队区分概念验证和实际运行状态。</p>
                </div>
            </div>
        </section>

        <section class="luckin-section rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="luckin-section-kicker text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">GEO 运营信号</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">运营信号总览</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">用于讲解工作台的六项概念指标，帮助团队快速理解从事实到 Agent 的工作量。</p>
                </div>
                <span class="luckin-demo-badge inline-flex w-fit items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                    <i data-lucide="flask-conical" class="mr-1.5 h-3.5 w-3.5"></i>
                    演示数据 · 仅用于概念验证
                </span>
            </div>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ($demoMetrics as $metric)
                    <article class="luckin-stat-card rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <span class="luckin-stat-icon flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200">
                                <i data-lucide="{{ $metric['icon'] }}" class="h-4 w-4"></i>
                            </span>
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-amber-600">演示数据</span>
                        </div>
                        <p class="mt-5 text-3xl font-bold tracking-tight text-slate-950">{{ $metric['value'] }}</p>
                        <h3 class="mt-1 text-sm font-semibold text-slate-800">{{ $metric['label'] }}</h3>
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ $metric['detail'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
            <div class="luckin-section rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="luckin-section-kicker text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">AGENT 高频需求</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">高频 Agent 需求</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">四条演示样例，用来说明需求、证据和工作台动作如何衔接。</p>
                    </div>
                    <span class="luckin-chip inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">演示样例</span>
                </div>
                <div class="mt-6 overflow-x-auto">
                    <table class="luckin-table min-w-full divide-y divide-slate-200 text-left">
                        <thead>
                            <tr class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <th scope="col" class="pb-3 pr-5">需求场景</th>
                                <th scope="col" class="pb-3 pr-5">请求量</th>
                                <th scope="col" class="pb-3 pr-5">知识覆盖状态</th>
                                <th scope="col" class="pb-3 pr-5">最近更新时间</th>
                                <th scope="col" class="pb-3 text-right">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($agentDemands as $demand)
                                <tr class="align-top">
                                    <td class="py-4 pr-5 text-sm font-semibold leading-6 text-slate-900">{{ $demand['question'] }}</td>
                                    <td class="py-4 pr-5 text-sm font-semibold text-slate-700">{{ $demand['volume'] }}</td>
                                    <td class="py-4 pr-5 text-sm leading-6 text-slate-500">{{ $demand['coverage'] }}</td>
                                    <td class="py-4 pr-5 text-sm leading-6 text-slate-500">{{ $demand['updated_at'] }}</td>
                                    <td class="py-4 text-right">
                                        <a href="{{ $demand['href'] }}" class="luckin-table-action inline-flex items-center whitespace-nowrap rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                            {{ $demand['action'] }}
                                            <i data-lucide="arrow-up-right" class="ml-1.5 h-3.5 w-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="luckin-section rounded-2xl bg-slate-950 p-6 text-white shadow-sm sm:p-8">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="luckin-section-kicker text-xs font-semibold uppercase tracking-[0.2em] text-rose-300">知识缺口样例</p>
                        <span class="rounded-full border border-amber-300/30 bg-amber-300/10 px-2.5 py-1 text-[10px] font-semibold text-amber-200">演示数据 · 固定概念样例</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight">知识缺口</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-300">以下数量不代表实时经营状态；每个样例仅连接到现有工作台入口，不虚构新的后台路由。</p>
                </div>
                <div class="mt-6 space-y-3">
                    @foreach ($knowledgeGaps as $gap)
                        <a href="{{ $gap['href'] }}" class="luckin-gap-card group block rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-white/25 hover:bg-white/10">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10 text-slate-200">
                                    <i data-lucide="{{ $gap['icon'] }}" class="h-4 w-4"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="text-sm font-semibold leading-6 text-white">{{ $gap['title'] }}</span>
                                        <span class="shrink-0 rounded-full bg-white/10 px-2 py-1 text-[10px] font-semibold text-slate-300">{{ $gap['status'] }}</span>
                                    </span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-400">{{ $gap['desc'] }}</span>
                                    <span class="mt-3 inline-flex items-center text-xs font-semibold text-blue-300 group-hover:text-blue-200">
                                        {{ $gap['action'] }}
                                        <i data-lucide="arrow-right" class="ml-1.5 h-3.5 w-3.5"></i>
                                    </span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="luckin-section rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="luckin-section-kicker text-xs font-semibold uppercase tracking-[0.2em] text-violet-600">GEO 运营流程</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">7 步 GEO 流程</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">从发现 Agent 需求，到用真实运行结果反哺下一轮知识与内容。</p>
                </div>
                <span class="luckin-chip inline-flex w-fit items-center rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">可观测闭环</span>
            </div>
            <div class="mt-6 overflow-x-auto pb-2">
                <ol class="luckin-flow luckin-flow-seven min-w-[1040px]">
                    @foreach ($geoFlowSteps as $step)
                        <li class="luckin-flow-step relative flex min-h-[176px] flex-col rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white hover:shadow-md">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-2xl font-bold tabular-nums text-slate-300">{{ $step['number'] }}</span>
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200">
                                    <i data-lucide="{{ $step['icon'] }}" class="h-4 w-4"></i>
                                </span>
                            </div>
                            <h3 class="mt-5 text-sm font-bold text-slate-900">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-xs leading-5 text-slate-500">{{ $step['desc'] }}</p>
                            @if ($step['href'])
                                <a href="{{ $step['href'] }}" class="mt-auto inline-flex items-center pt-4 text-xs font-semibold text-blue-700 hover:text-blue-600">
                                    查看入口
                                    <i data-lucide="arrow-up-right" class="ml-1.5 h-3.5 w-3.5"></i>
                                </a>
                            @else
                                <span class="mt-auto inline-flex items-center pt-4 text-xs font-semibold text-slate-400">需授权后查看</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="luckin-pilot overflow-hidden rounded-2xl bg-gradient-to-br from-blue-700 via-blue-600 to-slate-900 p-6 text-white shadow-xl sm:p-8">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="luckin-pilot-kicker text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">首期试点场景</span>
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-blue-100">试点进行中 · 演示数据</span>
                    </div>
                    <h2 class="mt-4 text-3xl font-bold tracking-tight">“晚间非咖与混合点单”</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-blue-100 sm:text-base">面向晚上不想喝咖啡、偏好较低甜度，以及与喝咖啡朋友共同点单的用户。试点只验证信息组织与调用路径，不替代真实菜单、价格或配方系统。</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="luckin-pilot-tag rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-50">晚间场景</span>
                        <span class="luckin-pilot-tag rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-50">非咖意图</span>
                        <span class="luckin-pilot-tag rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-50">混合点单</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">试点交付</span>
                        <i data-lucide="route" class="h-5 w-5 text-blue-200"></i>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-white/10 px-2 py-3"><strong class="block text-lg">82%</strong><span class="mt-1 block text-[10px] text-blue-200">场景知识覆盖率</span></div>
                        <div class="rounded-xl bg-white/10 px-2 py-3"><strong class="block text-lg">24</strong><span class="mt-1 block text-[10px] text-blue-200">相关内容数量</span></div>
                        <div class="rounded-xl bg-white/10 px-2 py-3"><strong class="block text-lg">↑18%</strong><span class="mt-1 block text-[10px] text-blue-200">Agent需求趋势</span></div>
                    </div>
                    <ul class="mt-5 space-y-4 text-sm text-blue-50">
                        <li class="flex gap-3"><i data-lucide="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-300"></i><span>一组有来源的可引用品牌事实</span></li>
                        <li class="flex gap-3"><i data-lucide="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-300"></i><span>一套先澄清再回答的问题路径</span></li>
                        <li class="flex gap-3"><i data-lucide="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-300"></i><span>一张可复盘的任务观测卡</span></li>
                    </ul>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <a href="{{ route('admin.tasks.create') }}" class="inline-flex h-10 items-center rounded-lg bg-white px-3.5 text-xs font-bold text-blue-700 transition hover:bg-blue-50">开始编排</a>
                        <a href="{{ route('admin.knowledge-bases.index') }}" class="inline-flex h-10 items-center rounded-lg border border-white/25 bg-transparent px-3.5 text-xs font-bold text-white transition hover:bg-white/10">查看知识库</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-8 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <div class="luckin-section rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="luckin-section-kicker text-xs font-semibold uppercase tracking-[0.2em] text-rose-600">真实任务数据</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">最近任务</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">复用现有 GEO 任务、知识库与内容统计，不生成虚构任务。</p>
                    </div>
                    <a href="{{ route('admin.tasks.index') }}" class="inline-flex w-fit items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        进入任务列表
                        <i data-lucide="arrow-up-right" class="ml-1.5 h-3.5 w-3.5"></i>
                    </a>
                </div>
                <div class="luckin-table-wrap mt-6">
                    <table class="luckin-table min-w-[920px] text-left text-sm">
                        <thead>
                            <tr>
                                <th>任务名称</th>
                                <th>业务场景</th>
                                <th>使用知识库</th>
                                <th>当前状态</th>
                                <th>内容数量</th>
                                <th>更新时间</th>
                                <th class="text-right">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                    @forelse ($recentTasks as $task)
                        @php
                            $taskStatus = (string) data_get($task, 'status', 'inactive');
                            $taskStatusLabel = match ($taskStatus) {
                                'active' => '运行中',
                                'paused' => '已暂停',
                                default => '未启用',
                            };
                            $taskStatusClass = $taskStatus === 'active' ? 'luckin-status-success' : ($taskStatus === 'paused' ? 'luckin-status-warning' : 'luckin-status-info');
                            $taskKnowledgeNames = collect(data_get($task, 'knowledgeBases', []))->pluck('name')->filter();
                            if ($taskKnowledgeNames->isEmpty() && data_get($task, 'knowledgeBase.name')) {
                                $taskKnowledgeNames = collect([data_get($task, 'knowledgeBase.name')]);
                            }
                            $taskScene = trim((string) data_get($task, 'fixedCategory.name', '')) ?: '多场景内容生成';
                        @endphp
                        <tr>
                            <td class="font-semibold text-slate-900">{{ data_get($task, 'name', '未命名任务') }}</td>
                            <td>{{ $taskScene }}</td>
                            <td>{{ $taskKnowledgeNames->isNotEmpty() ? $taskKnowledgeNames->take(2)->join('、') : '未绑定' }}</td>
                            <td><span class="luckin-status {{ $taskStatusClass }}">{{ $taskStatusLabel }}</span></td>
                            <td>{{ (int) data_get($task, 'articles_count', 0) }} 篇</td>
                            <td>{{ optional(data_get($task, 'updated_at'))->format('Y-m-d H:i') ?: '-' }}</td>
                            <td class="text-right"><a href="{{ route('admin.tasks.edit', ['taskId' => (int) data_get($task, 'id')]) }}" class="luckin-table-action inline-flex items-center whitespace-nowrap rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">查看任务 <i data-lucide="arrow-up-right" class="ml-1.5 h-3.5 w-3.5"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-500">暂无真实任务，可从“创建 GEO 任务”开始编排。</td></tr>
                    @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="luckin-section rounded-2xl bg-slate-50 p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="luckin-section-kicker text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">实时运行健康</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">运行健康</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">真实任务状态摘要</p>
                    </div>
                    <span class="luckin-live-badge inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>实时</span>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    @foreach ($liveHealth as $health)
                        <div class="luckin-health-card rounded-xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-slate-500">{{ $health['label'] }}</span>
                                <i data-lucide="{{ $health['icon'] }}" class="h-4 w-4 text-slate-400"></i>
                            </div>
                            <p class="mt-3 text-2xl font-bold tabular-nums text-slate-950">{{ $health['value'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3 text-xs text-slate-500"><span>暂停任务</span><span class="font-bold text-slate-900">{{ $pausedTasks }}</span></div>
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500"><span>今日新增内容</span><span class="font-bold text-slate-900">{{ (int) ($todayStats['today_articles'] ?? 0) }}</span></div>
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500"><span>当前文章总数</span><span class="font-bold text-slate-900">{{ (int) ($stats['total_articles'] ?? 0) }}</span></div>
                </div>
                <a href="{{ route('admin.tasks.health') }}" class="mt-5 inline-flex h-10 w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">打开任务健康检查 <i data-lucide="arrow-right" class="ml-2 h-4 w-4"></i></a>
            </aside>
        </section>

        <details class="luckin-foundation rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 sm:px-8">
                <span>
                    <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">GEOFlow 技术底座</span>
                    <span class="mt-2 block text-xl font-bold text-slate-950">{{ __('admin.dashboard.automation.title') }}</span>
                    <span class="mt-1 block text-sm leading-6 text-slate-500">保留原系统的入口、状态口径与八步能力地图，供演示后继续操作真实流程。</span>
                </span>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><i data-lucide="chevrons-up-down" class="h-4 w-4"></i></span>
            </summary>

            <div class="border-t border-slate-200 px-6 py-6 sm:px-8 sm:py-8">
                <div class="grid gap-4 md:grid-cols-2">
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="font-bold text-slate-950">{{ __('admin.dashboard.navigation.single_site_title') }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('admin.dashboard.navigation.single_site_desc') }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a class="luckin-chip" href="{{ route('admin.ai-models.index') }}">{{ __('admin.dashboard.navigation.ai_config_title') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.knowledge-bases.index') }}">{{ __('admin.dashboard.navigation.materials_title') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.tasks.create') }}">{{ __('admin.dashboard.navigation.create_task_title') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.articles.index') }}">{{ __('admin.dashboard.navigation.articles_title') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.site-settings.index') }}">站点设置</a>
                            <a class="luckin-chip" href="{{ route('admin.dashboard') }}">工作台</a>
                            <a class="luckin-chip" href="{{ route('admin.analytics') }}">观测归因</a>
                        </div>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="font-bold text-slate-950">{{ __('admin.dashboard.navigation.multi_site_title') }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('admin.dashboard.navigation.multi_site_desc') }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($canManageProtectedWorkflows)
                                <a class="luckin-chip" href="{{ route('admin.distribution.index') }}">{{ __('admin.dashboard.navigation.distribution_channels_title') }}</a>
                                <a class="luckin-chip" href="{{ route('admin.distribution.create') }}">新建渠道站点</a>
                                <a class="luckin-chip" href="{{ route('admin.distribution.jobs') }}">{{ __('admin.dashboard.navigation.distribution_jobs_title') }}</a>
                            @else
                                <span class="luckin-chip">分发入口需超级管理员权限</span>
                            @endif
                            <a class="luckin-chip" href="{{ route('admin.admin-users.index') }}">{{ __('admin.dashboard.navigation.admin_users_title') }}</a>
                        </div>
                    </article>
                </div>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">{{ __('admin.dashboard.automation.flow_title') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('admin.dashboard.automation.flow_desc') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="luckin-chip">{{ __('admin.dashboard.automation.running_badge', ['count' => $runningBadgeCount]) }}</span>
                        <span class="luckin-chip">{{ __('admin.dashboard.automation.attention_badge', ['count' => $attentionBadgeCount]) }}</span>
                    </div>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($foundationFlow as $node)
                        @php($foundationStep = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT))
                        @if ($node['href'])
                            <a id="content-engineering-step-{{ $foundationStep }}" href="{{ $node['href'] }}" class="luckin-card-interactive rounded-xl border border-slate-200 bg-white p-4">
                        @else
                            <div id="content-engineering-step-{{ $foundationStep }}" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        @endif
                            <span class="text-xs font-bold text-blue-700">{{ __('admin.dashboard.automation.step_label', ['step' => $foundationStep]) }}</span>
                            <strong class="mt-2 block text-sm text-slate-950">{{ $node['title'] }}</strong>
                            <span class="mt-2 block text-xs leading-5 text-slate-500">{{ $node['desc'] }}</span>
                            @if ($loop->iteration === 2)
                                <span class="mt-3 block text-xs font-semibold text-slate-600">{{ __('admin.dashboard.automation.metric_materials', ['count' => $materialLibraryCount]) }} · {{ __('admin.dashboard.automation.metric_vectorized', ['done' => $vectorizedChunks, 'total' => $knowledgeChunks]) }}</span>
                            @elseif ($loop->last)
                                <span class="mt-3 block text-xs font-semibold text-slate-600">{{ __('admin.dashboard.automation.metric_ai_today', ['count' => $aiUsedToday]) }}</span>
                            @endif
                        @if ($node['href'])
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    <article class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-bold text-slate-950">{{ __('admin.dashboard.automation.recommendations_title') }}</h3>
                        <div class="mt-3 space-y-2">
                            @forelse ($automationRecommendations as $recommendation)
                                <a href="{{ $recommendation['href'] }}" class="block rounded-lg border p-3 {{ $recommendation['tone'] }}">
                                    <span class="flex items-start justify-between gap-3 text-xs font-semibold">
                                        <span>{{ $recommendation['title'] }}</span>
                                        <strong>{{ $recommendation['count'] }}</strong>
                                    </span>
                                    <span class="mt-1 block text-xs leading-5 opacity-80">{{ $recommendation['desc'] }}</span>
                                </a>
                            @empty
                                <p class="text-xs leading-5 text-slate-500">{{ __('admin.dashboard.automation.recommendations_empty') }}</p>
                            @endforelse
                        </div>
                    </article>
                    <article class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-bold text-slate-950">{{ __('admin.dashboard.automation.health_task_title') }}</h3>
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('admin.dashboard.automation.health_task_meta', ['running' => $runningJobs, 'queued' => $pendingJobs, 'failed' => $failedJobs]) }}</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-bold text-slate-950">{{ __('admin.dashboard.automation.health_content_title') }}</h3>
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('admin.dashboard.automation.health_content_meta', ['published' => $publishedArticles, 'drafts' => $draftArticles, 'pending' => $pendingReview]) }}</p>
                    </article>
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <article><h3 class="text-sm font-bold text-slate-950">{{ __('admin.dashboard.automation.lane_single_title') }}</h3><p class="mt-1 text-xs leading-5 text-slate-500">{{ __('admin.dashboard.automation.lane_single_desc') }}</p></article>
                    <article><h3 class="text-sm font-bold text-slate-950">{{ __('admin.dashboard.automation.lane_multi_title') }}</h3><p class="mt-1 text-xs leading-5 text-slate-500">{{ __('admin.dashboard.automation.lane_multi_desc') }}</p></article>
                    <article><h3 class="text-sm font-bold text-slate-950">{{ __('admin.dashboard.automation.lane_feedback_title') }}</h3><p class="mt-1 text-xs leading-5 text-slate-500">{{ __('admin.dashboard.automation.lane_feedback_desc') }}</p></article>
                </div>

                <div class="mt-8 grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                    <article class="rounded-xl bg-blue-50 p-5">
                        <h3 class="text-base font-bold text-blue-950">{{ __('admin.dashboard.demo_journey.title') }}</h3>
                        <p class="mt-2 text-sm leading-6 text-blue-900/75">{{ __('admin.dashboard.demo_journey.assets_title') }} → {{ __('admin.dashboard.demo_journey.quality_title') }} → {{ __('admin.dashboard.demo_journey.observation_title') }}</p>
                        <h4 class="mt-5 text-sm font-bold text-blue-950">{{ __('admin.dashboard.quick_start.title') }}</h4>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a class="luckin-chip" href="{{ route('admin.ai-models.index') }}">{{ __('admin.dashboard.quick_start.api_title') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.knowledge-bases.index') }}">{{ __('admin.dashboard.quick_start.material_title') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.tasks.create') }}">{{ __('admin.dashboard.quick_start.task_title') }}</a>
                        </div>
                    </article>
                    <article class="rounded-xl border border-slate-200 p-5">
                        <h3 class="text-base font-bold text-slate-950">{{ __('admin.dashboard.navigation.prompt_config_title') }}</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a class="luckin-chip" href="{{ route('admin.ai-prompts') }}">{{ __('admin.dashboard.navigation.body_prompt_label') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.ai-special-prompts') }}">{{ __('admin.dashboard.navigation.special_prompt_label') }}</a>
                            <a class="luckin-chip" href="{{ route('admin.title-libraries.index') }}">标题库</a>
                            <a class="luckin-chip" href="{{ route('admin.keyword-libraries.index') }}">关键词库</a>
                            <a class="luckin-chip" href="{{ route('admin.image-libraries.index') }}">图片库</a>
                            <a class="luckin-chip" href="{{ route('admin.authors.index') }}">作者</a>
                            <a class="luckin-chip" href="{{ route('admin.materials.index') }}">素材</a>
                        </div>
                    </article>
                </div>

                <div class="mt-8">
                    <h3 class="text-base font-bold text-slate-950">{{ __('admin.dashboard.skill_resources.title') }}</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <a class="luckin-card-interactive rounded-xl border border-slate-200 p-4 text-sm font-bold text-blue-700" href="https://github.com/yaojingang/yao-geo-skills/tree/main/skills/yao-geoflow-template" target="_blank" rel="noopener noreferrer">{{ __('admin.dashboard.skill_resources.template_title') }}</a>
                        <a class="luckin-card-interactive rounded-xl border border-slate-200 p-4 text-sm font-bold text-blue-700" href="https://github.com/yaojingang/yao-geo-skills/tree/main/skills/yao-geoflow-design" target="_blank" rel="noopener noreferrer">{{ __('admin.dashboard.skill_resources.design_title') }}</a>
                        <a class="luckin-card-interactive rounded-xl border border-slate-200 p-4 text-sm font-bold text-blue-700" href="https://github.com/yaojingang/yao-geo-skills/tree/main/skills/yao-geoflow-cli" target="_blank" rel="noopener noreferrer">{{ __('admin.dashboard.skill_resources.cli_title') }}</a>
                    </div>
                </div>
            </div>
        </details>
    </div>
@endsection
