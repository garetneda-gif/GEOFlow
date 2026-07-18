@extends('admin.layouts.app')

@section('content')
    @php
        $status = in_array($mcpState['status'] ?? null, ['connected', 'partial', 'authorization_required', 'pending', 'error', 'disabled'], true)
            ? $mcpState['status']
            : 'error';
        $selectedTool = old('tool', 'queryShopList');
        $toolLabels = [
            'queryShopList' => __('luckin_mcp.tools.query_shop'),
            'searchProductForMcp' => __('luckin_mcp.tools.search_product'),
            'switchProduct' => __('luckin_mcp.tools.switch_product'),
            'queryProductDetailInfo' => __('luckin_mcp.tools.product_detail'),
        ];
    @endphp

    <div class="luckin-mcp-workspace px-4 sm:px-0">
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-4">
                <a href="{{ route('admin.knowledge-bases.index') }}" class="text-gray-400 hover:text-gray-600" aria-label="{{ __('luckin_mcp.back') }}">
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </a>
                <div class="luckin-mcp-brand-visual luckin-mcp-brand-visual-workspace" role="img" aria-label="luckin coffee 幸运在握"></div>
                <div class="min-w-0 flex-1 border-l border-gray-200 pl-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('luckin_mcp.heading') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('luckin_mcp.subtitle') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold {{ $status === 'connected' ? 'bg-emerald-50 text-emerald-700' : ($status === 'authorization_required' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                    <span class="h-2 w-2 rounded-full bg-current"></span>
                    {{ __('luckin_mcp.status.'.$status) }}
                </span>
            </div>
        </div>

        @if (session('message'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('message') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="mb-6 rounded-lg border border-blue-100 bg-white px-5 py-5 shadow-sm" data-luckin-mcp-api-key>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-base font-semibold text-gray-900">{{ __('luckin_mcp.api_key_title') }}</h2>
                        @if ($apiKeyMask !== '')
                            <span class="text-xs font-medium text-emerald-700">{{ __('luckin_mcp.api_key_configured', ['mask' => $apiKeyMask]) }}</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.knowledge-bases.luckin-mcp.api-key.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row" data-luckin-mcp-api-key-form>
                        @csrf
                        <label for="luckin-mcp-api-key" class="sr-only">{{ __('luckin_mcp.field.api_key') }}</label>
                        <input id="luckin-mcp-api-key" type="password" name="api_key" required minlength="20" maxlength="4096" autocomplete="off" class="block min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('luckin_mcp.api_key_placeholder') }}">
                        <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i data-lucide="shield-check" class="mr-2 h-4 w-4"></i>
                            {{ __('luckin_mcp.api_key_save') }}
                        </button>
                    </form>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-3">
                    <a href="https://open.lkcoffee.com/mcp" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900">
                        {{ __('luckin_mcp.api_key_get') }}
                        <i data-lucide="external-link" class="h-4 w-4"></i>
                    </a>
                    @if ($apiKeyMask !== '')
                        <form method="POST" action="{{ route('admin.knowledge-bases.luckin-mcp.api-key.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-gray-500 hover:text-red-600">{{ __('luckin_mcp.api_key_clear') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.72fr)]">
            <section class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('luckin_mcp.query_title') }}</h2>
                </div>
                <form method="POST" action="{{ route('admin.knowledge-bases.luckin-mcp.query') }}" class="space-y-5 p-6" data-luckin-mcp-query-form>
                    @csrf
                    <div>
                        <label for="luckin-mcp-tool" class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.tool') }}</label>
                        <select id="luckin-mcp-tool" name="tool" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" data-luckin-mcp-tool>
                            @foreach ($tools as $tool)
                                <option value="{{ $tool }}" @selected($selectedTool === $tool)>{{ $toolLabels[$tool] ?? $tool }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2" data-luckin-fields="queryShopList">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.longitude') }}</label>
                            <input type="number" step="any" name="longitude" value="{{ old('longitude') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.latitude') }}</label>
                            <input type="number" step="any" name="latitude" value="{{ old('latitude') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.dept_name') }}</label>
                            <input type="text" name="deptName" value="{{ old('deptName') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2" data-luckin-fields="searchProductForMcp">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.dept_id') }}</label>
                            <input type="number" min="1" name="deptId" value="{{ old('deptId') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.query') }}</label>
                            <input type="text" name="query" value="{{ old('query') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2" data-luckin-fields="queryProductDetailInfo">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.dept_id') }}</label>
                            <input type="number" min="1" name="deptId" value="{{ old('deptId') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.product_id') }}</label>
                            <input type="number" min="1" name="productId" value="{{ old('productId') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2" data-luckin-fields="switchProduct">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.dept_id') }}</label>
                            <input type="number" min="1" name="deptId" value="{{ old('deptId') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.product_id') }}</label>
                            <input type="number" min="1" name="productId" value="{{ old('productId') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.sku_code') }}</label>
                            <input type="text" name="skuCode" value="{{ old('skuCode') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.amount') }}</label>
                            <input type="number" min="1" max="20" name="amount" value="{{ old('amount', 1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.attr_operation') }}</label>
                            <textarea name="attrOperationParam" rows="4" class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder='{"attributeId":122,"subAttr":{"attributeId":428,"operation":3}}'>{{ old('attrOperationParam') }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-gray-100 pt-5">
                        <p class="text-xs leading-5 text-gray-500">{{ __('luckin_mcp.query_privacy') }}</p>
                        <button type="submit" @disabled(!($mcpState['configured'] ?? false)) class="inline-flex shrink-0 items-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-300">
                            <i data-lucide="search" class="mr-2 h-4 w-4"></i>
                            {{ __('luckin_mcp.query_action') }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('luckin_mcp.preview_title') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('luckin_mcp.preview_desc') }}</p>
                </div>
                @if (is_array($preview))
                    <div class="space-y-5 p-6">
                        <div class="rounded-md bg-slate-950 p-4 text-xs leading-5 text-slate-100">
                            <div class="mb-3 flex items-center justify-between gap-3 text-slate-300">
                                <span>{{ $toolLabels[$preview['tool']] ?? $preview['tool'] }}</span>
                                <time>{{ $preview['retrieved_at'] }}</time>
                            </div>
                            <pre class="max-h-[420px] overflow-auto whitespace-pre-wrap break-words">{{ json_encode($preview['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                        <form method="POST" action="{{ route('admin.knowledge-bases.luckin-mcp.import') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="preview_token" value="{{ $preview['token'] }}">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('luckin_mcp.field.knowledge_base_name') }}</label>
                                <input type="text" name="knowledge_base_name" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('luckin_mcp.name_placeholder') }}">
                            </div>
                            <p class="text-xs leading-5 text-gray-500">{{ __('luckin_mcp.import_confirmation') }}</p>
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i data-lucide="database-zap" class="mr-2 h-4 w-4"></i>
                                {{ __('luckin_mcp.import_action') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex min-h-72 flex-col items-center justify-center px-8 py-12 text-center">
                        <i data-lucide="database-zap" class="h-10 w-10 text-gray-300"></i>
                        <p class="mt-4 text-sm font-medium text-gray-700">{{ __('luckin_mcp.preview_empty') }}</p>
                        <p class="mt-1 max-w-sm text-xs leading-5 text-gray-500">{{ __('luckin_mcp.preview_empty_desc') }}</p>
                    </div>
                @endif
            </section>
        </div>

        <section class="mt-6 rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('luckin_mcp.recent_title') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('luckin_mcp.recent_desc') }}</p>
                </div>
            </div>
            @forelse ($recentImports as $knowledgeBase)
                <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $knowledgeBase->name }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $knowledgeBase->created_at?->format('Y-m-d H:i') }} · {{ __('luckin_mcp.review_status.'.$knowledgeBase->review_status) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.knowledge-bases.detail', ['knowledgeBaseId' => $knowledgeBase->id]) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('luckin_mcp.view_knowledge') }}</a>
                        @if ($knowledgeBase->isUsableForGeneration())
                            <a href="{{ route('admin.tasks.create', ['knowledge_base_id' => $knowledgeBase->id]) }}" class="inline-flex items-center rounded-md bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">{{ __('luckin_mcp.create_task') }}</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">{{ __('luckin_mcp.recent_empty') }}</div>
            @endforelse
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.querySelector('[data-luckin-mcp-tool]');
            const groups = Array.from(document.querySelectorAll('[data-luckin-fields]'));
            const sync = () => {
                groups.forEach((group) => {
                    const active = group.dataset.luckinFields === select?.value;
                    group.classList.toggle('hidden', !active);
                    group.querySelectorAll('input, textarea').forEach((input) => {
                        input.disabled = !active;
                    });
                });
            };
            select?.addEventListener('change', sync);
            sync();
        });
    </script>
@endsection
