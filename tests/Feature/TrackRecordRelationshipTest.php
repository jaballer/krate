<?php

namespace Tests\Feature;

use App\Enums\TrackSide;
use App\Models\Record;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackRecordRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_returns_its_tracklist_ordered_by_side_then_position(): void
    {
        $record = Record::factory()->create();

        // Created out of order; the relationship should sort A1, A2, B1.
        Track::factory()->forRecord($record, TrackSide::B, 1)->create(['title' => 'B One']);
        Track::factory()->forRecord($record, TrackSide::A, 2)->create(['title' => 'A Two']);
        Track::factory()->forRecord($record, TrackSide::A, 1)->create(['title' => 'A One']);

        $this->assertSame(
            ['A One', 'A Two', 'B One'],
            $record->tracks->pluck('title')->all(),
        );
    }

    public function test_a_track_belongs_back_to_its_record(): void
    {
        $record = Record::factory()->create(['title' => 'Enter the Wu-Tang']);
        $track = Track::factory()->forRecord($record, TrackSide::A, 1)->create();

        $this->assertTrue($track->record->is($record));
    }

    public function test_a_track_can_be_standalone(): void
    {
        $record = Record::factory()->create();
        Track::factory()->forRecord($record, TrackSide::A, 1)->create();
        $standalone = Track::factory()->create(); // no record

        $this->assertNull($standalone->record_id);
        $this->assertNull($standalone->record);
        $this->assertCount(1, $record->tracks);
    }

    public function test_deleting_a_record_orphans_its_tracks_rather_than_deleting_them(): void
    {
        $record = Record::factory()->create();
        $track = Track::factory()->forRecord($record, TrackSide::A, 1)->create();

        $record->delete();

        // nullOnDelete: the track survives, just unlinked.
        $this->assertDatabaseHas('tracks', ['id' => $track->id, 'record_id' => null]);
    }
}
