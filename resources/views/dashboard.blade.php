<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight">
            {{ __('Welcome back, :name', ['name' => auth()->user()->first_name ?: auth()->user()->name]) }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <p class="text-gray-600">
            Your home base for the {{ config('krate.site.name', 'Krate') }} collection.
            @if (isset($recordCount))
                There {{ $recordCount === 1 ? 'is' : 'are' }}
                <span class="font-semibold text-gray-900">{{ number_format($recordCount) }}</span>
                record{{ $recordCount === 1 ? '' : 's' }} in the catalog right now.
            @endif
        </p>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <a href="{{ route('records.index') }}"
               class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                <h3 class="font-semibold group-hover:underline">Browse the catalog</h3>
                <p class="mt-1 text-sm text-gray-600">Search and explore every record in the collection.</p>
            </a>

            <a href="{{ route('profile.edit') }}"
               class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                <h3 class="font-semibold group-hover:underline">Edit your profile</h3>
                <p class="mt-1 text-sm text-gray-600">Update your name, email, and password.</p>
            </a>
        </div>
    </div>
</x-app-layout>
