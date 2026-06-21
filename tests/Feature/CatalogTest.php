<?php

namespace Tests\Feature;

use App\Models\Record;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_records(): void
    {
        Record::factory()->create(['title' => 'Reasonable Doubt', 'artist' => 'Jay-Z']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Reasonable Doubt')
            ->assertSee('Jay-Z');
    }

    public function test_search_filters_records(): void
    {
        Record::factory()->create(['title' => 'Illmatic', 'artist' => 'Nas']);
        Record::factory()->create(['title' => 'Aquemini', 'artist' => 'Outkast']);

        $response = $this->get('/?search=Illmatic');

        $response->assertOk()->assertSee('Illmatic')->assertDontSee('Aquemini');
    }

    public function test_record_detail_is_shown(): void
    {
        $record = Record::factory()->create(['title' => 'The Chronic', 'artist' => 'Dr. Dre']);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('The Chronic')
            ->assertSee('Dr. Dre');
    }
}
