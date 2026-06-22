<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('krate.site.name', 'Krate') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-full flex-col bg-gray-50 text-gray-900 antialiased">
        <x-site-header />

        <!-- Page Heading -->
        @isset($header)
            <header class="border-b border-gray-200 bg-white">
                <div class="mx-auto w-full max-w-6xl px-4 py-6">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
