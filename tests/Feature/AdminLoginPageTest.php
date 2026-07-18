<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_keeps_original_form_and_loads_luckin_brand_assets(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('css/luckin-admin-theme.css?v=', false)
            ->assertSee('images/luckin-coffee-logo.png', false)
            ->assertSee('images/luckin-admin-login-bg.webp', false)
            ->assertSee('luckin-admin-theme luckin-admin-login', false)
            ->assertSee('action="'.route('admin.login.attempt').'"', false)
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false)
            ->assertSee('data-brand-admin-username', false)
            ->assertSee(__('admin.login.brand_username_hint', ['username' => 'luckin_admin']))
            ->assertDontSee('data-brand-admin-password', false)
            ->assertDontSee('luckin-login-story', false);

        $this->assertFileExists(public_path('images/luckin-admin-login-bg.webp'));
    }

    public function test_login_page_can_show_verified_demo_password_when_explicitly_enabled(): void
    {
        config([
            'geoflow.login_demo_password_enabled' => true,
            'geoflow.initial_admin_username' => 'admin',
            'geoflow.initial_admin_password' => 'demo-secret-123',
        ]);

        Admin::query()->create([
            'username' => 'admin',
            'password' => 'demo-secret-123',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
            'last_login' => now(),
        ]);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('placeholder="demo-secret-123"', false)
            ->assertSee('placeholder:text-gray-400', false)
            ->assertDontSee('data-brand-admin-password', false);
    }

    public function test_login_page_hides_demo_password_when_configured_value_is_stale(): void
    {
        config([
            'geoflow.login_demo_password_enabled' => true,
            'geoflow.initial_admin_username' => 'admin',
            'geoflow.initial_admin_password' => 'stale-secret',
        ]);

        Admin::query()->create([
            'username' => 'admin',
            'password' => 'changed-secret-123',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee('data-brand-admin-password', false)
            ->assertSee('placeholder="'.__('admin.login.password_placeholder').'"', false)
            ->assertDontSee('stale-secret');
    }

    public function test_luckin_username_alias_authenticates_the_configured_default_admin(): void
    {
        config([
            'geoflow.initial_admin_username' => 'admin',
            'luckin.admin_username' => 'luckin_admin',
        ]);

        $admin = Admin::query()->create([
            'username' => 'admin',
            'password' => 'secret-123',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->post(route('admin.login.attempt'), [
            'username' => 'luckin_admin',
            'password' => 'secret-123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_luckin_alias_keeps_resolving_to_default_admin_if_a_legacy_collision_exists(): void
    {
        config([
            'geoflow.initial_admin_username' => 'admin',
            'luckin.admin_username' => 'luckin_admin',
        ]);

        $admin = Admin::query()->create([
            'username' => 'admin',
            'password' => 'canonical-secret-123',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        Admin::query()->create([
            'username' => 'luckin_admin',
            'password' => 'collision-secret-123',
            'email' => 'collision@example.com',
            'display_name' => 'Legacy Collision',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->post(route('admin.login.attempt'), [
            'username' => 'luckin_admin',
            'password' => 'canonical-secret-123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_failed_alias_logins_lock_the_real_default_admin_account(): void
    {
        config([
            'geoflow.initial_admin_username' => 'admin',
            'luckin.admin_username' => 'luckin_admin',
        ]);

        $admin = Admin::query()->create([
            'username' => 'admin',
            'password' => 'secret-123',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.attempt'), [
                'username' => 'luckin_admin',
                'password' => 'wrong-secret',
            ]);
        }

        $this->assertSame('locked', $admin->fresh()?->status);
    }

    public function test_login_page_shows_initial_admin_hint_when_default_credentials_are_still_valid(): void
    {
        config([
            'geoflow.initial_admin_hint_enabled' => true,
            'geoflow.initial_admin_username' => 'admin',
            'geoflow.initial_admin_password' => 'password',
        ]);

        Admin::query()->create([
            'username' => 'admin',
            'password' => 'password',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
            'last_login' => null,
        ]);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee(__('admin.login.first_login_hint_title'))
            ->assertSee(__('admin.login.first_login_security'))
            ->assertSee('admin')
            ->assertSee('password')
            ->assertSee('id="initial-admin-hint"', false)
            ->assertSee('localStorage.setItem', false);
    }

    public function test_login_page_hides_initial_admin_hint_after_first_successful_login(): void
    {
        config([
            'geoflow.initial_admin_hint_enabled' => true,
            'geoflow.initial_admin_username' => 'admin',
            'geoflow.initial_admin_password' => 'password',
        ]);

        Admin::query()->create([
            'username' => 'admin',
            'password' => 'password',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
            'last_login' => now(),
        ]);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee(__('admin.login.first_login_hint_title'))
            ->assertDontSee('id="initial-admin-hint"', false);
    }

    public function test_login_page_hides_initial_admin_hint_when_configured_password_no_longer_matches(): void
    {
        config([
            'geoflow.initial_admin_hint_enabled' => true,
            'geoflow.initial_admin_username' => 'admin',
            'geoflow.initial_admin_password' => 'password',
        ]);

        Admin::query()->create([
            'username' => 'admin',
            'password' => 'changed-secret-123',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
            'last_login' => null,
        ]);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee(__('admin.login.first_login_hint_title'))
            ->assertDontSee('id="initial-admin-hint"', false);
    }

    public function test_login_page_points_to_init_log_when_production_password_was_generated(): void
    {
        config([
            'app.env' => 'production',
            'geoflow.initial_admin_hint_enabled' => true,
            'geoflow.initial_admin_username' => 'admin',
            'geoflow.initial_admin_password' => '',
        ]);

        Admin::query()->create([
            'username' => 'admin',
            'password' => 'random-generated-secret',
            'email' => 'admin@example.com',
            'display_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
            'last_login' => null,
        ]);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee(__('admin.login.first_login_hint_title'))
            ->assertSee(__('admin.login.first_login_password_from_log'))
            ->assertSee('geoflow-init')
            ->assertDontSee('random-generated-secret');
    }
}
