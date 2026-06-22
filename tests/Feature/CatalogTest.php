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

    public function test_array_search_parameter_is_ignored(): void
    {
        Record::factory()->create(['title' => 'Some Record']);

        $this->get('/?search[]=foo')
            ->assertOk()
            ->assertSee('Some Record');
    }

    public function test_grid_shows_cover_art_with_back_fallback(): void
    {
        // A record with a front cover, and one with only a back cover.
        Record::factory()->create(['front_image' => 'records/front-art.jpg', 'back_image' => null]);
        Record::factory()->create(['front_image' => null, 'back_image' => 'records/back-art.jpg']);

        // 200 here also proves the Storage facade resolves in the rendered grid.
        $this->get('/')
            ->assertOk()
            ->assertSee('front-art.jpg')   // front cover shown
            ->assertSee('back-art.jpg');   // back-only record falls back to its back cover
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
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_back_cover_is_shown_when_there_is_no_front_cover(): void
    {
        $record = Record::factory()->create([
            'front_image' => null,
            'back_image' => 'records/only-back-cover.jpg',
        ]);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('only-back-cover.jpg');
    }

    public function test_zero_bpm_is_hidden(): void
    {
        $hasBpm = Record::factory()->create(['bpm' => 120]);
        $this->get(route('records.show', $hasBpm))->assertOk()->assertSee('BPM');

        $zeroBpm = Record::factory()->create(['bpm' => 0]);
        $this->get(route('records.show', $zeroBpm))->assertOk()->assertDontSee('BPM');
    }

    public function test_non_web_purchase_links_are_not_rendered(): void
    {
        $malicious = Record::factory()->create(['purchase_link' => 'javascript:alert(1)']);
        $this->get(route('records.show', $malicious))
            ->assertOk()
            ->assertDontSee('javascript:alert(1)', false)
            ->assertDontSee('Where to buy');

        $safe = Record::factory()->create(['purchase_link' => 'https://example.com/buy']);
        $this->get(route('records.show', $safe))
            ->assertOk()
            ->assertSee('Where to buy');
    }
}
