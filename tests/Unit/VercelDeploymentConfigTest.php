<?php

namespace Tests\Unit;

use Monolog\Formatter\LineFormatter;
use PDO;
use Tests\TestCase;

class VercelDeploymentConfigTest extends TestCase
{
    public function test_vercel_uses_php_83_runtime_and_routes_assets_before_laravel(): void
    {
        $config = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2).'/vercel.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('public', $config['outputDirectory']);
        $this->assertSame('^/$', $config['routes'][0]['src']);
        $this->assertSame('/geo_admin', $config['routes'][0]['headers']['Location']);
        $this->assertSame(307, $config['routes'][0]['status']);
        $this->assertSame('vercel-php@0.7.4', $config['functions']['api/index.php']['runtime']);
        $this->assertSame('/public/$1/$2', $config['routes'][1]['dest']);
        $this->assertSame('/api/index.php', $config['routes'][array_key_last($config['routes'])]['dest']);
    }

    public function test_vercel_entrypoint_boots_the_existing_public_front_controller(): void
    {
        $entrypoint = (string) file_get_contents(dirname(__DIR__, 2).'/api/index.php');
        $bootstrap = (string) file_get_contents(dirname(__DIR__, 2).'/bootstrap/app.php');

        $this->assertStringContainsString("dirname(__DIR__).'/public/index.php'", $entrypoint);
        $this->assertStringContainsString("'VIEW_COMPILED_PATH' => '/tmp/views'", $entrypoint);
        $this->assertStringContainsString("env('TRUSTED_PROXIES', '')", $bootstrap);
        $this->assertStringContainsString('$middleware->trustProxies', $bootstrap);
    }

    public function test_production_nginx_redirects_the_root_to_the_admin_console(): void
    {
        $nginx = (string) file_get_contents(dirname(__DIR__, 2).'/docker/nginx/default.conf');

        $this->assertStringContainsString("location = / {\n        return 307 /geo_admin;\n    }", $nginx);
    }

    public function test_supabase_transaction_pooler_disables_named_prepared_statements_without_emulation(): void
    {
        $originalPort = getenv('DB_PORT');
        $originalDisablePrepares = getenv('DB_PGSQL_DISABLE_PREPARES');
        $originalEmulatePrepares = getenv('DB_PGSQL_EMULATE_PREPARES');

        try {
            putenv('DB_PORT=6543');
            putenv('DB_PGSQL_DISABLE_PREPARES');
            putenv('DB_PGSQL_EMULATE_PREPARES=false');

            $config = require dirname(__DIR__, 2).'/config/database.php';
            $options = $config['connections']['pgsql']['options'];

            $this->assertTrue($options[PDO::PGSQL_ATTR_DISABLE_PREPARES]);
            $this->assertArrayNotHasKey(PDO::ATTR_EMULATE_PREPARES, $options);
        } finally {
            $this->restoreEnvironmentVariable('DB_PORT', $originalPort);
            $this->restoreEnvironmentVariable('DB_PGSQL_DISABLE_PREPARES', $originalDisablePrepares);
            $this->restoreEnvironmentVariable('DB_PGSQL_EMULATE_PREPARES', $originalEmulatePrepares);
        }
    }

    public function test_stderr_logging_keeps_exception_messages_without_oversized_stack_traces(): void
    {
        $originalFormatter = getenv('LOG_STDERR_FORMATTER');
        $originalStacktraces = getenv('LOG_STDERR_STACKTRACES');

        try {
            putenv('LOG_STDERR_FORMATTER');
            putenv('LOG_STDERR_STACKTRACES=false');

            $config = require dirname(__DIR__, 2).'/config/logging.php';
            $stderr = $config['channels']['stderr'];

            $this->assertSame(LineFormatter::class, $stderr['formatter']);
            $this->assertFalse($stderr['formatter_with']['includeStacktraces']);
        } finally {
            $this->restoreEnvironmentVariable('LOG_STDERR_FORMATTER', $originalFormatter);
            $this->restoreEnvironmentVariable('LOG_STDERR_STACKTRACES', $originalStacktraces);
        }
    }

    private function restoreEnvironmentVariable(string $key, string|false $value): void
    {
        putenv($value === false ? $key : $key.'='.$value);
    }
}
