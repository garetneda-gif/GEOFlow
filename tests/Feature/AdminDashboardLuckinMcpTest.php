<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminDashboardLuckinMcpTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_explains_pending_authorization_without_external_request(): void
    {
        config([
            'geoflow.luckin_mcp.enabled' => true,
            'geoflow.luckin_mcp.token' => '',
        ]);
        Http::fake();

        $admin = Admin::query()->create([
            'username' => 'luckin_mcp_admin',
            'password' => 'secret-123',
            'email' => 'luckin-mcp@example.com',
            'display_name' => 'Luckin MCP Admin',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('luckin-mcp-panel', false)
            ->assertSee(__('admin.dashboard.luckin_mcp.title'))
            ->assertSee(__('admin.dashboard.luckin_mcp.authorization_required_notice'))
            ->assertSee(__('admin.dashboard.luckin_mcp.scope'))
            ->assertSee('https://open.lkcoffee.com/mcp', false)
            ->assertSee('作者：任济坤')
            ->assertSee('微信：rjk-2006')
            ->assertDontSee('test-token-never-render');
        Http::assertNothingSent();
    }

    public function test_public_homepage_does_not_include_admin_mcp_panel(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('luckin-mcp-panel', false)
            ->assertDontSee(__('admin.dashboard.luckin_mcp.title'));
    }

    public function test_dashboard_with_configured_token_and_empty_cache_still_makes_no_external_request(): void
    {
        config(['geoflow.luckin_mcp.token' => 'configured-but-not-probed']);
        Http::fake();

        $admin = Admin::query()->create([
            'username' => 'luckin_mcp_pending_admin',
            'password' => 'secret-123',
            'email' => 'luckin-mcp-pending@example.com',
            'display_name' => 'Luckin MCP Pending Admin',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('admin.dashboard.luckin_mcp.pending_notice'))
            ->assertDontSee('configured-but-not-probed');

        Http::assertNothingSent();
    }

    public function test_check_command_with_missing_token_emits_sanitized_state_without_network(): void
    {
        config(['geoflow.luckin_mcp.token' => '']);
        Http::fake();

        $this->artisan('geoflow:luckin-mcp:check', ['--json' => true])
            ->expectsOutputToContain('"status":"authorization_required"')
            ->assertSuccessful();

        Http::assertNothingSent();
    }
}
