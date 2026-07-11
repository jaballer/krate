<?php

namespace Tests\Feature;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Enums\RecordSpeed;
use App\Filament\Resources\Records\Pages\CreateRecord;
use App\Filament\Resources\Records\Pages\EditRecord;
use App\Models\Record;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Record CRUD through the Filament admin panel. The public catalog is
 * read-only (see CatalogTest); every write path lives behind staff auth.
 */
class RecordResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_reach_the_record_create_page(): void
    {
        $this->get('/admin/records/create')->assertRedirect('/admin/login');
    }

    public function test_non_staff_cannot_reach_the_record_create_page(): void
    {
        $member = User::factory()->create(); // defaults to Standard User

        $this->actingAs($member)->get('/admin/records/create')->assertForbidden();
    }

    public function test_staff_can_create_a_record(): void
    {
        $admin = User::factory()->administrator()->create();

        Livewire::actingAs($admin)
            ->test(CreateRecord::class)
            ->fillForm([
                'title' => 'Reasonable Doubt',
                'artist' => 'Jay-Z',
                'format' => RecordFormat::TwelveInch->value,
                'speed' => RecordSpeed::Rpm33->value,
                'condition' => RecordCondition::NearMint->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vinyl_records', [
            'title' => 'Reasonable Doubt',
            'artist' => 'Jay-Z',
            'format' => RecordFormat::TwelveInch->value,
            'condition' => RecordCondition::NearMint->value,
        ]);
    }

    public function test_creating_a_record_requires_title_and_artist(): void
    {
        $admin = User::factory()->administrator()->create();

        Livewire::actingAs($admin)
            ->test(CreateRecord::class)
            ->fillForm([
                'title' => null,
                'artist' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'artist' => 'required']);

        $this->assertDatabaseCount('vinyl_records', 0);
    }

    public function test_staff_can_edit_a_record(): void
    {
        $admin = User::factory()->administrator()->create();
        $record = Record::factory()->create(['title' => 'Old Title']);

        Livewire::actingAs($admin)
            ->test(EditRecord::class, ['record' => $record->getRouteKey()])
            ->fillForm(['title' => 'New Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vinyl_records', [
            'id' => $record->id,
            'title' => 'New Title',
        ]);
    }

    public function test_staff_can_delete_a_record(): void
    {
        $admin = User::factory()->administrator()->create();
        $record = Record::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditRecord::class, ['record' => $record->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($record);
    }

    public function test_edit_page_links_to_the_public_record_page(): void
    {
        $admin = User::factory()->administrator()->create();
        $record = Record::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditRecord::class, ['record' => $record->getRouteKey()])
            ->assertActionExists('viewOnSite');

        // The action renders as a real link to the public detail page.
        $this->actingAs($admin)
            ->get("/admin/records/{$record->getKey()}/edit")
            ->assertOk()
            ->assertSee(route('records.show', $record), false);
    }
}
