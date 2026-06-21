<?php

return [

    // App-level identity + notification settings, ported from the legacy .env.
    // Database-backed settings (logo, audio source, dark mode) live in the
    // `settings` table and are handled separately (Setting model, see epic).

    'site' => [
        'owner' => env('SITE_OWNER'),
        'name' => env('SITE_NAME', 'Krate'),
        'tagline' => env('SITE_TAGLINE'),
        'description' => env('SITE_DESCRIPTION'),
        'author' => env('SITE_AUTHOR'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_API_TOKEN'),
    ],

    'admin_notification_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_NOTIFICATION_EMAILS', ''))
    ))),

];
