<?php

namespace App\Services\GeoFlow;

use App\Services\Outbound\SafeOutboundHttpClient;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use stdClass;

final class LuckinMcpClient
{
    private const SUPPORTED_PROTOCOL_VERSION = '2025-06-18';

    private const PRODUCT_TOOLS = [
        'queryShopList',
        'searchProductForMcp',
        'switchProduct',
        'queryProductDetailInfo',
    ];

    private const TOOL_FIELDS = [
        'queryShopList' => ['deptName', 'longitude', 'latitude'],
        'searchProductForMcp' => ['deptId', 'query'],
        'switchProduct' => ['deptId', 'productId', 'skuCode', 'attrOperationParam', 'amount'],
        'queryProductDetailInfo' => ['deptId', 'productId'],
    ];

    public function __construct(
        private readonly SafeOutboundHttpClient $safeHttp,
        private readonly Factory $http,
        private readonly LuckinMcpCredentialStore $credentials,
    ) {}

    public function cachedState(int $adminId): array
    {
        $token = $this->credentials->tokenFor($adminId);
        $base = $this->baseState($token);
        if (! $base['enabled'] || ! $base['configured']) {
            return $base;
        }

        $cached = $this->cacheGet($this->cacheKey($token));
        if (! is_array($cached)) {
            return $base;
        }

        return $this->sanitizeState($cached, $base);
    }

    public function refreshState(int $adminId): array
    {
        $token = $this->credentials->tokenFor($adminId);
        $state = $this->probeToken($token);

        return $state;
    }

    public function probeToken(string $token): array
    {
        $token = trim($token);
        $base = $this->baseState($token);
        if (! $base['enabled'] || ! $base['configured']) {
            return $base;
        }

        try {
            $available = $this->withSession($token, function (array &$session) use ($token): array {
                return $this->listTools($session, $token);
            });
            $capabilities = [];
            foreach (self::PRODUCT_TOOLS as $tool) {
                $capabilities[$tool] = in_array($tool, $available, true);
            }
            $availableCount = count(array_filter($capabilities));
            $state = [
                ...$base,
                'status' => $availableCount === count(self::PRODUCT_TOOLS) ? 'connected' : 'partial',
                'capabilities' => $capabilities,
                'protocol_version' => self::SUPPORTED_PROTOCOL_VERSION,
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (RuntimeException $exception) {
            $state = [
                ...$base,
                'status' => $exception->getMessage() === 'authorization_required' ? 'authorization_required' : 'error',
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable) {
            $state = [
                ...$base,
                'status' => 'error',
                'checked_at' => now()->toIso8601String(),
            ];
        }

        $state = $this->sanitizeState($state, $base);
        $this->cachePut($this->cacheKey($token), $state);

        return $state;
    }

    public function callProductTool(int $adminId, string $name, array $arguments): array
    {
        $this->assertToolArguments($name, $arguments);
        $token = $this->credentials->tokenFor($adminId);
        $base = $this->baseState($token);
        if (! $base['enabled'] || ! $base['configured']) {
            throw new RuntimeException('authorization_required');
        }

        return $this->withSession($token, function (array &$session) use ($token, $name, $arguments): array {
            $result = $this->request($session, $token, 'tools/call', [
                'name' => $name,
                'arguments' => $arguments,
            ]);
            if (($result['isError'] ?? false) === true) {
                throw new RuntimeException('tool_error');
            }

            return $result;
        });
    }

    public function forgetCachedState(int $adminId): void
    {
        $this->forgetCachedToken($this->credentials->tokenFor($adminId));
    }

    public function forgetCachedToken(string $token): void
    {
        if ($token !== '') {
            try {
                Cache::forget($this->cacheKey($token));
            } catch (\Throwable $exception) {
                $this->logCacheFailure('forget', $exception);
            }
        }
    }

    private function cacheGet(string $key): mixed
    {
        try {
            return Cache::get($key);
        } catch (\Throwable $exception) {
            $this->logCacheFailure('get', $exception);

            return null;
        }
    }

    private function cachePut(string $key, array $state): void
    {
        try {
            Cache::put($key, $state, $this->stateTtlSeconds());
        } catch (\Throwable $exception) {
            $this->logCacheFailure('put', $exception);
        }
    }

    private function logCacheFailure(string $operation, \Throwable $exception): void
    {
        Log::warning('Luckin MCP state cache operation failed.', [
            'operation' => $operation,
            'exception' => $exception::class,
        ]);
    }

    private function withSession(string $token, callable $operation): array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $session = null;
            try {
                $session = $this->initializeSession($token);
                $result = $operation($session);
                $this->closeSession($session, $token);

                return $result;
            } catch (LuckinMcpSessionExpiredException) {
                if ($attempt === 1) {
                    throw new RuntimeException('protocol_error');
                }
            } catch (\Throwable $exception) {
                if ($session !== null) {
                    $this->closeSession($session, $token);
                }

                throw $exception;
            }
        }

        throw new RuntimeException('protocol_error');
    }

    private function initializeSession(string $token): array
    {
        $response = $this->post([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => self::SUPPORTED_PROTOCOL_VERSION,
                'capabilities' => new stdClass,
                'clientInfo' => [
                    'name' => 'GEOFlow Luckin Product Connector',
                    'version' => (string) config('geoflow.app_version', '2.1.1'),
                ],
            ],
        ], null, self::SUPPORTED_PROTOCOL_VERSION, $token);
        $result = $this->responseResult($response, 1);
        if (($result['protocolVersion'] ?? null) !== self::SUPPORTED_PROTOCOL_VERSION) {
            throw new RuntimeException('protocol_error');
        }
        $capabilities = $result['capabilities'] ?? null;
        if (! is_array($capabilities) || ! array_key_exists('tools', $capabilities)) {
            throw new RuntimeException('protocol_error');
        }

        $sessionId = trim((string) $response->header('Mcp-Session-Id', ''));
        if ($sessionId !== '' && ! $this->validSessionId($sessionId)) {
            throw new RuntimeException('protocol_error');
        }
        $session = [
            'id' => $sessionId,
            'protocol_version' => self::SUPPORTED_PROTOCOL_VERSION,
            'next_id' => 2,
        ];

        $initialized = $this->post([
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
            'params' => new stdClass,
        ], $session, self::SUPPORTED_PROTOCOL_VERSION, $token);
        if (! $initialized->successful()) {
            throw new RuntimeException('protocol_error');
        }

        return $session;
    }

