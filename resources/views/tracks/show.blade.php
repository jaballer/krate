@extends('layouts.public')

@use('App\Filament\Resources\Tracks\TrackResource')
@use('App\Models\Track')

@section('title', e($track->title).' — '.config('krate.site.name', 'Krate'))

@section('content')
    @php
        // Staff (Administrator/Manager) get an inline shortcut to the Filament
        // edit screen. Mirrors the isStaff() gate in the site header; the public
        // track library itself stays read-only.
        $canEdit = auth()->user()?->role->isStaff() ?? false;

        // Only expose http/https URLs publicly — schemes like javascript: are an
        // XSS vector in src and HTML escaping does not neutralize them.
        $scheme = strtolower((string) parse_url((string) $track->audio_file_url, PHP_URL_SCHEME));
        $audioUrl = in_array($scheme, ['http', 'https'], true) ? $track->audio_file_url : null;
    @endphp

    <div class="mb-6 flex items-center justify-between gap-3">
        <a href="{{ route('tracks.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Back to tracks</a>

        @if ($canEdit)
            <a href="{{ TrackResource::getUrl('edit', ['record' => $track]) }}"
               class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                </svg>
                Edit track
            </a>
        @endif
    </div>

    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold tracking-tight">{{ $track->title }}</h1>
        <p class="mt-1 text-lg text-gray-600">{{ $track->artist }}</p>

        <dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            @php
                $facts = array_filter([
                    'Album' => $track->displayAlbum(),
                    'Genre' => $track->genre,
                    'Year' => $track->release_year,
                    'Length' => Track::formatDuration($track->duration_seconds),
                    'BPM' => $track->bpm ?: null, // 0/null are both "unknown"
                ], fn ($v) => $v !== null && $v !== '');
            @endphp
            @foreach ($facts as $label => $value)
                <div>
                    <dt class="text-gray-500">{{ $label }}</dt>
                    <dd class="font-medium text-gray-900">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($track->notes)
            <div class="mt-6">
                <h2 class="text-sm font-semibold text-gray-500">Notes</h2>
                <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $track->notes }}</p>
            </div>
        @endif

        @if ($audioUrl)
            <div class="mt-6">
                <h2 class="text-sm font-semibold text-gray-500">Listen</h2>
                <audio controls preload="none" src="{{ $audioUrl }}" class="mt-2 w-full"></audio>
            </div>
        @endif
    </div>
@endsection
