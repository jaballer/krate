@extends('layouts.public')

@section('title', $record->title.' — '.config('krate.site.name', 'Krate'))

@section('content')
    <a href="{{ route('records.index') }}" class="mb-6 inline-block text-sm text-gray-500 hover:text-gray-900">&larr; Back to catalog</a>

    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
        <div x-data="{ back: false }" class="space-y-3">
            <div class="aspect-square w-full overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                @php
                    $front = $record->front_image ? Storage::disk('public')->url($record->front_image) : null;
                    $back = $record->back_image ? Storage::disk('public')->url($record->back_image) : null;
                @endphp
                @if ($front || $back)
                    <img :src="back ? @js($back) : @js($front)" alt="{{ $record->title }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-8xl text-gray-300">&#9210;</div>
                @endif
            </div>
            @if ($back)
                <button type="button" @click="back = !back"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Flip <span x-text="back ? '(showing back)' : '(showing front)'"></span>
                </button>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $record->title }}</h1>
            <p class="mt-1 text-lg text-gray-600">{{ $record->artist }}</p>

            <dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                @php
                    $facts = array_filter([
                        'Genre' => $record->genre,
                        'Year' => $record->release_year,
                        'Label' => $record->label,
                        'Catalog #' => $record->catalog_number,
                        'Format' => $record->format->value,
                        'Speed' => $record->speed->value,
                        'Condition' => $record->condition->value,
                        'BPM' => $record->bpm,
                        'Purchased' => $record->purchase_date?->format('M j, Y'),
                        'Price' => $record->purchase_price !== null ? '$'.$record->purchase_price : null,
                    ], fn ($v) => $v !== null && $v !== '');
                @endphp
                @foreach ($facts as $label => $value)
                    <div>
                        <dt class="text-gray-500">{{ $label }}</dt>
                        <dd class="font-medium text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($record->notes)
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-gray-500">Notes</h2>
                    <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $record->notes }}</p>
                </div>
            @endif

            @if ($record->audio_file_url)
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-gray-500">Listen</h2>
                    <audio controls preload="none" src="{{ $record->audio_file_url }}" class="mt-2 w-full"></audio>
                </div>
            @endif

            @if ($record->purchase_link)
                <a href="{{ $record->purchase_link }}" target="_blank" rel="noopener noreferrer"
                   class="mt-8 inline-block rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Where to buy &rarr;
                </a>
            @endif
        </div>
    </div>
@endsection