    private function listTools(array &$session, string $token): array
    {
        $tools = [];
        $cursor = null;
        $seenCursors = [];

        for ($page = 0; $page < 4; $page++) {
            $params = $cursor === null ? new stdClass : ['cursor' => $cursor];
            $result = $this->request($session, $token, 'tools/list', $params);
            $rows = $result['tools'] ?? null;
            if (! is_array($rows)) {
                throw new RuntimeException('protocol_error');
            }
            foreach ($rows as $tool) {
                $name = is_array($tool) ? ($tool['name'] ?? null) : null;
                if (! is_string($name) || preg_match('/^[A-Za-z][A-Za-z0-9_.-]{0,127}$/', $name) !== 1) {
                    throw new RuntimeException('protocol_error');
                }
                $tools[] = $name;
                if (count($tools) > 100) {
                    throw new RuntimeException('protocol_error');
                }
            }

            $nextCursor = $result['nextCursor'] ?? null;
            if ($nextCursor === null || $nextCursor === '') {
                return array_values(array_unique($tools));
            }
            if (! is_string($nextCursor) || strlen($nextCursor) > 512 || isset($seenCursors[$nextCursor])) {
                throw new RuntimeException('protocol_error');
            }
            $seenCursors[$nextCursor] = true;
            $cursor = $nextCursor;
        }

        throw new RuntimeException('protocol_error');
    }

    private function request(array &$session, string $token, string $method, array|stdClass $params): array
    {
        $id = $session['next_id']++;
        $response = $this->post([
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ], $session, $session['protocol_version'], $token);

        return $this->responseResult($response, $id);
    }

    private function post(array $payload, ?array $session, string $protocolVersion, string $token): Response
    {
        $headers = [
            'Accept' => 'application/json, text/event-stream',
            'MCP-Protocol-Version' => $protocolVersion,
        ];
        if ($session !== null && $session['id'] !== '') {
            $headers['Mcp-Session-Id'] = $session['id'];
        }

        $request = $this->http
            ->timeout($this->timeoutSeconds())
            ->connectTimeout($this->connectTimeoutSeconds())
            ->withToken($token)
            ->withHeaders($headers)
            ->asJson();
        $response = $this->safeHttp->post(
            $request,
            $this->endpoint(),
            $payload,
            $this->maxResponseBytes(),
        );

        if (in_array($response->status(), [401, 403], true)) {
            throw new RuntimeException('authorization_required');
        }
        if ($response->status() === 404 && $session !== null && $session['id'] !== '') {
            throw new LuckinMcpSessionExpiredException;
        }
        if (! $response->successful()) {
            throw new RuntimeException('transport_error');
        }

        return $response;
    }

