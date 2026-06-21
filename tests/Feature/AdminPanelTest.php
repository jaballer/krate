<?php

namespace Tests\Feature;

use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_staff_can_access_the_panel(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_non_staff_members_are_forbidden(): void
    {
        $member = User::factory()->create(); // defaults to Standard User

        $this->actingAs($member)->get('/admin')->assertForbidden();
    }

    public function test_staff_can_list_records(): void
    {
        $admin = User::factory()->administrator()->create();
        Record::factory()->count(3)->create();

        $this->actingAs($admin)->get('/admin/records')->assertOk();
    }

    public function test_staff_can_manage_users_and_settings(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }
}
