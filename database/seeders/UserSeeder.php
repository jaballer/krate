<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // All legacy rows share this bcrypt hash; preserved verbatim (no re-hash).
        $hash = '$2y$10$zk9c62bpSql1.TM3UiMsc.A/LhByu.Fxjh9XnIpKowM69Nq5EyaF6';

        $rows = [
            [1, 'Admin', 'User', 'admin', 'admin@example.com', 'Administrator'],
            [2, 'Clifford', 'Smith', 'rafael', 'fake.email4@example.com', 'Administrator'],
            [3, 'John', 'Doe', 'johndoe', 'john.doe@example.com', 'Manager'],
            [4, 'Eloney', 'Musk', 'elon', 'elon.musk@example.com', 'Manager'],
            [5, 'Brew', 'Bro', 'brewbro', 'brew.bro@example.com', 'Standard User'],
            [6, 'Jason', 'Wilde', 'wilde', 'j.wilde@example.com', 'Guest'],
        ];

        foreach ($rows as [$id, $first, $last, $username, $email, $role]) {
            DB::table('users')->insert([
                'id' => $id,
                'first_name' => $first,
                'last_name' => $last,
                'username' => $username,
                'email' => $email,
                'email_verified_at' => $now,
                'password' => $hash,
                'role' => $role,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