    private function closeSession(array $session, string $token): void
    {
        if ($session['id'] === '') {
            return;
        }

        try {
            $request = $this->http
                ->timeout($this->timeoutSeconds())
                ->connectTimeout($this->connectTimeoutSeconds())
                ->withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json, text/event-stream',
                    'MCP-Protocol-Version' => $session['protocol_version'],
                    'Mcp-Session-Id' => $session['id'],
                ])
                ->asJson();
            $this->safeHttp->delete(
                $request,
                $this->endpoint(),
                [],
                $this->maxResponseBytes(),
            );
        } catch (\Throwable) {
        }
    }

    private function responseResult(Response $response, int $expectedId): array
    {
        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type', ''))[0]));
        if ($contentType === 'application/json') {
            $message = $this->decodeJson($response->body());
        } elseif ($contentType === 'text/event-stream') {
            $message = $this->decodeSse($response->body(), $expectedId);
        } else {
            throw new RuntimeException('protocol_error');
        }

        if (($message['jsonrpc'] ?? null) !== '2.0' || ($message['id'] ?? null) !== $expectedId) {
            throw new RuntimeException('protocol_error');
        }
        $hasResult = array_key_exists('result', $message);
        $hasError = array_key_exists('error', $message);
        if ($hasResult === $hasError) {
            throw new RuntimeException('protocol_error');
        }
        if ($hasError) {
            throw new RuntimeException('remote_error');
        }
        if (! is_array($message['result'])) {
            throw new RuntimeException('protocol_error');
        }

        return $message['result'];
    }

    private function decodeJson(string $body): array
    {
        try {
            $decoded = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('protocol_error');
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw new RuntimeException('protocol_error');
        }

        return $decoded;
    }

    private function decodeSse(string $body, int $expectedId): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $body);
        $events = preg_split('/\n{2,}/', $normalized) ?: [];
        foreach ($events as $event) {
            $data = [];
            foreach (explode("\n", $event) as $line) {
                if ($line === '' || str_starts_with($line, ':')) {
                    continue;
                }
                if (str_starts_with($line, 'data:')) {
                    $data[] = ltrim(substr($line, 5), ' ');
                }
            }
            if ($data === []) {
                continue;
            }
            $message = $this->decodeJson(implode("\n", $data));
            $hasResult = array_key_exists('result', $message);
            $hasError = array_key_exists('error', $message);
            $isResponse = $hasResult !== $hasError;
            if (($message['id'] ?? null) === $expectedId && $isResponse) {
                return $message;
            }
        }

        throw new RuntimeException('protocol_error');
    }

    private function assertToolArguments(string $name, array $arguments): void
    {
        if (! in_array($name, self::PRODUCT_TOOLS, true)) {
            throw new RuntimeException('tool_not_allowed');
        }
        $unknown = array_diff(array_keys($arguments), self::TOOL_FIELDS[$name]);
        if ($unknown !== []) {
            throw new RuntimeException('invalid_arguments');
        }
        $this->assertSafeValue($arguments);
        try {
            $encoded = json_encode($arguments, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('invalid_arguments');
        }
        if (strlen($encoded) > 32768) {
            throw new RuntimeException('invalid_arguments');
        }

        match ($name) {
            'queryShopList' => $this->assertShopArguments($arguments),
            'searchProductForMcp' => $this->assertSearchArguments($arguments),
            'switchProduct' => $this->assertSwitchArguments($arguments),
            'queryProductDetailInfo' => $this->assertProductDetailArguments($arguments),
        };
    }

    private function assertShopArguments(array $arguments): void
    {
        $this->assertCoordinate($arguments['longitude'] ?? null, -180, 180);
        $this->assertCoordinate($arguments['latitude'] ?? null, -90, 90);
        if (isset($arguments['deptName']) && (! is_string($arguments['deptName']) || mb_strlen($arguments['deptName']) > 100)) {
            throw new RuntimeException('invalid_arguments');
        }
    }

    private function assertSearchArguments(array $arguments): void
    {
        $this->assertPositiveInteger($arguments['deptId'] ?? null);
        $query = $arguments['query'] ?? null;
        if (! is_string($query) || trim($query) === '' || mb_strlen($query) > 200) {
            throw new RuntimeException('invalid_arguments');
        }
    }

    private function assertSwitchArguments(array $arguments): void
    {
        $this->assertPositiveInteger($arguments['deptId'] ?? null);
        $this->assertPositiveInteger($arguments['productId'] ?? null);
        $skuCode = $arguments['skuCode'] ?? null;
        if (! is_string($skuCode) || trim($skuCode) === '' || strlen($skuCode) > 128) {
            throw new RuntimeException('invalid_arguments');
        }
        if (! is_array($arguments['attrOperationParam'] ?? null)) {
            throw new RuntimeException('invalid_arguments');
        }
        $amount = $arguments['amount'] ?? null;
        if (! is_int($amount) || $amount < 1 || $amount > 20) {
            throw new RuntimeException('invalid_arguments');
        }
    }

    private function assertProductDetailArguments(array $arguments): void
    {
        $this->assertPositiveInteger($arguments['deptId'] ?? null);
        $this->assertPositiveInteger($arguments['productId'] ?? null);
    }

    private function assertPositiveInteger(mixed $value): void
    {
        if (! is_int($value) || $value < 1) {
            throw new RuntimeException('invalid_arguments');
        }
    }

    private function assertCoordinate(mixed $value, float $min, float $max): void
    {
        if (! is_int($value) && ! is_float($value)) {
            throw new RuntimeException('invalid_arguments');
        }
        if (! is_finite((float) $value) || $value < $min || $value > $max) {
            throw new RuntimeException('invalid_arguments');
        }
    }

    private function assertSafeValue(mixed $value, int $depth = 0): void
    {
        if ($depth > 6) {
            throw new RuntimeException('invalid_arguments');
        }
        if (is_string($value)) {
            if (strlen($value) > 1000) {
                throw new RuntimeException('invalid_arguments');
            }

            return;
        }
        if (is_array($value)) {
            if (count($value) > 50) {
                throw new RuntimeException('invalid_arguments');
            }
            foreach ($value as $key => $child) {
                if (is_string($key) && strlen($key) > 100) {
                    throw new RuntimeException('invalid_arguments');
                }
                $this->assertSafeValue($child, $depth + 1);
            }

            return;
        }
        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return;
        }

        throw new RuntimeException('invalid_arguments');
    }

    private function validSessionId(string $sessionId): bool
    {
        return strlen($sessionId) <= 512 && preg_match('/^[\x21-\x7E]+$/', $sessionId) === 1;
    }

    private function baseState(string $token): array
    {
        $enabled = (bool) config('geoflow.luckin_mcp.enabled', true);
        $configured = $token !== '';

        return [
            'enabled' => $enabled,
            'configured' => $configured,
            'status' => ! $enabled ? 'disabled' : ($configured ? 'pending' : 'authorization_required'),
            'capabilities' => array_fill_keys(self::PRODUCT_TOOLS, false),
            'protocol_version' => '',
            'checked_at' => '',
        ];
    }

    private function sanitizeState(array $state, array $base): array
    {
        $allowedStatuses = ['disabled', 'authorization_required', 'pending', 'connected', 'partial', 'error'];
        $status = is_string($state['status'] ?? null) && in_array($state['status'], $allowedStatuses, true)
            ? $state['status']
            : 'error';
        $capabilities = [];
        foreach (self::PRODUCT_TOOLS as $tool) {
            $capabilities[$tool] = ($state['capabilities'][$tool] ?? false) === true;
        }
        $protocolVersion = ($state['protocol_version'] ?? '') === self::SUPPORTED_PROTOCOL_VERSION
            ? self::SUPPORTED_PROTOCOL_VERSION
            : '';
        $checkedAt = is_string($state['checked_at'] ?? null) && strlen($state['checked_at']) <= 64
            ? $state['checked_at']
            : '';

        return [
            'enabled' => (bool) $base['enabled'],
            'configured' => (bool) $base['configured'],
            'status' => $status,
            'capabilities' => $capabilities,
            'protocol_version' => $protocolVersion,
            'checked_at' => $checkedAt,
        ];
    }

    private function cacheKey(string $token): string
    {
        $key = (string) config('app.key', '');
        $fingerprint = hash_hmac('sha256', $token, $key !== '' ? $key : 'geoflow-luckin-mcp');

        return 'geoflow:luckin-mcp:state:'.$fingerprint;
    }

    private function endpoint(): string
    {
        return 'https://gwmcp.lkcoffee.com/order/user/mcp';
    }

    private function timeoutSeconds(): int
    {
        return max(1, min(15, (int) config('geoflow.luckin_mcp.timeout_seconds', 5)));
    }

    private function connectTimeoutSeconds(): int
    {
        return max(1, min(10, (int) config('geoflow.luckin_mcp.connect_timeout_seconds', 3)));
    }

    private function maxResponseBytes(): int
    {
        return max(1024, min(2 * 1024 * 1024, (int) config('geoflow.luckin_mcp.max_response_bytes', 1024 * 1024)));
    }

    private function stateTtlSeconds(): int
    {
        return max(360, min(86400, (int) config('geoflow.luckin_mcp.state_ttl_seconds', 900)));
    }
}

final class LuckinMcpSessionExpiredException extends RuntimeException {}
