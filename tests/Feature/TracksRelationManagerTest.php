<?php

namespace Tests\Feature;

use App\Enums\TrackSide;
use App\Filament\Resources\Records\Pages\EditRecord;
use App\Filament\Resources\Records\RelationManagers\TracksRelationManager;
use App\Models\Record;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Tracks relation manager on the Record edit page — how staff manage a
 * record's tracklist. Every path lives behind staff auth.
 */
class TracksRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_the_records_tracklist(): void
    {
        $admin = User::factory()->administrator()->create();
        $record = Record::factory()->create();
        $onThisRecord = Track::factory()->forRecord($record, TrackSide::A, 1)->create();
        $onAnotherRecord = Track::factory()->forRecord(Record::factory()->create(), TrackSide::A, 1)->create();

        Livewire::actingAs($admin)
            ->test(TracksRelationManager::class, [
                'ownerRecord' => $record,
                'pageClass' => EditRecord::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$onThisRecord])
            ->assertCanNotSeeTableRecords([$onAnotherRecord]);
    }

    public function test_staff_can_add_a_track_to_a_record_via_the_relation_manager(): void
    {
        $admin = User::factory()->administrator()->create();
        $record = Record::factory()->create();

        Livewire::actingAs($admin)
            ->test(TracksRelationManager::class, [
                'ownerRecord' => $record,
                'pageClass' => EditRecord::class,
            ])
            ->callTableAction('create', data: [
                'title' => 'Protect Ya Neck',
                'artist' => 'Wu-Tang Clan',
                'side' => TrackSide::A->value,
                'position' => 2,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('tracks', [
            'record_id' => $record->id,
            'title' => 'Protect Ya Neck',
            'side' => TrackSide::A->value,
            'position' => 2,
        ]);
    }
}
