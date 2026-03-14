@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxStyles
    @stack('head')
</head>
<body class="min-h-full bg-white dark:bg-zinc-900 font-sans antialiased" x-data>

    <flux:header class="border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:brand href="{{ route('dashboard') }}" name="{{ config('app.name') }}" />

        <flux:spacer />

        <flux:navbar>
            <flux:navbar.item href="{{ route('dashboard') }}"
                              :current="request()->routeIs('dashboard')">
                Dashboard
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:button href="{{ route('pages.create') }}" variant="primary" size="sm" icon="plus">
            New Page
        </flux:button>

        <form method="POST" action="{{ route('logout') }}" class="ml-2">
            @csrf
            <flux:button type="submit" variant="ghost" size="sm">Logout</flux:button>
        </form>
    </flux:header>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="border-b border-green-200 bg-green-50 dark:bg-green-950 dark:border-green-800
                    text-green-800 dark:text-green-300 px-6 py-3 text-sm flex justify-between">
            <span>{{ session('success') }}</span>
            <button x-on:click="show = false" class="opacity-60 hover:opacity-100">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="border-b border-red-200 bg-red-50 dark:bg-red-950
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
    @fluxScripts
    @stack('scripts')
</body>
</html>
