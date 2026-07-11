@extends('layouts.public')

@use('App\Models\Track')

@section('title', 'Tracks — '.config('krate.site.name', 'Krate'))

@section('content')
    @php
        $selectClass = 'rounded-md border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-700 focus:border-gray-900 focus:ring-gray-900';
    @endphp

    <section class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Tracks</h1>
        <p class="mt-2 text-gray-600">Browse the track library</p>

        <form method="GET" action="{{ route('tracks.index') }}" class="mt-6">
            <div class="mx-auto flex max-w-md gap-2">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by title, artist, or album…"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:ring-gray-900"
                >
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Search
                </button>
            </div>

            {{-- Sort auto-submits on change; the <noscript> button keeps it usable without JavaScript. --}}
            <div class="mx-auto mt-4 flex max-w-3xl flex-wrap items-center justify-center gap-2">
                <select name="sort" aria-label="Sort tracks" onchange="this.form.submit()" class="{{ $selectClass }}">
                    @foreach (['newest' => 'Newest', 'title' => 'Title A–Z', 'artist' => 'Artist A–Z', 'bpm' => 'BPM'] as $key => $label)
                        <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <noscript>
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Apply
                    </button>
                </noscript>
            </div>
        </form>

        @if ($hasActiveFilters)
            <p class="mt-3 text-sm text-gray-500">
                {{ $tracks->total() }} result{{ $tracks->total() === 1 ? '' : 's' }}
                @if ($search !== '')
                    for &ldquo;{{ $search }}&rdquo;
                @endif
                · <a href="{{ route('tracks.index') }}" class="underline">clear</a>
            </p>
        @endif
    </section>

    @if ($tracks->isEmpty())
        <div class="py-16 text-center text-gray-500">
            <p>No tracks found.</p>
            @if ($hasActiveFilters)
                <a href="{{ route('tracks.index') }}" class="mt-2 inline-block text-sm underline">Clear search</a>
            @endif
        </div>
    @else
        <ul class="divide-y divide-gray-200 overflow-hidden rounded-lg border border-gray-200 bg-white">
            @foreach ($tracks as $track)
                <li>
                    <a href="{{ route('tracks.show', $track) }}"
                       class="flex items-center justify-between gap-4 px-4 py-3 transition hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900">{{ $track->title }}</p>
                            @php $albumLabel = $track->displayAlbum(); @endphp
                            <p class="truncate text-sm text-gray-600">
                                {{ $track->artist }}@if ($albumLabel) &middot; {{ $albumLabel }}@endif
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3 text-sm text-gray-500">
                            @if ($length = Track::formatDuration($track->duration_seconds))
                                <span class="tabular-nums">{{ $length }}</span>
                            @endif
                            @if ($track->bpm)
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $track->bpm }} BPM</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">{{ $tracks->links() }}</div>
    @endif
@endsection
