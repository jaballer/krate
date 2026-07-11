{{-- Neutral fallback shown when a record or track has no image. Square art,
     matching the app's aspect-square image slots. Swap the asset to restyle
     every placeholder at once. --}}
@props(['alt' => ''])
<img src="{{ asset('images/placeholders/square.svg') }}"
     alt="{{ $alt }}"
     {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
     loading="lazy" decoding="async">
