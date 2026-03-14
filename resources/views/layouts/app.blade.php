<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'StaticHTMLSites' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-gray-900">

<nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="font-bold text-lg tracking-tight text-indigo-600">
        StaticHTMLSites
    </a>
    <div class="flex items-center gap-4 text-sm">
        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
        <a href="{{ route('pages.create') }}"
           class="bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700 transition">
            + New Page
        </a>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-gray-500 hover:text-gray-800">Logout</button>
        </form>
    </div>
</nav>

@if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-3 text-sm">
        {{ session('success') }}
    </div>
@endif

<main class="max-w-6xl mx-auto px-6 py-8">
    {{ $slot }}
</main>

@livewireScripts
@stack('scripts')
</body>
</html>
