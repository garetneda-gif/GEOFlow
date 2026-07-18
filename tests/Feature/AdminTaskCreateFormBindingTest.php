<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTaskCreateFormBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_validation_binds_to_task_form_instead_of_header_logout_form(): void
    {
        $admin = Admin::query()->create([
            'username' => 'task_form_binding_admin',
            'password' => 'secret-123',
            'email' => 'task-form-binding@example.com',
            'display_name' => 'Task Form Binding Admin',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.tasks.create'))
            ->assertOk()
            ->assertSee("document.querySelector('[data-task-form-shell] form')", false)
            ->assertDontSee("document.querySelector('form')", false);
    }
}
