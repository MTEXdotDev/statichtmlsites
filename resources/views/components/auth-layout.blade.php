@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-zinc-50 dark:bg-zinc-950 font-sans antialiased
             flex items-center justify-center px-4 py-12 transition-colors">

    <div class="w-full max-w-sm">
        <div class="mb-8 text-center flex flex-col items-center gap-3">
            <a href="{{ route('home') }}"
               class="text-2xl font-bold text-brand-600 dark:text-brand-400 tracking-tight">
                {{ config('app.name') }}
            </a>
            <x-theme-toggle />
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800
                    rounded-2xl p-8 shadow-sm dark:shadow-none">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
