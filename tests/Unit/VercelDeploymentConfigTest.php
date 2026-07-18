<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

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
}
