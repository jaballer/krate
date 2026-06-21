@extends('layouts.public')

@section('content')
    <section class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ config('krate.site.name', 'Krate') }}</h1>
        <p class="mt-2 text-gray-600">{{ config('krate.site.tagline', 'Your record collection, in order') }}</p>

        <form method="GET" action="{{ route('records.index') }}" class="mx-auto mt-6 flex max-w-md gap-2">
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
        </form>
        @if ($search !== '')
            <p class="mt-3 text-sm text-gray-500">
                {{ $records->total() }} result{{ $records->total() === 1 ? '' : 's' }} for &ldquo;{{ $search }}&rdquo; ·
                <a href="{{ route('records.index') }}" class="underline">clear</a>
            </p>
        @endif
    </section>

    @if ($records->isEmpty())
        <p class="py-16 text-center text-gray-500">No records found.</p>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($records as $record)
                <a href="{{ route('records.show', $record) }}"
                   class="group overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                    <div class="aspect-square w-full bg-gray-100">
                        @if ($record->front_image)
                            <img src="{{ Storage::disk('public')->url($record->front_image) }}"
                                 alt="{{ $record->title }}" class="h-full w-full object-cover">
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
