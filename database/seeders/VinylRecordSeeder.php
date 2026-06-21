<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VinylRecordSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'id' => 1, 'title' => 'The Jump Off Vol.1', 'artist' => 'The Perfectionist / Realz',
                'genre' => 'Hip Hop', 'release_year' => 2007, 'label' => 'Independent', 'catalog_number' => '00000',
                'format' => '12"', 'speed' => '33 1/3 RPM', 'condition' => 'Mint',
                'purchase_date' => '2024-09-01', 'purchase_price' => 30.00, 'notes' => 'These are notes',
                'front_image' => null, 'back_image' => null,
                'purchase_link' => 'https://example.com/the-jump-off-vol-1/',
                'audio_file_url' => null, 'bpm' => 0,
                'created_at' => '2024-09-07 06:42:50', 'updated_at' => '2025-02-01 13:40:16',
            ],
            [
                'id' => 2, 'title' => 'The Elmatic Instrumentals', 'artist' => 'Will Sessions',
                'genre' => 'Rap', 'release_year' => 2011, 'label' => 'Fat Beats Records', 'catalog_number' => 'FB5149',
                'format' => '12"', 'speed' => '33 1/3 RPM', 'condition' => 'Near Mint',
                'purchase_date' => '2016-03-10', 'purchase_price' => 20.00,
                'notes' => 'Elmatic had been initially planned and announced in 2008 as a tribute to the historic 1994 album Illmatic by Nas, but it wasnâ€™t until early 2011 that Elzhi and his manager Jae Barber agreed that it would be best to recreate all of the beats from scratch--and there was clearly nobody who could do it better than producer Sam Beaubien and Will Sessions. Since itâ€™s widely considered to be the holy grail of hip-hop recordings, the prospect of duplicating the music from Illmatic was daunting. By using the original sample sources of the albumâ€™s tracks as a foundation, producer Sam Beaubien and the band members managed to recreate both the sound and the mood of the classic album with stunning precision on Elmatic, exceeding lofty expectations set by loyal fans and skeptical critics alike.',
                'front_image' => null, 'back_image' => null,
                'purchase_link' => 'https://willsessions.bandcamp.com/album/the-elmatic-instrumentals',
                'audio_file_url' => null, 'bpm' => null,
                'created_at' => '2024-09-07 22:40:08', 'updated_at' => '2025-01-30 21:40:52',
            ],
            [
                'id' => 3, 'title' => 'The Miseducation of Lauryn Hill', 'artist' => 'Lauryn Hill',
                'genre' => 'Hip Hop', 'release_year' => 2000, 'label' => 'Ruffhouse Records / Columbia', 'catalog_number' => 'MOVLP060',
                'format' => '12"', 'speed' => '33 1/3 RPM', 'condition' => 'Very Good',
                'purchase_date' => '2017-09-11', 'purchase_price' => 24.00, 'notes' => null,
                'front_image' => null, 'back_image' => null,
                'purchase_link' => 'https://tinyurl.com/tmeolh',
                'audio_file_url' => null, 'bpm' => 0,
                'created_at' => '2024-09-07 22:49:05', 'updated_at' => '2025-01-30 21:40:52',
            ],
            [
                'id' => 4, 'title' => 'Cinematic Adventure', 'artist' => 'Sample Artist',
                'genre' => 'Hip Hop', 'release_year' => 2024, 'label' => 'Independent', 'catalog_number' => '123456',
                'format' => '12"', 'speed' => '33 1/3 RPM', 'condition' => 'Mint',
                'purchase_date' => '2024-06-04', 'purchase_price' => 19.99,
                'notes' => 'Laculis et metus. Duis in magna laoreet, varius nisl eget, rutrum velit. Curabitur volutpat turpis orci, et ullamcorper elit ultrices sed.',
                'front_image' => null, 'back_image' => null,
                'purchase_link' => 'https://example.com/the-jump-off-vol-1/',
                'audio_file_url' => 'https://example.com/wp-content/uploads/2024/06/Cinematic-Adventures.mp3', 'bpm' => 98,
                'created_at' => '2024-09-08 06:29:06', 'updated_at' => '2025-02-01 13:27:54',
            ],
            [
                'id' => 6, 'title' => 'Bring Da Ruckus', 'artist' => 'Wu-Tang Clan',
                'genre' => 'Hip Hop', 'release_year' => 1994, 'label' => 'Wu Tang', 'catalog_number' => '0000',
                'format' => '12"', 'speed' => '33 1/3 RPM', 'condition' => 'Mint',
                'purchase_date' => '2024-09-03', 'purchase_price' => 12.00, 'notes' => null,
                'front_image' => null, 'back_image' => null,
                'purchase_link' => 'https://store.thewutangclan.com/products/bring-da-ruckus-t-shirt',
                'audio_file_url' => 'https://example.com/wp-content/uploads/2024/09/Wu-Tang-Clan-Bring-Da-Ruckus.mp3', 'bpm' => 120,
                'created_at' => '2024-09-12 03:15:08', 'updated_at' => '2025-02-01 15:16:23',
            ],
        ];

        DB::table('vinyl_records')->insert($rows);
    }
}
