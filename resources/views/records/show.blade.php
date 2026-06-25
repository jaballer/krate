@extends('layouts.public')

@use('Illuminate\Support\Facades\Storage')
@use('App\Filament\Resources\Records\RecordResource')

@section('title', e($record->title).' — '.config('krate.site.name', 'Krate'))

@section('content')
    @php
        // Staff (Administrator/Manager) get an inline shortcut to the Filament
        // edit screen for the record on view. Mirrors the isStaff() gate in the
        // site header; the public catalog itself stays read-only.
        $canEdit = auth()->user()?->role->isStaff() ?? false;
    @endphp

    <div class="mb-6 flex items-center justify-between gap-3">
        <a href="{{ route('records.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Back to catalog</a>

        @if ($canEdit)
            <a href="{{ RecordResource::getUrl('edit', ['record' => $record]) }}"
               class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                </svg>
                Edit record
            </a>
        @endif
    </div>

    @php
        $front = $record->front_image ? Storage::disk('public')->url($record->front_image) : null;
        $back = $record->back_image ? Storage::disk('public')->url($record->back_image) : null;

        // Only expose http/https URLs publicly — schemes like javascript: are an
        // XSS vector in href/src and HTML escaping does not neutralize them.
        $webUrl = function (?string $url): ?string {
            $scheme = strtolower((string) parse_url((string) $url, PHP_URL_SCHEME));
            return in_array($scheme, ['http', 'https'], true) ? $url : null;
        };
        $purchaseUrl = $webUrl($record->purchase_link);
        $audioUrl = $webUrl($record->audio_file_url);
    @endphp

    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
        <div x-data="{ back: false }" class="space-y-3">
            <div class="aspect-square w-full overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                @if ($front || $back)
                    {{-- Each side falls back to the other so a single available cover always shows. --}}
                    <img :src="back ? @js($back ?? $front) : @js($front ?? $back)" alt="{{ $record->title }} by {{ $record->artist }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-8xl text-gray-300" aria-hidden="true">&#9210;</div>
                @endif
            </div>
            @if ($front && $back)
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
                        'BPM' => $record->bpm ?: null, // 0 is the "unknown" sentinel
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

            @if ($audioUrl)
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-gray-500">Listen</h2>
                    <audio controls preload="none" src="{{ $audioUrl }}" class="mt-2 w-full"></audio>
                </div>
            @endif

            @if ($purchaseUrl)
                <a href="{{ $purchaseUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mt-8 inline-block rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Where to buy &rarr;
                </a>
            @endif
        </div>
    </div>
@endsection
