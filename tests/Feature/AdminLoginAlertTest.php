<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminLoginAlert;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminLoginAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['krate.admin_notification_emails' => ['ops@example.com', 'security@example.com']]);
    }

    public function test_staff_login_notifies_configured_admins(): void
    {
        Notification::fake();
        $admin = User::factory()->administrator()->create();

        event(new Login('web', $admin, false));

        Notification::assertSentOnDemand(
            AdminLoginAlert::class,
            function (AdminLoginAlert $notification, array $channels, AnonymousNotifiable $notifiable) use ($admin): bool {
                return $notification->user->is($admin)
                    && in_array('mail', $channels, true)
                    && $notifiable->routes['mail'] === ['ops@example.com', 'security@example.com'];
            }
        );
    }

    public function test_standard_member_login_sends_no_alert(): void
    {
        Notification::fake();
        $member = User::factory()->create(); // defaults to Standard User

        event(new Login('web', $member, false));

        Notification::assertNothingSent();
    }

    public function test_no_alert_when_no_recipients_configured(): void
    {
        config(['krate.admin_notification_emails' => []]);
        Notification::fake();
        $admin = User::factory()->administrator()->create();

        event(new Login('web', $admin, false));

        Notification::assertNothingSent();
    }

    public function test_member_registration_does_not_trigger_alert(): void
    {
        Notification::fake();

        // Self-registration defaults a user to the Standard User role, so the
        // registration auto-login must not fire a staff alert (and must not
        // error on the freshly-created, pre-hydration model).
        $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticated();
        Notification::assertNothingSent();
    }

    public function test_real_staff_login_through_breeze_triggers_alert(): void
    {
        Notification::fake();
        $admin = User::factory()->administrator()->create(); // factory password is "password"

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($admin);
        Notification::assertSentOnDemand(AdminLoginAlert::class);
    }
}
