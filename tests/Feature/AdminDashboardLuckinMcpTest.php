<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\GeoFlow\LuckinMcpCredentialStore;
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
            ->assertSee('luckin-mcp-source-strip', false)
            ->assertSee('luckin-mcp-brand-visual-dashboard', false)
            ->assertDontSee('width="360" height="100"', false)
            ->assertSee(__('luckin_mcp.heading'))
            ->assertSee(__('luckin_mcp.status.authorization_required'))
            ->assertSee(route('admin.knowledge-bases.luckin-mcp.index'), false)
            ->assertSee('作者：任济坤、铁晋鸾')
            ->assertSee('微信：rjk-2006')
            ->assertDontSee('test-token-never-render');
        Http::assertNothingSent();
    }

    public function test_public_homepage_does_not_include_admin_mcp_panel(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('luckin-mcp-source-strip', false)
            ->assertDontSee(route('admin.knowledge-bases.luckin-mcp.index'), false);
    }

    public function test_dashboard_with_configured_token_and_empty_cache_still_makes_no_external_request(): void
    {
        Http::fake();

        $admin = Admin::query()->create([
            'username' => 'luckin_mcp_pending_admin',
            'password' => 'secret-123',
            'email' => 'luckin-mcp-pending@example.com',
            'display_name' => 'Luckin MCP Pending Admin',
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        app(LuckinMcpCredentialStore::class)->put((int) $admin->id, 'configured-but-not-probed');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('luckin_mcp.status.pending'))
            ->assertDontSee('configured-but-not-probed');

        Http::assertNothingSent();
    }

    public function test_check_command_with_missing_token_emits_sanitized_state_without_network(): void
    {
        Http::fake();

        $this->artisan('geoflow:luckin-mcp:check', ['--json' => true])
            ->expectsOutputToContain('"admins":[]')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_check_command_fails_when_a_configured_key_is_revoked(): void
    {
        $admin = Admin::query()->create([
            'username' => 'luckin_mcp_revoked_admin',
            'password' => 'secret-123',
            'email' => 'luckin-mcp-revoked@example.com',
            'display_name' => 'Luckin MCP Revoked Admin',
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        app(LuckinMcpCredentialStore::class)->put((int) $admin->id, 'revoked-token-never-render');
        Http::fake(['*' => Http::response('', 401)]);

        $this->artisan('geoflow:luckin-mcp:check', ['--admin' => [(int) $admin->id], '--json' => true])
            ->expectsOutputToContain('"status":"authorization_required"')
            ->assertFailed();
    }
}
