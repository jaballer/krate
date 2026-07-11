@extends('layouts.public')

@use('Illuminate\Support\Facades\Storage')
@use('App\Enums\RecordFormat')
@use('App\Enums\RecordCondition')

@section('content')
    @php
        $selectClass = 'rounded-md border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-700 focus:border-gray-900 focus:ring-gray-900';
    @endphp

    <section class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ config('krate.site.name', 'Krate') }}</h1>
        <p class="mt-2 text-gray-600">{{ config('krate.site.tagline', 'Your record collection, in order') }}</p>

        <form method="GET" action="{{ route('records.index') }}" class="mt-6">
            <div class="mx-auto flex max-w-md gap-2">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by title or artist…"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:ring-gray-900"
                >
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Search
                </button>
            </div>

            {{-- Filters + sort. The selects auto-submit on change; the <noscript>
                 button keeps them usable without JavaScript. --}}
            <div class="mx-auto mt-4 flex max-w-3xl flex-wrap items-center justify-center gap-2">
                @if ($genres->isNotEmpty())
                    <select name="genre" aria-label="Filter by genre" onchange="this.form.submit()" class="{{ $selectClass }}">
                        <option value="">All genres</option>
                        @foreach ($genres as $g)
                            <option value="{{ $g }}" @selected($filters['genre'] === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                @endif

                <select name="format" aria-label="Filter by format" onchange="this.form.submit()" class="{{ $selectClass }}">
                    <option value="">All formats</option>
                    @foreach (RecordFormat::cases() as $f)
                        <option value="{{ $f->value }}" @selected($filters['format'] === $f->value)>{{ $f->getLabel() }}</option>
                    @endforeach
                </select>

                <select name="condition" aria-label="Filter by condition" onchange="this.form.submit()" class="{{ $selectClass }}">
                    <option value="">All conditions</option>
                    @foreach (RecordCondition::cases() as $c)
                        <option value="{{ $c->value }}" @selected($filters['condition'] === $c->value)>{{ $c->getLabel() }}</option>
                    @endforeach
                </select>

                @if ($decades->isNotEmpty())
                    <select name="decade" aria-label="Filter by decade" onchange="this.form.submit()" class="{{ $selectClass }}">
                        <option value="">All decades</option>
                        @foreach ($decades as $d)
                            <option value="{{ $d }}" @selected($filters['decade'] === $d)>{{ $d }}s</option>
                        @endforeach
                    </select>
                @endif

                <select name="sort" aria-label="Sort records" onchange="this.form.submit()" class="{{ $selectClass }}">
                    @foreach (['newest' => 'Newest', 'artist' => 'Artist A–Z', 'year' => 'Release year', 'price' => 'Price'] as $key => $label)
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
                {{ $records->total() }} result{{ $records->total() === 1 ? '' : 's' }}
                @if ($search !== '')
                    for &ldquo;{{ $search }}&rdquo;
                @endif
                · <a href="{{ route('records.index') }}" class="underline">clear all</a>
            </p>
        @endif
    </section>

    @if ($records->isEmpty())
        <div class="py-16 text-center text-gray-500">
            <p>No records found.</p>
            @if ($hasActiveFilters)
                <a href="{{ route('records.index') }}" class="mt-2 inline-block text-sm underline">Clear filters</a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($records as $record)
                <a href="{{ route('records.show', $record) }}"
                   class="group overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                    <div class="aspect-square w-full bg-gray-100">
                        @php $cover = $record->front_image ?? $record->back_image; @endphp
                        @if ($cover)
                            {{-- First row (up to 3 cols) loads eagerly for LCP; the rest lazy-load. --}}
                            <img src="{{ Storage::disk('public')->url($cover) }}"
                                 alt="{{ $record->title }}" class="h-full w-full object-cover"
                                 width="600" height="600" decoding="async"
                                 @if ($loop->index < 3) fetchpriority="high" @else loading="lazy" @endif>
                        @else
                            <div class="flex h-full w-full items-center justify-center text-6xl text-gray-300">&#9210;</div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h2 class="truncate font-semibold group-hover:underline">{{ $record->title }}</h2>
                        <p class="truncate text-sm text-gray-600">{{ $record->artist }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5 text-xs">
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-700">{{ $record->format->value }}</span>
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-700">{{ $record->condition->value }}</span>
                            @if ($record->release_year)
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-700">{{ $record->release_year }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $records->links() }}</div>
    @endif
@endsection
