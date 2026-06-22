<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('krate.site.name', 'Krate'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-gray-50 text-gray-900 antialiased">
    <x-site-header />

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
