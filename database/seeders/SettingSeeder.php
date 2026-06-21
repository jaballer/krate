<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // [id, key, value, type, category, description, created_at, updated_at]
        $rows = [
            [1, 'dark_mode', '0', 'boolean', 'general', 'Enable dark mode', '2025-01-31 03:41:55', '2025-02-01 07:40:50'],
            [2, 'logo_url', 'https://example.com/wp-content/uploads/2025/01/916-Maketing-Logo.svg', 'string', 'branding', 'Logo Url', '2025-01-31 04:06:32', '2025-01-31 05:24:22'],
            [3, 'audio_source', 'https://example.com/wp-content/uploads/2024/06/Cinematic-Adventures.mp3', 'string', 'audio', 'Cinematix Adventures', '2025-01-31 04:41:11', '2025-01-31 05:43:02'],
            [27, 'site_name', 'Krate', 'string', 'site', 'Site Name', '2025-01-31 05:26:05', '2025-02-01 02:05:14'],
            [28, 'site_url', 'https://example.com', 'string', 'site', 'Site URL', '2025-01-31 05:27:10', '2025-02-01 02:39:13'],
            [29, 'admin_email', 'admin@example.com', 'string', 'admin', 'Admin email', '2025-01-31 05:27:29', '2025-01-31 23:51:32'],
            [30, 'site_tagline', 'Simple. Scalable. Smart Content Management.', 'string', 'site', 'Site Tagline', '2025-01-31 05:28:53', '2025-02-01 02:39:05'],
            [31, 'site_description', 'A Very Cool Descripition', 'string', 'site', 'Agreed, in deed', '2025-01-31 05:29:20', '2025-02-01 02:05:26'],
            [32, 'site_author', 'Admin User', 'string', 'site', '', '2025-01-31 05:29:38', '2025-02-01 02:04:32'],
            [38, 'audio_player_on', '0', 'boolean', 'audio', 'Audio player on by default', '2025-01-31 16:39:12', '2025-02-01 12:21:23'],
            [52, 'postmark_from_email', 'no-reply@example.com', 'string', 'postmark', 'Postmark Sender Signature', '2025-01-31 23:35:56', '2025-01-31 23:35:56'],
            [60, 'social_facebook', 'https://facebook.com', 'string', 'social', '', '2025-02-01 00:39:45', '2025-02-01 02:01:42'],
            [61, 'social_linkedin', 'https://linkedin.com', 'string', 'social', '', '2025-02-01 00:40:30', '2025-02-01 02:01:57'],
            [62, 'social_instagram', 'https://instagram.com', 'string', 'social', '', '2025-02-01 00:41:01', '2025-02-01 02:01:50'],
            [65, 'audio_default_on_load', 'https://example.com/wp-content/uploads/2025/01/THATS-WHATS-UP.mp3', 'string', 'audio', 'Audio to play on load', '2025-02-01 02:01:13', '2025-02-01 02:07:51'],
            [75, 'admin_name', 'Admin User', 'string', 'admin', 'Admin Name', '2025-02-01 02:07:14', '2025-02-01 02:07:14'],
        ];

        foreach ($rows as [$id, $key, $value, $type, $category, $description, $createdAt, $updatedAt]) {
            DB::table('settings')->insert([
                'id' => $id,
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type,
                'category' => $category,
                'description' => $description,
                'is_private' => false,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}
