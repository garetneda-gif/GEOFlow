<?php

namespace Tests\Unit;

use App\Services\GeoFlow\LuckinMcpClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class LuckinMcpClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
        config([
            'geoflow.luckin_mcp.enabled' => true,
            'geoflow.luckin_mcp.token' => 'test-token-never-render',
        ]);
    }

    public function test_missing_token_returns_authorization_state_without_network(): void
    {
        config(['geoflow.luckin_mcp.token' => '']);
        Http::fake();

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('authorization_required', $state['status']);
        $this->assertFalse($state['configured']);
        Http::assertNothingSent();
    }

    public function test_refresh_negotiates_session_and_caches_only_declared_product_tools(): void
    {
        Http::fakeSequence()
            ->push($this->jsonRpc(1, [
                'protocolVersion' => '2025-06-18',
                'capabilities' => ['tools' => new \stdClass],
            ]), 200, [
                'Content-Type' => 'application/json',
                'Mcp-Session-Id' => 'safe-session-1',
            ])
            ->push('', 202)
            ->push($this->jsonRpc(2, [
                'tools' => [
                    ['name' => 'queryShopList'],
                    ['name' => 'searchProductForMcp'],
                    ['name' => 'switchProduct'],
                    ['name' => 'queryProductDetailInfo'],
                    ['name' => 'createOrder'],
                ],
            ]), 200, ['Content-Type' => 'application/json'])
            ->push('', 405);

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('connected', $state['status']);
        $this->assertSame('2025-06-18', $state['protocol_version']);
        $this->assertSame([
            'queryShopList' => true,
            'searchProductForMcp' => true,
            'switchProduct' => true,
            'queryProductDetailInfo' => true,
        ], $state['capabilities']);
        $this->assertStringNotContainsString('test-token-never-render', json_encode($state));
        $this->assertSame($state, app(LuckinMcpClient::class)->cachedState());

        $requests = [];
        Http::assertSent(function (Request $request) use (&$requests): bool {
            $requests[] = $request;

            return true;
        });
        $this->assertCount(4, $requests);
        $this->assertSame('initialize', $requests[0]->data()['method']);
        $this->assertSame('notifications/initialized', $requests[1]->data()['method']);
        $this->assertSame('tools/list', $requests[2]->data()['method']);
        $this->assertSame('Bearer test-token-never-render', $requests[0]->header('Authorization')[0] ?? null);
        $this->assertSame('safe-session-1', $requests[1]->header('Mcp-Session-Id')[0] ?? null);
        $this->assertSame('2025-06-18', $requests[2]->header('MCP-Protocol-Version')[0] ?? null);
    }

    public function test_session_expiry_reinitializes_once(): void
    {
        Http::fakeSequence()
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'expired-session'])
            ->push('', 202)
            ->push('', 404)
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'new-session'])
            ->push('', 202)
            ->push($this->jsonRpc(2, ['tools' => []]), 200, ['Content-Type' => 'application/json'])
            ->push('', 405);

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('partial', $state['status']);
        Http::assertSentCount(7);
    }

    public function test_initialized_notification_session_expiry_reinitializes_once(): void
    {
        Http::fakeSequence()
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'expired-during-notification'])
            ->push('', 404)
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'replacement-session'])
            ->push('', 202)
            ->push($this->jsonRpc(2, ['tools' => []]), 200, ['Content-Type' => 'application/json'])
            ->push('', 405);

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('partial', $state['status']);
        Http::assertSentCount(6);
    }

    public function test_tool_listing_follows_bounded_pagination(): void
    {
        Http::fakeSequence()
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'paged-session'])
            ->push('', 202)
            ->push($this->jsonRpc(2, [
                'tools' => [['name' => 'queryShopList'], ['name' => 'searchProductForMcp']],
                'nextCursor' => 'page-2',
            ]), 200, ['Content-Type' => 'application/json'])
            ->push($this->jsonRpc(3, [
                'tools' => [['name' => 'switchProduct'], ['name' => 'queryProductDetailInfo']],
            ]), 200, ['Content-Type' => 'application/json'])
            ->push('', 405);

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('connected', $state['status']);
        Http::assertSent(function (Request $request): bool {
            $params = $request->data()['params'] ?? null;

            return ($request->data()['method'] ?? null) === 'tools/list'
                && is_array($params)
                && ($params['cursor'] ?? null) === 'page-2';
        });
    }

    public function test_repeated_pagination_cursor_becomes_sanitized_error(): void
    {
        Http::fakeSequence()
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'loop-session'])
            ->push('', 202)
            ->push($this->jsonRpc(2, ['tools' => [], 'nextCursor' => 'same']), 200, ['Content-Type' => 'application/json'])
            ->push($this->jsonRpc(3, ['tools' => [], 'nextCursor' => 'same']), 200, ['Content-Type' => 'application/json'])
            ->push('', 405);

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('error', $state['status']);
        $this->assertSame('', $state['protocol_version']);
        Http::assertSentCount(5);
    }

    public function test_sse_tool_response_accepts_comments_multiline_data_and_matching_id(): void
    {
        $sse = ": keepalive\r\n"
            ."data: {\"jsonrpc\":\"2.0\",\"method\":\"notifications/progress\"}\r\n\r\n"
            ."data: {\"jsonrpc\":\"2.0\",\"id\":2,\"method\":\"sampling/createMessage\",\"params\":{}}\r\n\r\n"
            ."event: message\r\n"
            ."data: {\"jsonrpc\":\"2.0\",\"id\":2,\r\n"
            ."data: \"result\":{\"content\":[]}}\r\n\r\n";
        Http::fakeSequence()
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'sse-session'])
            ->push('', 202)
            ->push($sse, 200, ['Content-Type' => 'text/event-stream; charset=utf-8'])
            ->push('', 405);

        $result = app(LuckinMcpClient::class)->callProductTool('queryShopList', [
            'longitude' => 116.397,
            'latitude' => 39.908,
        ]);

        $this->assertSame([], $result['content']);
    }

    public function test_sse_error_response_is_classified_as_remote_error(): void
    {
        $sse = "data: {\"jsonrpc\":\"2.0\",\"id\":2,\"error\":{\"code\":-32603,\"message\":\"private remote detail\"}}\n\n";
        Http::fakeSequence()
            ->push($this->initializeResponse(1), 200, ['Content-Type' => 'application/json', 'Mcp-Session-Id' => 'sse-error-session'])
            ->push('', 202)
            ->push($sse, 200, ['Content-Type' => 'text/event-stream'])
            ->push('', 405);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('remote_error');

        app(LuckinMcpClient::class)->callProductTool('queryShopList', [
            'longitude' => 116.397,
            'latitude' => 39.908,
        ]);
    }

    public function test_order_tool_and_unknown_arguments_are_rejected_before_network(): void
    {
        Http::fake();
        $client = app(LuckinMcpClient::class);

        foreach ([
            ['createOrder', []],
            ['queryShopList', ['longitude' => 116.397, 'latitude' => 39.908, 'orderId' => 1]],
        ] as [$tool, $arguments]) {
            try {
                $client->callProductTool($tool, $arguments);
                $this->fail('Unsafe tool call was not rejected.');
            } catch (RuntimeException $exception) {
                $this->assertContains($exception->getMessage(), ['tool_not_allowed', 'invalid_arguments']);
            }
        }

        Http::assertNothingSent();
    }

    public function test_invalid_session_header_is_sanitized_to_error_state(): void
    {
        Http::fakeSequence()->push(
            $this->initializeResponse(1),
            200,
            ['Content-Type' => 'application/json', 'Mcp-Session-Id' => "bad\theader"],
        );

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('error', $state['status']);
        $this->assertStringNotContainsString('bad', json_encode($state));
    }

    public function test_remote_authorization_body_is_never_returned_or_cached(): void
    {
        Http::fakeSequence()->push(
            'remote secret detail that must stay private',
            401,
            ['Content-Type' => 'text/plain'],
        );

        $state = app(LuckinMcpClient::class)->refreshState();

        $this->assertSame('authorization_required', $state['status']);
        $this->assertStringNotContainsString('remote secret detail', json_encode($state));
        $this->assertSame($state, app(LuckinMcpClient::class)->cachedState());
    }

    private function initializeResponse(int $id): string
    {
        return $this->jsonRpc($id, [
            'protocolVersion' => '2025-06-18',
            'capabilities' => ['tools' => new \stdClass],
        ]);
    }

    private function jsonRpc(int $id, array $result): string
    {
        return (string) json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ], JSON_UNESCAPED_SLASHES);
    }
}
