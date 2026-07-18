<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use App\Services\GeoFlow\LuckinMcpClient;
use App\Services\GeoFlow\LuckinMcpCredentialStore;
use App\Services\GeoFlow\LuckinMcpKnowledgeService;
use App\Support\AdminWeb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

final class LuckinMcpKnowledgeController extends Controller
{
    public function __construct(
        private readonly LuckinMcpKnowledgeService $knowledgeService,
        private readonly LuckinMcpClient $client,
        private readonly LuckinMcpCredentialStore $credentials,
    ) {}

    public function index(): View
    {
        return $this->workspace();
    }

    public function saveApiKey(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'api_key' => ['required', 'string', 'min:20', 'max:4096'],
        ]);
        $adminId = (int) auth('admin')->id();
        $apiKey = trim((string) $payload['api_key']);
        $state = $this->client->probeToken($apiKey);

        if ($state['status'] === 'authorization_required') {
            return back()->withErrors(['api_key' => __('luckin_mcp.errors.invalid_api_key')]);
        }
        if (! in_array($state['status'], ['connected', 'partial'], true)) {
            return back()->withErrors(['api_key' => __('luckin_mcp.errors.unavailable')]);
        }

        $previousToken = $this->credentials->tokenFor($adminId);
        $this->credentials->put($adminId, $apiKey);
        if ($previousToken !== '' && ! hash_equals($previousToken, $apiKey)) {
            $this->client->forgetCachedToken($previousToken);
        }

        return redirect()
            ->route('admin.knowledge-bases.luckin-mcp.index')
            ->with('message', __('luckin_mcp.api_key_saved'));
    }

    public function clearApiKey(): RedirectResponse
    {
        $adminId = (int) auth('admin')->id();
        $previousToken = $this->credentials->tokenFor($adminId);
        $this->credentials->forget($adminId);
        $this->client->forgetCachedToken($previousToken);

        return redirect()
            ->route('admin.knowledge-bases.luckin-mcp.index')
            ->with('message', __('luckin_mcp.api_key_cleared'));
    }

    public function query(Request $request): View|RedirectResponse
    {
        $payload = $request->validate([
            'tool' => ['required', 'string', 'max:64'],
            'deptName' => ['nullable', 'string', 'max:100'],
            'longitude' => ['nullable'],
            'latitude' => ['nullable'],
            'deptId' => ['nullable'],
            'query' => ['nullable', 'string', 'max:300'],
            'productId' => ['nullable'],
            'skuCode' => ['nullable', 'string', 'max:128'],
            'attrOperationParam' => ['nullable', 'string', 'max:2000'],
            'amount' => ['nullable'],
        ]);

        try {
            $arguments = $this->knowledgeService->normalizeArguments((string) $payload['tool'], $payload);
            $preview = $this->knowledgeService->preview(
                (int) auth('admin')->id(),
                (string) $payload['tool'],
                $arguments,
            );

            return $this->workspace($preview);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors($this->messageFor($exception));
        } catch (Throwable) {
            return back()->withInput()->withErrors(__('luckin_mcp.errors.unavailable'));
        }
    }

    public function import(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'preview_token' => ['required', 'string', 'size:64'],
            'knowledge_base_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $knowledgeBase = $this->knowledgeService->importPreview(
                (int) auth('admin')->id(),
                (string) $payload['preview_token'],
                isset($payload['knowledge_base_name']) ? (string) $payload['knowledge_base_name'] : null,
            );

            return redirect()
                ->route('admin.knowledge-bases.luckin-mcp.index')
                ->with('message', __('luckin_mcp.import_success', ['name' => $knowledgeBase->name]));
        } catch (RuntimeException $exception) {
            return back()->withErrors($this->messageFor($exception));
        } catch (Throwable) {
            return back()->withErrors(__('luckin_mcp.errors.import_failed'));
        }
    }

    private function workspace(?array $preview = null): View
    {
        $adminId = (int) auth('admin')->id();

        return view('admin.knowledge-bases.luckin-mcp', [
            'pageTitle' => __('luckin_mcp.page_title'),
            'activeMenu' => 'materials',
            'adminSiteName' => AdminWeb::siteName(),
            'mcpState' => $this->client->cachedState($adminId),
            'apiKeyMask' => $this->credentials->maskFor($adminId),
            'tools' => $this->knowledgeService->tools(),
            'preview' => $preview,
            'recentImports' => KnowledgeBase::query()
                ->where('source_type', KnowledgeBase::LUCKIN_MCP_SOURCE_TYPE)
                ->where('source_url', KnowledgeBase::LUCKIN_MCP_SOURCE_URL)
                ->latest('id')
                ->limit(8)
                ->get(['id', 'name', 'review_status', 'effective_date', 'created_at']),
        ]);
    }

    private function messageFor(RuntimeException $exception): string
    {
        $key = $exception->getMessage();
        if (! in_array($key, [
            'invalid_arguments',
            'authorization_required',
            'invalid_result',
            'empty_result',
            'preview_invalid',
            'preview_busy',
            'chunk_sync_failed',
            'publish_failed',
            'tool_error',
            'remote_error',
            'protocol_error',
        ], true)) {
            $key = 'unavailable';
        }

        return __('luckin_mcp.errors.'.$key);
    }
}
