@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    {{-- Dark mode bootstrap: runs before CSS paint to prevent flash --}}
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-full bg-white dark:bg-zinc-900 font-sans antialiased text-zinc-800 dark:text-zinc-200 transition-colors">

<nav class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center gap-4">
        <a href="{{ route('dashboard') }}"
           class="font-bold text-base tracking-tight text-brand-600 dark:text-brand-400 shrink-0">
            {{ config('app.name') }}
        </a>

        <div class="flex items-center gap-1 flex-1">
            <a href="{{ route('dashboard') }}"
               class="px-3 py-1.5 text-sm rounded-md transition
                      {{ request()->routeIs('dashboard') ? 'text-zinc-900 dark:text-white bg-zinc-100 dark:bg-white/10 font-medium' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-white/8' }}">
                Dashboard
            </a>
            <a href="{{ route('settings.profile') }}"
               class="px-3 py-1.5 text-sm rounded-md transition
                      {{ request()->routeIs('settings.*') ? 'text-zinc-900 dark:text-white bg-zinc-100 dark:bg-white/10 font-medium' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-white/8' }}">
                Settings
            </a>
        </div>

        <div class="flex items-center gap-2">
            <x-theme-toggle />

            <a href="{{ route('pages.create') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg
                      bg-brand-600 hover:bg-brand-500 text-white transition">
                <flux:icon.plus class="size-3.5" />
                New Page
            </a>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 text-sm rounded-md text-zinc-500 dark:text-zinc-400
                               hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/8 transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

@if (session('success'))
    <div x-data="{ show: true }"
         x-show="show" x-transition
         x-init="setTimeout(() => show = false, 4000)"
         class="border-b border-green-200 dark:border-green-800
                bg-green-50 dark:bg-green-950/50
                text-green-800 dark:text-green-300 px-6 py-3 text-sm flex justify-between">
        <span>{{ session('success') }}</span>
        <button x-on:click="show = false" class="opacity-50 hover:opacity-100 ml-4">✕</button>
    </div>
@endif

@if ($errors->any())
    <div class="border-b border-red-200 dark:border-red-800
                bg-red-50 dark:bg-red-950/50
                text-red-700 dark:text-red-300 px-6 py-3 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<main>{{ $slot }}</main>

@livewireScripts
@stack('scripts')
</body>
</html>
