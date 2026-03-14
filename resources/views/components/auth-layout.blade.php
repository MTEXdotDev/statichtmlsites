@props(['title' => config('app.name'), 'footer' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}"
               class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400
                      bg-clip-text text-transparent tracking-tight">
                {{ config('app.name') }}
            </a>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-2xl">
            {{ $slot }}
        </div>

        @if ($footer)
            <p class="text-center text-sm text-gray-500 mt-5">{{ $footer }}</p>
        @endif
    </div>

</body>
</html>
