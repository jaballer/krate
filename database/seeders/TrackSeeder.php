<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // record_id values reference VinylRecordSeeder: 6 = Wu-Tang "Bring Da
        // Ruckus", 3 = "The Miseducation of Lauryn Hill". The third track is left
        // standalone (record_id null) to exercise both cases.
        $rows = [
            [
                'record_id' => 6, 'side' => 'A', 'position' => 1,
                'title' => 'Bring Da Ruckus', 'artist' => 'Wu-Tang Clan', 'album' => 'Enter the Wu-Tang (36 Chambers)',
                'genre' => 'Hip Hop', 'release_year' => 1993, 'duration_seconds' => 251, 'bpm' => 88,
                'audio_file_url' => 'https://example.com/wp-content/uploads/2024/09/Wu-Tang-Clan-Bring-Da-Ruckus.mp3',
                'notes' => null,
            ],
            [
                'record_id' => 3, 'side' => 'A', 'position' => 5,
                'title' => 'Doo Wop (That Thing)', 'artist' => 'Lauryn Hill', 'album' => 'The Miseducation of Lauryn Hill',
                'genre' => 'Hip Hop', 'release_year' => 1998, 'duration_seconds' => 320, 'bpm' => 100,
                'audio_file_url' => null,
                'notes' => null,
            ],
            [
                'record_id' => null, 'side' => null, 'position' => null,
                'title' => 'Cinematic Adventure', 'artist' => 'Sample Artist', 'album' => null,
                'genre' => 'Hip Hop', 'release_year' => 2024, 'duration_seconds' => 198, 'bpm' => 98,
                'audio_file_url' => 'https://example.com/wp-content/uploads/2024/06/Cinematic-Adventures.mp3',
                'notes' => 'Instrumental sample track.',
            ],
        ];

        foreach ($rows as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('tracks')->insert($rows);
    }
}
