<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the configured admin recipients when a staff account signs in.
 * Ports the legacy UserManager login alert (which emailed
 * ADMIN_NOTIFICATION_EMAILS on every admin login) to a Laravel notification.
 */
class AdminLoginAlert extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly ?string $ipAddress,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $site = (string) config('krate.site.name', 'Krate');

        return (new MailMessage)
            ->subject("[{$site}] Staff sign-in alert")
            ->greeting('Staff sign-in detected')
            ->line("A staff account just signed in to {$site}.")
            ->line('Account: '.$this->user->name.' <'.$this->user->email.'>')
            ->line('Role: '.$this->user->role->value)
            ->line('IP address: '.($this->ipAddress ?? 'unknown'))
            ->line('Time: '.now()->toDayDateTimeString().' (UTC)')
            ->line('If this sign-in was not expected, review the account immediately.');
    }
}
