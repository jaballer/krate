<?php

namespace Tests\Feature;

use App\Filament\Resources\Tracks\Pages\CreateTrack;
use App\Filament\Resources\Tracks\Pages\EditTrack;
use App\Models\Track;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Track CRUD through the Filament admin panel. Tracks are a standalone,
 * admin-only entity (no public catalog surface); every write path lives
 * behind staff auth, mirroring RecordResourceTest.
 */
class TrackResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_reach_the_track_create_page(): void
    {
        $this->get('/admin/tracks/create')->assertRedirect('/admin/login');
    }

    public function test_non_staff_cannot_reach_the_track_create_page(): void
    {
        $member = User::factory()->create(); // defaults to Standard User

        $this->actingAs($member)->get('/admin/tracks/create')->assertForbidden();
    }

    public function test_staff_can_create_a_track(): void
    {
        $admin = User::factory()->administrator()->create();

        Livewire::actingAs($admin)
            ->test(CreateTrack::class)
            ->fillForm([
                'title' => 'C.R.E.A.M.',
                'artist' => 'Wu-Tang Clan',
                'bpm' => 90,
                'duration_seconds' => 252,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tracks', [
            'title' => 'C.R.E.A.M.',
            'artist' => 'Wu-Tang Clan',
            'bpm' => 90,
            'duration_seconds' => 252,
        ]);
    }

    public function test_creating_a_track_requires_title_and_artist(): void
    {
        $admin = User::factory()->administrator()->create();

        Livewire::actingAs($admin)
            ->test(CreateTrack::class)
            ->fillForm([
                'title' => null,
                'artist' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'artist' => 'required']);

        $this->assertDatabaseCount('tracks', 0);
    }

    public function test_staff_can_edit_a_track(): void
    {
        $admin = User::factory()->administrator()->create();
        $track = Track::factory()->create(['title' => 'Old Title']);

        Livewire::actingAs($admin)
            ->test(EditTrack::class, ['record' => $track->getRouteKey()])
            ->fillForm(['title' => 'New Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tracks', [
            'id' => $track->id,
            'title' => 'New Title',
        ]);
    }

    public function test_staff_can_delete_a_track(): void
    {
        $admin = User::factory()->administrator()->create();
        $track = Track::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditTrack::class, ['record' => $track->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($track);
    }

    public function test_edit_page_links_to_the_public_track_page(): void
    {
        $admin = User::factory()->administrator()->create();
        $track = Track::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditTrack::class, ['record' => $track->getRouteKey()])
            ->assertActionExists('viewOnSite');

        // The action renders as a real link to the public detail page.
        $this->actingAs($admin)
            ->get("/admin/tracks/{$track->getKey()}/edit")
            ->assertOk()
            ->assertSee(route('tracks.show', $track), false);
    }
}
