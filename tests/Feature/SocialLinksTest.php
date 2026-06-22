<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    private function setSocial(string $key, string $value): void
    {
        Setting::create([
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => 'string',
        ]);
    }

    public function test_footer_shows_configured_social_links(): void
    {
        $this->setSocial('social_facebook', 'https://facebook.com/krate');

        $this->get('/')
            ->assertOk()
            ->assertSee('Facebook')
            ->assertSee('https://facebook.com/krate');
    }

    public function test_footer_omits_non_web_social_links(): void
    {
        $this->setSocial('social_facebook', 'javascript:alert(1)');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('javascript:alert(1)', false)
            ->assertDontSee('Facebook');
    }

    public function test_footer_has_no_list_when_no_social_links_configured(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Facebook')
            ->assertDontSee('Instagram');
    }
}
