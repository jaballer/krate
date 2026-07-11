@php
    $user = auth()->user();
    $isStaff = $user?->role->isStaff() ?? false;
@endphp

<header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
        <a href="{{ route('records.index') }}" class="text-xl font-bold tracking-tight">
            {{ config('krate.site.name', 'Krate') }}
        </a>

        <nav class="flex items-center gap-3 text-sm sm:gap-4">
            <a href="{{ route('records.index') }}"
               class="{{ request()->routeIs('records.*') ? 'font-medium text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                Catalog
            </a>

            <a href="{{ route('tracks.index') }}"
               class="{{ request()->routeIs('tracks.*') ? 'font-medium text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                Tracks
            </a>

            @auth
                @if ($isStaff)
                    <a href="/admin"
                       class="hidden rounded-md bg-gray-900 px-3 py-1.5 font-medium text-white hover:bg-gray-700 sm:inline-block">
                        Admin
                    </a>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-1 rounded-md px-2 py-1.5 font-medium text-gray-600 hover:text-gray-900 focus:outline-none">
                            <span>{{ $user->name }}</span>
                            <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if ($isStaff)
                            <x-dropdown-link href="/admin">{{ __('Admin panel') }}</x-dropdown-link>
                        @else
                            <x-dropdown-link :href="route('dashboard')">{{ __('Dashboard') }}</x-dropdown-link>
                        @endif

                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @else
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                <a href="{{ route('register') }}"
                   class="rounded-md bg-gray-900 px-3 py-1.5 font-medium text-white hover:bg-gray-700">Register</a>
            @endauth
        </nav>
    </div>
</header>
