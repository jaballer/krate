<?php

namespace App\Services;

use App\Models\Setting;

class SocialLinksService
{
    /** setting_key => display label */
    private const PLATFORMS = [
        'social_facebook' => 'Facebook',
        'social_instagram' => 'Instagram',
        'social_linkedin' => 'LinkedIn',
    ];

    /**
     * Configured social links, restricted to http/https URLs.
     *
     * @return array<int, array{label: string, url: string}>
     */
    public function links(): array
    {
        $links = [];

        foreach (self::PLATFORMS as $key => $label) {
            $url = Setting::getValue($key);

            if (is_string($url) && $this->isWebUrl($url)) {
                $links[] = ['label' => $label, 'url' => $url];
            }
        }

        return $links;
    }

    private function isWebUrl(string $url): bool
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
