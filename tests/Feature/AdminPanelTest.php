<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_staff_can_list_records_including_enum_columns(): void
    {
        $admin = User::factory()->administrator()->create();
        Record::factory()->create(['title' => 'Reasonable Doubt']);

        $this->actingAs($admin)
            ->get('/admin/records')
            ->assertOk()
            ->assertSee('Reasonable Doubt'); // table body (with enum columns) renders
    }

    public function test_staff_can_manage_users_and_settings(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }

    public function test_staff_can_create_an_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'first_name' => 'New',
                'last_name' => 'Admin',
                'username' => 'newadmin',
                'email' => 'newadmin@example.com',
                'password' => 'password',
                'role' => UserRole::Administrator->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // role must actually persist (regression: role was not fillable)
        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => UserRole::Administrator->value,
        ]);
    }
}
