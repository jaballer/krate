<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('krate.site.name', 'Krate'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-gray-50 text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('records.index') }}" class="text-xl font-bold tracking-tight">
                {{ config('krate.site.name', 'Krate') }}
            </a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('records.index') }}" class="text-gray-600 hover:text-gray-900">Catalog</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-gray-900 px-3 py-1.5 font-medium text-white hover:bg-gray-700">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">
        @php($socialLinks = app(\App\Services\SocialLinksService::class)->links())
        @if ($socialLinks)
            <ul class="mb-3 flex justify-center gap-4">
                @foreach ($socialLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-gray-900">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        &copy; {{ date('Y') }} {{ config('krate.site.name', 'Krate') }}
    </footer>
</body>
</html>
