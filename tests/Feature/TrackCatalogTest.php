<?php

namespace Tests\Feature;

use App\Enums\TrackSide;
use App\Enums\UserRole;
use App\Filament\Resources\Tracks\TrackResource;
use App\Models\Record;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public track library (read-only). Mirrors CatalogTest; every write path
 * lives behind staff auth (see TrackResourceTest).
 */
class TrackCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_tracks(): void
    {
        Track::factory()->create(['title' => 'C.R.E.A.M.', 'artist' => 'Wu-Tang Clan']);

        $this->get('/tracks')
            ->assertOk()
            ->assertSee('C.R.E.A.M.')
            ->assertSee('Wu-Tang Clan');
    }

    public function test_search_filters_tracks_by_title_artist_and_album(): void
    {
        Track::factory()->create(['title' => 'Shook Ones', 'artist' => 'Mobb Deep', 'album' => 'The Infamous']);
        Track::factory()->create(['title' => 'Elevators', 'artist' => 'Outkast', 'album' => 'ATLiens']);

        $this->get('/tracks?search=Infamous')
            ->assertOk()
            ->assertSee('Shook Ones')
            ->assertDontSee('Elevators');
    }

    public function test_array_search_parameter_is_ignored(): void
    {
        Track::factory()->create(['title' => 'Some Track']);

        $this->get('/tracks?search[]=foo')
            ->assertOk()
            ->assertSee('Some Track');
    }

    public function test_track_detail_is_shown(): void
    {
        $track = Track::factory()->create(['title' => 'Liquid Swords', 'artist' => 'GZA']);

        $this->get(route('tracks.show', $track))
            ->assertOk()
            ->assertSee('Liquid Swords')
            ->assertSee('GZA');
    }

    public function test_track_title_is_escaped_in_the_page_title(): void
    {
        $track = Track::factory()->create(['title' => 'Evil</title><script>alert(1)</script>']);

        $this->get(route('tracks.show', $track))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_duration_is_shown_as_minutes_and_seconds(): void
    {
        Track::factory()->create(['title' => 'Timed', 'duration_seconds' => 251]);

        $this->get('/tracks')->assertOk()->assertSee('4:11');
    }

    public function test_web_audio_url_renders_a_player(): void
    {
        $track = Track::factory()->create(['audio_file_url' => 'https://example.com/track.mp3']);

        $this->get(route('tracks.show', $track))
            ->assertOk()
            ->assertSee('Listen')
            ->assertSee('https://example.com/track.mp3', false);
    }

    public function test_non_web_audio_url_is_not_rendered(): void
    {
        $track = Track::factory()->create(['audio_file_url' => 'javascript:alert(1)']);

        $this->get(route('tracks.show', $track))
            ->assertOk()
            ->assertDontSee('javascript:alert(1)', false)
            ->assertDontSee('Listen');
    }

    public function test_staff_see_an_edit_link_to_the_filament_track_editor(): void
    {
        $track = Track::factory()->create();
        $editUrl = TrackResource::getUrl('edit', ['record' => $track]);

        $staff = [
            User::factory()->administrator()->create(),
            User::factory()->create(['role' => UserRole::Manager]),
        ];

        foreach ($staff as $user) {
            $this->actingAs($user)
                ->get(route('tracks.show', $track))
                ->assertOk()
                ->assertSee('Edit track')
                ->assertSee($editUrl, false);
        }
    }

    public function test_non_staff_and_guests_do_not_see_an_edit_link(): void
    {
        $track = Track::factory()->create();

        $this->get(route('tracks.show', $track))->assertOk()->assertDontSee('Edit track');

        $member = User::factory()->create(); // Standard User
        $this->actingAs($member)->get(route('tracks.show', $track))->assertOk()->assertDontSee('Edit track');
    }

    public function test_sort_by_title_orders_alphabetically(): void
    {
        Track::factory()->create(['title' => 'Zulu Nation']);
        Track::factory()->create(['title' => 'Award Tour']);

        $this->get('/tracks?sort=title')
            ->assertOk()
            ->assertSeeInOrder(['Award Tour', 'Zulu Nation']);
    }

    public function test_invalid_sort_value_falls_back_to_default(): void
    {
        Track::factory()->create(['title' => 'Only Track']);

        $this->get('/tracks?sort=bogus')->assertOk()->assertSee('Only Track');
    }

    public function test_tracks_appear_in_the_site_navigation(): void
    {
        $this->get('/')->assertOk()->assertSee(route('tracks.index'), false);
    }

    public function test_a_linked_track_shows_the_record_title_not_a_stale_album(): void
    {
        $record = Record::factory()->create(['title' => 'Enter the Wu-Tang (36 Chambers)']);
        $track = Track::factory()->forRecord($record, TrackSide::A, 1)->create([
            'title' => 'Bring Da Ruckus',
            'album' => 'Stale Album Name',
        ]);

        // Both public surfaces prefer the record title; the stale album never shows.
        $this->get('/tracks')
            ->assertOk()
            ->assertSee('Enter the Wu-Tang (36 Chambers)')
            ->assertDontSee('Stale Album Name');

        $this->get(route('tracks.show', $track))
            ->assertOk()
            ->assertSee('Enter the Wu-Tang (36 Chambers)')
            ->assertDontSee('Stale Album Name');
    }
}
