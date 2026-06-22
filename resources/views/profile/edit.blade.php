<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
