<?php

namespace Tests\Feature;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Enums\TrackSide;
use App\Enums\UserRole;
use App\Filament\Resources\Records\RecordResource;
use App\Models\Record;
use App\Models\Track;
use App\Models\User;
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

    public function test_search_matches_genre(): void
    {
        Record::factory()->create(['title' => 'Kind of Blue', 'genre' => 'Jazz']);
        Record::factory()->create(['title' => 'Straight Outta Compton', 'genre' => 'Hip Hop']);

        $this->get('/?search=Jazz')
            ->assertOk()
            ->assertSee('Kind of Blue')
            ->assertDontSee('Straight Outta Compton');
    }

    public function test_search_matches_label(): void
    {
        // Label is not rendered on the card, so a match here proves the search
        // reaches the label column (not a coincidental title/artist hit).
        Record::factory()->create(['title' => 'Blue Train', 'label' => 'Impulse']);
        Record::factory()->create(['title' => 'Thriller', 'label' => 'Epic']);

        $this->get('/?search=Impulse')
            ->assertOk()
            ->assertSee('Blue Train')
            ->assertDontSee('Thriller');
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

    public function test_grid_covers_reserve_space_and_lazy_load(): void
    {
        // Four covers so at least one falls outside the eager first row (3 cols).
        Record::factory()->count(4)->create(['front_image' => 'records/art.jpg']);

        $this->get('/')
            ->assertOk()
            ->assertSee('width="600" height="600"', false) // explicit dimensions avoid CLS
            ->assertSee('fetchpriority="high"', false)     // first row is eager for LCP
            ->assertSee('loading="lazy"', false);          // off-screen covers defer
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

    public function test_staff_see_an_edit_link_to_the_filament_record_editor(): void
    {
        $record = Record::factory()->create();
        $editUrl = RecordResource::getUrl('edit', ['record' => $record]);

        $staff = [
            User::factory()->administrator()->create(),
            User::factory()->create(['role' => UserRole::Manager]),
        ];

        foreach ($staff as $user) {
            $this->actingAs($user)
                ->get(route('records.show', $record))
                ->assertOk()
                ->assertSee('Edit record')
                ->assertSee($editUrl, false);
        }
    }

    public function test_non_staff_and_guests_do_not_see_an_edit_link(): void
    {
        $record = Record::factory()->create();

        // Guest
        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('Edit record');

        // Standard member
        $this->actingAs(User::factory()->create()) // defaults to Standard User
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('Edit record');
    }

    public function test_filter_by_genre_narrows_results(): void
    {
        Record::factory()->create(['title' => 'Blue Train', 'genre' => 'Jazz']);
        Record::factory()->create(['title' => 'Funkadelic LP', 'genre' => 'Funk']);

        $this->get('/?genre=Jazz')
            ->assertOk()
            ->assertSee('Blue Train')
            ->assertDontSee('Funkadelic LP');
    }

    public function test_filter_by_format_narrows_results(): void
    {
        Record::factory()->create(['title' => 'Twelve Inch Cut', 'format' => RecordFormat::TwelveInch]);
        Record::factory()->create(['title' => 'Seven Inch Single', 'format' => RecordFormat::SevenInch]);

        $this->get('/?format='.urlencode(RecordFormat::TwelveInch->value))
            ->assertOk()
            ->assertSee('Twelve Inch Cut')
            ->assertDontSee('Seven Inch Single');
    }

    public function test_filter_by_condition_narrows_results(): void
    {
        Record::factory()->create(['title' => 'Pristine Pressing', 'condition' => RecordCondition::Mint]);
        Record::factory()->create(['title' => 'Worn Copy', 'condition' => RecordCondition::Poor]);

        $this->get('/?condition='.urlencode(RecordCondition::Mint->value))
            ->assertOk()
            ->assertSee('Pristine Pressing')
            ->assertDontSee('Worn Copy');
    }

    public function test_filter_by_decade_narrows_results(): void
    {
        Record::factory()->create(['title' => 'Nineties Anthem', 'release_year' => 1995]);
        Record::factory()->create(['title' => 'Eighties Jam', 'release_year' => 1985]);

        $this->get('/?decade=1990')
            ->assertOk()
            ->assertSee('Nineties Anthem')
            ->assertDontSee('Eighties Jam');
    }

    public function test_filters_combine_with_search(): void
    {
        // Same title, so search alone matches both; genre narrows to one.
        Record::factory()->create(['title' => 'Midnight', 'artist' => 'Alpha Band', 'genre' => 'Jazz']);
        Record::factory()->create(['title' => 'Midnight', 'artist' => 'Beta Band', 'genre' => 'Funk']);

        $this->get('/?search=Midnight&genre=Jazz')
            ->assertOk()
            ->assertSee('Alpha Band')
            ->assertDontSee('Beta Band');
    }

    public function test_sort_by_artist_orders_alphabetically(): void
    {
        // Created Abba-first so the default newest-first order would lead with
        // Zappa; sort=artist must flip that to prove the sort is applied.
        Record::factory()->create(['artist' => 'Abba', 'title' => 'Record Aaa']);
        Record::factory()->create(['artist' => 'Zappa', 'title' => 'Record Zzz']);

        $content = (string) $this->get('/?sort=artist')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($content, 'Zappa'),
            strpos($content, 'Abba'),
            'Records should be ordered by artist A–Z.'
        );
    }

    public function test_sort_by_price_orders_high_to_low(): void
    {
        Record::factory()->create(['title' => 'Cheap Find', 'purchase_price' => 5]);
        Record::factory()->create(['title' => 'Rare Gem', 'purchase_price' => 250]);

        $content = (string) $this->get('/?sort=price')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($content, 'Cheap Find'),
            strpos($content, 'Rare Gem'),
            'Records should be ordered by price high to low.'
        );
    }

    public function test_invalid_sort_value_falls_back_to_default(): void
    {
        Record::factory()->create(['title' => 'Still Here']);

        // A bogus sort key must not error or leak into the order clause.
        $this->get('/?sort=id);drop')
            ->assertOk()
            ->assertSee('Still Here');
    }

    public function test_unknown_filter_values_are_ignored(): void
    {
        Record::factory()->create(['title' => 'Visible Record', 'genre' => 'Jazz', 'release_year' => 1990]);

        // Unrecognised filter values fall back to "no filter" rather than hiding
        // everything or erroring.
        $this->get('/?genre=Nonexistent&format=99&condition=Bogus&decade=abcd')
            ->assertOk()
            ->assertSee('Visible Record');
    }

    public function test_array_shaped_filter_values_are_ignored(): void
    {
        Record::factory()->create(['title' => 'Array Safe', 'genre' => 'Jazz']);

        // Mirrors the search guard: array-shaped input must not trigger an
        // Array-to-string error on any filter or sort parameter.
        $this->get('/?genre[]=Jazz&format[]=x&condition[]=Mint&decade[]=1990&sort[]=artist')
            ->assertOk()
            ->assertSee('Array Safe');
    }

    public function test_empty_genre_param_does_not_filter_even_with_a_blank_genre_record(): void
    {
        // A record with genre '' would make '' a candidate filter value, but the
        // default "All genres" option submits genre= — that must stay "no filter"
        // and not hide every record that has a real genre.
        Record::factory()->create(['title' => 'Real Genre Record', 'genre' => 'Jazz']);
        Record::factory()->create(['title' => 'Blank Genre Record', 'genre' => '']);

        $this->get('/?genre=')
            ->assertOk()
            ->assertSee('Real Genre Record')
            ->assertSee('Blank Genre Record');
    }

    public function test_record_detail_shows_its_tracklist_in_order(): void
    {
        $record = Record::factory()->create();
        // Created out of order; the tracklist should render A1 before A2.
        Track::factory()->forRecord($record, TrackSide::A, 2)->create(['title' => 'Second Track']);
        $first = Track::factory()->forRecord($record, TrackSide::A, 1)->create(['title' => 'First Track']);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('Tracklist')
            ->assertSeeInOrder(['First Track', 'Second Track'])
            ->assertSee(route('tracks.show', $first), false); // each track links to its page
    }

    public function test_record_without_tracks_has_no_tracklist_section(): void
    {
        $record = Record::factory()->create();

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('Tracklist');
    }
}
