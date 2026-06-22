<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\AdminLoginAlert;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies the configured admin recipients when a staff member signs in.
 *
 * Auto-discovered from app/Listeners (Laravel registers any handle()/__invoke()
 * method by its type-hinted event). The Login event fires for both surfaces —
 * Breeze (public) and Filament (staff) share the `web` guard — so we filter to
 * staff sign-ins here; public member logins are intentionally ignored to avoid
 * noise, matching the legacy admin-only behaviour.
 */
class SendAdminLoginAlert
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Only staff (Administrator/Manager) sign-ins are alert-worthy; public
        // member logins are ignored to avoid noise, matching the legacy
        // admin-only behaviour.
        if (! $user instanceof User || ! $user->role->isStaff()) {
            return;
        }

        /** @var array<int, string> $recipients */
        $recipients = (array) config('krate.admin_notification_emails', []);

        if ($recipients === []) {
            return;
        }

        try {
            Notification::route('mail', $recipients)
                ->notify(new AdminLoginAlert($user, request()->ip()));
        } catch (\Throwable $e) {
            // A mail outage must never break authentication: the user is already
            // signed in by the time this event fires. Log and move on.
            report($e);
        }
    }
}
