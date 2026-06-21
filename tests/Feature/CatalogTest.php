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

    public function test_record_title_is_escaped_in_the_page_title(): void
    {
        $record = Record::factory()->create(['title' => 'Evil</title><script>alert(1)</script>']);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false); // raw needle must not appear
    }

    public function test_back_cover_is_shown_when_there_is_no_front_cover(): void
    {
        // Distinctive, slash-free filename so the assertion is robust against
        // @js slash-escaping and absolute vs relative storage URLs.
        $record = Record::factory()->create([
            'front_image' => null,
            'back_image' => 'records/only-back-cover.jpg',
        ]);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('only-back-cover.jpg'); // back image referenced even with no front
    }
}
